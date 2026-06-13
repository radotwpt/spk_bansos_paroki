<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CalonPenerima;
use App\Models\PenerimaBantuan;
use App\Models\PeriodeBantuan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get summary statistics for a period
     */
    public function summary(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $totalCandidates = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->count();

        $candidatesByStatus = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status');

        $totalBeneficiaries = PenerimaBantuan::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->count();

        $beneficiariesByStatus = PenerimaBantuan::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->groupBy('final_status')
            ->selectRaw('final_status, COUNT(*) as count')
            ->pluck('count', 'final_status');

        $totalDisbursed = PenerimaBantuan::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->where('disbursement_status', 'disbursed')
            ->count();

        $totalAmountDisbursed = PenerimaBantuan::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->where('disbursement_status', 'disbursed')
            ->sum('aid_amount');

        $remainingBudget = $periodeBantuan->total_budget - ($totalAmountDisbursed ?? 0);

        return $this->success([
            'period' => [
                'id' => $periodeBantuan->id,
                'code' => $periodeBantuan->code,
                'name' => $periodeBantuan->name,
                'status' => $periodeBantuan->status,
                'starts_at' => $periodeBantuan->starts_at,
                'ends_at' => $periodeBantuan->ends_at,
                'quota' => $periodeBantuan->quota,
                'total_budget' => $periodeBantuan->total_budget,
            ],
            'candidates' => [
                'total' => $totalCandidates,
                'by_status' => $candidatesByStatus,
                'draft' => $candidatesByStatus->get('draft', 0),
                'submitted' => $candidatesByStatus->get('submitted_to_stasi', 0),
                'approved_by_stasi' => $candidatesByStatus->get('approved_by_stasi', 0),
                'sent_to_paroki' => $candidatesByStatus->get('sent_to_paroki', 0),
                'ranked' => $candidatesByStatus->get('ranked', 0),
                'approved_final' => $candidatesByStatus->get('approved_final', 0),
                'rejected' => $candidatesByStatus->get('rejected', 0),
            ],
            'beneficiaries' => [
                'total' => $totalBeneficiaries,
                'by_status' => $beneficiariesByStatus,
                'selected' => $beneficiariesByStatus->get('selected', 0),
                'waiting_list' => $beneficiariesByStatus->get('waiting_list', 0),
                'disbursed' => $totalDisbursed,
            ],
            'financial' => [
                'total_budget' => $periodeBantuan->total_budget,
                'amount_disbursed' => $totalAmountDisbursed ?? 0,
                'remaining_budget' => $remainingBudget,
                'average_aid_amount' => $totalBeneficiaries > 0
                    ? round(($totalAmountDisbursed ?? 0) / $totalBeneficiaries, 2)
                    : 0,
                'budget_utilization_percent' => $periodeBantuan->total_budget > 0
                    ? round((($totalAmountDisbursed ?? 0) / $periodeBantuan->total_budget) * 100, 2)
                    : 0,
            ],
        ]);
    }

    /**
     * Get detailed statistics for a period
     */
    public function statistics(Request $request, PeriodeBantuan $periodeBantuan)
    {
        // Economic Data Statistics
        $economicStats = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->selectRaw('
                COUNT(*) as total_candidates,
                AVG(monthly_income) as avg_income,
                MIN(monthly_income) as min_income,
                MAX(monthly_income) as max_income,
                AVG(dependents_count) as avg_dependents,
                SUM(CASE WHEN has_disability THEN 1 ELSE 0 END) as total_disabled
            ')
            ->first();

        // Housing Status Distribution
        $housingStats = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->groupBy('housing_status')
            ->selectRaw('housing_status, COUNT(*) as count')
            ->pluck('count', 'housing_status');

        // Gender Distribution (if tracked)
        $genderStats = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->groupBy('gender')
            ->selectRaw('gender, COUNT(*) as count')
            ->pluck('count', 'gender');

        // Top Applicant Areas (by lingkungan)
        $topAreas = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->with('lingkungan')
            ->groupBy('lingkungan_id')
            ->selectRaw('lingkungan_id, COUNT(*) as count')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->pluck('count', 'lingkungan_id')
            ->map(function ($count, $id) use ($periodeBantuan) {
                $lingkungan = CalonPenerima::query()
                    ->where('periode_bantuan_id', $periodeBantuan->id)
                    ->where('lingkungan_id', $id)
                    ->first()?->lingkungan;

                return [
                    'lingkungan_id' => $id,
                    'lingkungan_name' => $lingkungan?->name,
                    'applicant_count' => $count,
                ];
            });

        // Workflow Stage Statistics
        $workflowStats = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->selectRaw('
                SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status IN ("submitted_to_stasi", "revision_requested") THEN 1 ELSE 0 END) as stasi_review,
                SUM(CASE WHEN status IN ("approved_by_stasi", "sent_to_paroki") THEN 1 ELSE 0 END) as paroki_review,
                SUM(CASE WHEN status IN ("ranked", "under_discussion") THEN 1 ELSE 0 END) as ranked,
                SUM(CASE WHEN status = "approved_final" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected
            ')
            ->first();

        // Age Distribution (if available)
        $ageStats = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->whereNotNull('date_of_birth')
            ->selectRaw('
                SUM(CASE WHEN YEAR(CURDATE()) - YEAR(date_of_birth) < 30 THEN 1 ELSE 0 END) as under_30,
                SUM(CASE WHEN YEAR(CURDATE()) - YEAR(date_of_birth) BETWEEN 30 AND 50 THEN 1 ELSE 0 END) as age_30_50,
                SUM(CASE WHEN YEAR(CURDATE()) - YEAR(date_of_birth) > 50 THEN 1 ELSE 0 END) as over_50
            ')
            ->first();

        // Ranking Score Distribution
        $scoreStats = \App\Models\SawResult::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->selectRaw('
                COUNT(*) as total_ranked,
                AVG(final_score) as avg_score,
                MIN(final_score) as min_score,
                MAX(final_score) as max_score,
                STDDEV(final_score) as score_stddev
            ')
            ->first();

        return $this->success([
            'period' => [
                'id' => $periodeBantuan->id,
                'code' => $periodeBantuan->code,
                'name' => $periodeBantuan->name,
            ],
            'economic_statistics' => [
                'total_candidates' => $economicStats?->total_candidates ?? 0,
                'average_monthly_income' => round($economicStats?->avg_income ?? 0, 2),
                'min_monthly_income' => round($economicStats?->min_income ?? 0, 2),
                'max_monthly_income' => round($economicStats?->max_income ?? 0, 2),
                'average_dependents' => round($economicStats?->avg_dependents ?? 0, 2),
                'total_disabled' => $economicStats?->total_disabled ?? 0,
            ],
            'housing_distribution' => $housingStats->map(fn ($count, $status) => [
                'housing_status' => $status,
                'count' => $count,
            ])->values(),
            'gender_distribution' => $genderStats->map(fn ($count, $gender) => [
                'gender' => $gender ?? 'not_specified',
                'count' => $count,
            ])->values(),
            'age_distribution' => [
                'under_30' => $ageStats?->under_30 ?? 0,
                'age_30_50' => $ageStats?->age_30_50 ?? 0,
                'over_50' => $ageStats?->over_50 ?? 0,
            ],
            'workflow_statistics' => [
                'draft' => $workflowStats?->draft ?? 0,
                'stasi_review' => $workflowStats?->stasi_review ?? 0,
                'paroki_review' => $workflowStats?->paroki_review ?? 0,
                'ranked' => $workflowStats?->ranked ?? 0,
                'approved' => $workflowStats?->approved ?? 0,
                'rejected' => $workflowStats?->rejected ?? 0,
            ],
            'ranking_statistics' => [
                'total_ranked' => $scoreStats?->total_ranked ?? 0,
                'average_score' => round($scoreStats?->avg_score ?? 0, 6),
                'min_score' => round($scoreStats?->min_score ?? 0, 6),
                'max_score' => round($scoreStats?->max_score ?? 0, 6),
                'score_stddev' => round($scoreStats?->score_stddev ?? 0, 6),
            ],
            'top_applicant_areas' => $topAreas,
        ]);
    }
}
