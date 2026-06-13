<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PeriodeBantuan;
use App\Models\SawResult;
use App\Models\SawWeightVersion;
use App\Services\AuditService;
use App\Services\SawCalculationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RankingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SawCalculationService $sawService,
        protected AuditService $auditService,
    ) {}

    /**
     * Calculate SAW ranking for a period
     *
     * @throws ValidationException
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'periode_bantuan_id' => ['required', 'exists:periode_bantuans,id'],
            'saw_weight_version_id' => ['nullable', 'exists:saw_weight_versions,id'],
        ]);

        $period = PeriodeBantuan::query()->findOrFail($request->input('periode_bantuan_id'));
        $this->authorizeUserToPeriode($request->user(), $period);

        $weightVersion = null;
        if ($request->filled('saw_weight_version_id')) {
            $weightVersion = SawWeightVersion::query()->findOrFail($request->input('saw_weight_version_id'));
        }

        try {
            $results = $this->sawService->calculateForPeriod($period, $request->user(), $weightVersion);

            $this->auditService->record(
                'ranking.calculated',
                $period,
                newValues: ['results_count' => $results->count()],
                request: $request
            );

            return $this->success([
                'periode_bantuan_id' => $period->id,
                'total_candidates_ranked' => $results->count(),
                'weight_version_used' => $weightVersion?->code ?? 'active',
                'calculated_at' => now(),
                'results' => $results->map(fn ($result) => [
                    'rank' => $result->rank,
                    'candidate' => [
                        'id' => $result->calonPenerima->id,
                        'name' => $result->calonPenerima->name,
                        'nik' => $result->calonPenerima->nik,
                        'status' => $result->calonPenerima->status,
                    ],
                    'score' => $result->final_score,
                    'normalized_scores' => [
                        'income' => $result->normalized_income,
                        'dependents' => $result->normalized_dependents,
                        'housing' => $result->normalized_housing,
                        'disability' => $result->normalized_disability,
                    ],
                ]),
            ], 'Ranking berhasil dihitung.');
        } catch (\Exception $e) {
            $this->auditService->record(
                'ranking.calculation_failed',
                $period,
                newValues: ['error' => $e->getMessage()],
                request: $request
            );

            return $this->error('Gagal menghitung ranking: '.$e->getMessage(), 422);
        }
    }

    /**
     * Get ranking results for a period
     */
    public function results(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $this->authorizeUserToPeriode($request->user(), $periodeBantuan);

        $results = SawResult::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->with(['calonPenerima', 'sawWeightVersion'])
            ->orderBy('rank')
            ->paginate((int) $request->query('per_page', 15));

        return $this->success(
            $results->through(fn ($result) => [
                'rank' => $result->rank,
                'candidate' => [
                    'id' => $result->calonPenerima->id,
                    'name' => $result->calonPenerima->name,
                    'nik' => $result->calonPenerima->nik,
                    'address' => $result->calonPenerima->address,
                    'phone' => $result->calonPenerima->phone,
                    'status' => $result->calonPenerima->status,
                ],
                'economic_data' => [
                    'monthly_income' => $result->monthly_income_value,
                    'dependents_count' => $result->dependents_count_value,
                    'housing_status' => $result->calonPenerima->housing_status,
                    'has_disability' => $result->calonPenerima->has_disability,
                ],
                'scoring' => [
                    'final_score' => $result->final_score,
                    'normalized_income' => $result->normalized_income,
                    'normalized_dependents' => $result->normalized_dependents,
                    'normalized_housing' => $result->normalized_housing,
                    'normalized_disability' => $result->normalized_disability,
                ],
                'calculated_at' => $result->calculated_at,
            ])
        );
    }

    /**
     * Finalize ranking and create penerima bantuan (beneficiaries)
     */
    public function finalize(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $this->authorizeUserToPeriode($request->user(), $periodeBantuan);

        $request->validate([
            'approved_count' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $approvedCount = $request->input('approved_count');

        try {
            $results = SawResult::query()
                ->where('periode_bantuan_id', $periodeBantuan->id)
                ->orderBy('rank')
                ->get();

            if ($results->isEmpty()) {
                return $this->error('Tidak ada hasil ranking untuk periode ini.', 422);
            }

            // Create penerima bantuan for approved candidates.
            $results->each(function ($result, $index) use ($request, $periodeBantuan, $approvedCount) {
                $status = $index < $approvedCount ? 'selected' : 'waiting_list';

                $result->calonPenerima->penerimaBantuan()->updateOrCreate(
                    [
                        'periode_bantuan_id' => $periodeBantuan->id,
                        'calon_penerima_id' => $result->calon_penerima_id,
                    ],
                    [
                        'decided_by' => $request->user()->id,
                        'final_status' => $status,
                        'periode_bantuan_id' => $periodeBantuan->id,
                        'aid_amount' => $periodeBantuan->default_aid_amount,
                        'decision_note' => $request->input('notes'),
                        'decided_at' => now(),
                    ]
                );

                $result->calonPenerima->update([
                    'status' => $status === 'selected' ? 'approved_final' : 'under_discussion',
                    'decided_by' => $request->user()->id,
                    'decided_at' => now(),
                    'paroki_decision_note' => $request->input('notes'),
                ]);
            });

            $periodeBantuan->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);

            $this->auditService->record(
                'ranking.finalized',
                $periodeBantuan,
                newValues: [
                    'approved_count' => $approvedCount,
                    'status' => 'finalized',
                ],
                request: $request
            );

            return $this->success([
                'period_id' => $periodeBantuan->id,
                'status' => 'finalized',
                'approved_count' => $approvedCount,
                'waiting_list_count' => $results->count() - $approvedCount,
            ], 'Ranking berhasil difinalisasi.');
        } catch (\Exception $e) {
            return $this->error('Gagal menfinalisasi ranking: '.$e->getMessage(), 422);
        }
    }

    /**
     * Get current SAW weights
     */
    public function getWeights(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $this->authorizeUserToPeriode($request->user(), $periodeBantuan);

        $activeVersion = SawWeightVersion::query()
            ->where('is_active', true)
            ->with(['items.criterion'])
            ->first();

        if (! $activeVersion) {
            return $this->error('Tidak ada versi bobot aktif.', 404);
        }

        return $this->success([
            'version_id' => $activeVersion->id,
            'version_code' => $activeVersion->code,
            'is_active' => $activeVersion->is_active,
            'is_locked' => $activeVersion->locked_at !== null,
            'weights' => $activeVersion->items->map(fn ($item) => [
                'criterion_id' => $item->criterion->id,
                'criterion_code' => $item->criterion->code,
                'criterion_name' => $item->criterion->name,
                'weight' => $item->weight,
                'type' => $item->criterion->type,
            ])->sortBy('weight')->reverse(),
        ]);
    }

    /**
     * Update SAW weights (only for active version, not locked)
     */
    public function updateWeights(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $this->authorizeUserToPeriode($request->user(), $periodeBantuan);

        if (! $request->user()->hasRole('paroki') && ! $request->user()->hasRole('super_admin')) {
            return $this->error('Hanya kepala paroki atau admin yang dapat mengubah bobot.', 403);
        }

        $request->validate([
            'weights' => ['required', 'array'],
            'weights.*.criterion_id' => ['required', 'exists:saw_criteria,id'],
            'weights.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $activeVersion = SawWeightVersion::query()->where('is_active', true)->firstOrFail();

            if ($activeVersion->locked_at !== null) {
                return $this->error('Bobot terkunci dan tidak dapat diubah.', 422);
            }

            // Update weights
            foreach ($request->input('weights') as $weightData) {
                $activeVersion->items()
                    ->where('saw_criterion_id', $weightData['criterion_id'])
                    ->update(['weight' => $weightData['weight']]);
            }

            $this->auditService->record(
                'ranking.weights_updated',
                $activeVersion,
                newValues: ['weights' => $request->input('weights')],
                request: $request
            );

            return $this->success([], 'Bobot berhasil diperbarui.');
        } catch (\Exception $e) {
            return $this->error('Gagal memperbarui bobot: '.$e->getMessage(), 422);
        }
    }

    /**
     * Authorize user to access period
     */
    private function authorizeUserToPeriode($user, PeriodeBantuan $period): void
    {
        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('paroki') && $user->paroki_id === $period->paroki_id) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke periode ini.');
    }
}
