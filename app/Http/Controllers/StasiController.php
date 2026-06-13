<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CalonPenerima;

class StasiController extends Controller
{
    /**
     * Show the dashboard for stasi.
     */
    public function dashboard(): View
    {
        $this->authorizeRole('stasi');

        $user = Auth::user();
        $stasiId = $user->stasi_id;

        // Metrik Ringkasan
        $metrics = [
            'total_masuk'     => CalonPenerima::where('stasi_id', $stasiId)->whereIn('status', ['submitted_to_stasi', 'revision_requested', 'approved_by_stasi', 'sent_to_paroki', 'ranked', 'rejected'])->count(),
            'perlu_validasi'  => CalonPenerima::where('stasi_id', $stasiId)->where('status', 'submitted_to_stasi')->count(),
            'telah_divalidasi'=> CalonPenerima::where('stasi_id', $stasiId)->whereIn('status', ['approved_by_stasi', 'sent_to_paroki', 'ranked', 'rejected'])->count(),
            'sedang_revisi'   => CalonPenerima::where('stasi_id', $stasiId)->where('status', 'revision_requested')->count(),
        ];

        // Pipeline Validasi
        $pipeline = [
            'submitted' => $metrics['perlu_validasi'],
            'approved'  => CalonPenerima::where('stasi_id', $stasiId)->whereIn('status', ['approved_by_stasi', 'sent_to_paroki', 'ranked'])->count(),
            'rejected'  => CalonPenerima::where('stasi_id', $stasiId)->where('status', 'rejected')->count(),
        ];
        $totalPipeline = array_sum($pipeline);
        $pipelineData = [];
        if ($totalPipeline > 0) {
            $pipelineData = [
                'submitted' => ['count' => $pipeline['submitted'], 'pct' => round(($pipeline['submitted']/$totalPipeline)*100, 1)],
                'approved'  => ['count' => $pipeline['approved'],  'pct' => round(($pipeline['approved']/$totalPipeline)*100, 1)],
                'rejected'  => ['count' => $pipeline['rejected'],  'pct' => round(($pipeline['rejected']/$totalPipeline)*100, 1)],
            ];
        }

        // Calon terbaru yang butuh validasi (max 5)
        $recentSubmissions = CalonPenerima::where('stasi_id', $stasiId)
            ->where('status', 'submitted_to_stasi')
            ->with(['lingkungan:id,name', 'periodeBantuan:id,name'])
            ->latest('submitted_at')
            ->take(5)
            ->get();

        // Distribusi Pengajuan per Lingkungan
        $distribusiLingkungan = \App\Models\Lingkungan::where('stasi_id', $stasiId)
            ->withCount(['calonPenerimas' => function ($query) {
                // Hitung semua pengajuan yang sedang diproses stasi atau lebih tinggi
                $query->whereIn('status', ['submitted_to_stasi', 'revision_requested', 'approved_by_stasi', 'sent_to_paroki', 'ranked', 'rejected']);
            }])
            ->orderByDesc('calon_penerimas_count')
            ->get();

        return view('stasi.dashboard', compact('metrics', 'pipelineData', 'recentSubmissions', 'distribusiLingkungan'));
    }

    protected function authorizeRole(string $role)
    {
        if (!Auth::check()) {
            abort(redirect()->route('login'));
        }

        $user = Auth::user();
        if (!isset($user->role) || $user->role->name !== $role) {
            abort(403, 'Unauthorized action.');
        }
    }
}

