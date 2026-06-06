<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\LingkunganParoki;
use App\Models\SawResult;
use App\Services\BansosWorkflowService;
use Illuminate\Http\Request;

class KetuaLingkunganParokiController extends Controller
{
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;

    public function __construct(BansosWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Get dashboard statistics for Ketua Lingkungan Paroki
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $currentPeriod = BansosPeriod::where('status', 'aktif')
            ->orWhere('status', 'berlangsung')
            ->latest()
            ->first();

        if (!$currentPeriod) {
            return $this->success([
                'current_period' => null,
                'statistics' => null,
            ], 'Tidak ada periode aktif');
        }

        // Get lingkungan paroki for current user
        $lingkunganParokis = $user->lingkunganParokis;

        // Get candidates in current period for user's lingkungan paroki
        $candidatesQuery = CalonPenerima::whereHas('stasiLingkungan', function ($q) use ($lingkunganParokis) {
            $q->whereIn('lingkungan_paroki_id', $lingkunganParokis->pluck('id'));
        })->where('bansos_period_id', $currentPeriod->id);

        $totalCandidates = $candidatesQuery->count();
        $rankedCandidates = SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
            ->count();

        // Get top 5 candidates
        $topCandidates = SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
            ->with('calon')
            ->orderByDesc('score')
            ->take(5)
            ->get()
            ->map(function ($result) {
                return [
                    'rank' => $result->rank,
                    'nama' => $result->calon->nama_lengkap,
                    'score' => round($result->score, 4),
                    'nik' => $result->calon->nik,
                ];
            });

        // Get score distribution
        $scoreDistribution = SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
            ->selectRaw('ROUND(score, 1) as score_range, COUNT(*) as count')
            ->groupBy('score_range')
            ->orderBy('score_range', 'desc')
            ->get();

        return $this->success([
            'current_period' => [
                'id' => $currentPeriod->id,
                'nama' => $currentPeriod->nama_periode,
                'tahun' => $currentPeriod->tahun,
                'status' => $currentPeriod->status,
            ],
            'statistics' => [
                'total_candidates' => $totalCandidates,
                'ranked_candidates' => $rankedCandidates,
                'pending_candidates' => $totalCandidates - $rankedCandidates,
                'ranking_progress' => $totalCandidates > 0 
                    ? round(($rankedCandidates / $totalCandidates) * 100, 2) 
                    : 0,
            ],
            'top_candidates' => $topCandidates,
            'score_distribution' => $scoreDistribution,
        ], 'Dashboard data berhasil diambil');
    }

    /**
     * Get ranking list with pagination and filters
     */
    public function rankingList(Request $request)
    {
        $user = $request->user();
        $periodId = $request->input('period_id');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);
        $sortBy = $request->input('sort_by', 'rank');
        $order = $request->input('order', 'asc');

        $lingkunganParokis = $user->lingkunganParokis->pluck('id');

        $query = CalonPenerima::with('sawResult')
            ->whereHas('stasiLingkungan', function ($q) use ($lingkunganParokis) {
                $q->whereIn('lingkungan_paroki_id', $lingkunganParokis);
            })
            ->where('bansos_period_id', $periodId);

        // Join with saw_results for sorting
        $query->leftJoin('saw_results', 'calon_penerimas.id', '=', 'saw_results.calon_penerima_id')
            ->select('calon_penerimas.*', 'saw_results.score', 'saw_results.rank');

        if ($sortBy === 'rank') {
            $query->orderByRaw('CAST(saw_results.rank AS UNSIGNED) ' . strtoupper($order));
        } elseif ($sortBy === 'score') {
            $query->orderBy('saw_results.score', $order);
        } elseif ($sortBy === 'nama') {
            $query->orderBy('calon_penerimas.nama_lengkap', $order);
        }

        $total = $query->count();
        $rankings = $query->paginate($limit, ['*'], 'page', $page);

        return $this->success([
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit),
            'data' => $rankings->items(),
        ], 'Ranking list berhasil diambil');
    }

    /**
     * Get SAW calculation details for a specific candidate
     */
    public function sawDetails(Request $request, $candidateId)
    {
        $user = $request->user();
        
        $candidate = CalonPenerima::with('sawResult')
            ->find($candidateId);

        if (!$candidate) {
            return $this->error('Calon penerima tidak ditemukan', 404);
        }

        // Check authorization
        $userLingkunganIds = $user->lingkunganParokis->pluck('id');
        if (!$candidate->stasiLingkungan->lingkungan_paroki_id || 
            !$userLingkunganIds->contains($candidate->stasiLingkungan->lingkungan_paroki_id)) {
            return $this->error('Unauthorized', 403);
        }

        $sawResult = $candidate->sawResult;
        if (!$sawResult) {
            return $this->error('SAW result tidak ditemukan', 404);
        }

        return $this->success([
            'candidate' => [
                'id' => $candidate->id,
                'nik' => $candidate->nik,
                'nama' => $candidate->nama_lengkap,
                'alamat' => $candidate->alamat,
            ],
            'saw_result' => [
                'rank' => $sawResult->rank,
                'score' => round($sawResult->score, 4),
                'raw_values' => $sawResult->raw_values,
                'normalized_values' => $sawResult->normalized_values,
                'weights_used' => $sawResult->weights_used,
            ],
        ], 'Detail SAW berhasil diambil');
    }

    /**
     * Get activity logs for the period
     */
    public function activityLogs(Request $request)
    {
        $user = $request->user();
        $periodId = $request->input('period_id');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        $lingkunganIds = $user->lingkunganParokis->pluck('id');

        $logs = ActivityLog::where('module', 'ketua_lingkungan_paroki')
            ->where('period_id', $periodId)
            ->whereIn('lingkungan_id', $lingkunganIds)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);

        return $this->success([
            'total' => $logs->total(),
            'per_page' => $limit,
            'current_page' => $page,
            'data' => $logs->items(),
        ], 'Activity logs berhasil diambil');
    }

    /**
     * Get statistics summary for reporting
     */
    public function reportingSummary(Request $request)
    {
        $user = $request->user();
        $periodId = $request->input('period_id');

        $lingkunganParokis = $user->lingkunganParokis;
        $lingkunganIds = $lingkunganParokis->pluck('id');

        $candidatesQuery = CalonPenerima::whereHas('stasiLingkungan', function ($q) use ($lingkunganIds) {
            $q->whereIn('lingkungan_paroki_id', $lingkunganIds);
        })->where('bansos_period_id', $periodId);

        $totalCandidates = $candidatesQuery->count();
        $rankedCandidates = SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))->count();

        // Get average score
        $avgScore = SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
            ->avg('score');

        // Get score ranges
        $scoreRanges = [
            'excellent' => SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
                ->where('score', '>=', 80)
                ->count(),
            'very_good' => SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
                ->whereBetween('score', [60, 79.99])
                ->count(),
            'good' => SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
                ->whereBetween('score', [40, 59.99])
                ->count(),
            'fair' => SawResult::whereIn('calon_penerima_id', $candidatesQuery->pluck('id'))
                ->where('score', '<', 40)
                ->count(),
        ];

        return $this->success([
            'summary' => [
                'total_candidates' => $totalCandidates,
                'ranked_candidates' => $rankedCandidates,
                'pending_candidates' => $totalCandidates - $rankedCandidates,
                'average_score' => $avgScore ? round($avgScore, 4) : 0,
            ],
            'score_categories' => $scoreRanges,
            'lingkungan_paroki_count' => $lingkunganParokis->count(),
        ], 'Summary report berhasil diambil');
    }

    public function executeSawRanking(Request $request, $periodId)
    {
        $user = $request->user();
        $result = $this->workflow->triggerSaw((int) $periodId, $user ? $user->id : null);

        return $this->success($result, 'Perankingan SAW berhasil dijalankan.');
    }

    public function sendRankingToParoki(Request $request, $periodId)
    {
        $user = $request->user();
        $ok = $this->workflow->sendRankingToParoki((int) $periodId, $user ? $user->id : null);

        return $this->success(['ok' => (bool) $ok], 'Ranking berhasil dikirim ke paroki.');
    }
}
