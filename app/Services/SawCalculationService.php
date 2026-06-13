<?php

namespace App\Services;

use App\Models\CalonPenerima;
use App\Models\PeriodeBantuan;
use App\Models\SawResult;
use App\Models\SawWeightVersion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SawCalculationService
{
    /**
     * @return Collection<int, SawResult>
     */
    public function calculateForPeriod(PeriodeBantuan $period, User $user, ?SawWeightVersion $weightVersion = null): Collection
    {
        $weightVersion ??= SawWeightVersion::query()->where('is_active', true)->with('items.criterion')->firstOrFail();

        $candidates = CalonPenerima::query()
            ->where('periode_bantuan_id', $period->id)
            ->whereIn('status', ['sent_to_paroki', 'ranked', 'under_discussion'])
            ->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        $minIncome = max((float) $candidates->min('monthly_income'), 1);
        $maxDependents = max((int) $candidates->max('dependents_count'), 1);
        $maxHousing = max((int) $candidates->max('housing_status_score'), 1);
        $maxDisability = max((int) $candidates->max('disability_score'), 1);
        $weightsByAttribute = $weightVersion->items->keyBy(fn ($item) => $item->criterion->attribute_key);
        $weightsByCode = $weightVersion->items->keyBy(fn ($item) => $item->criterion->code);
        $weight = fn (string $attribute, string $code): float => (float) (
            $weightsByAttribute->get($attribute)?->weight
            ?? $weightsByCode->get($code)?->weight
            ?? 0
        ) / 100;

        $ranked = $candidates
            ->map(function (CalonPenerima $candidate) use ($minIncome, $maxDependents, $maxHousing, $maxDisability, $weight, $weightVersion, $user): array {
                $income = max((float) $candidate->monthly_income, 1);
                $dependents = (int) $candidate->dependents_count;
                $housing = (int) $candidate->housing_status_score;
                $disability = (int) $candidate->disability_score;

                $normalizedIncome = $minIncome / $income;
                $normalizedDependents = $dependents / $maxDependents;
                $normalizedHousing = $housing / $maxHousing;
                $normalizedDisability = $disability / $maxDisability;

                $score = (
                    $normalizedIncome * $weight('monthly_income', 'monthly_income')
                    + $normalizedDependents * $weight('dependents_count', 'dependents_count')
                    + $normalizedHousing * $weight('housing_status_score', 'housing_status')
                    + $normalizedDisability * $weight('disability_score', 'disability')
                );

                return [
                    'candidate' => $candidate,
                    'payload' => [
                        'periode_bantuan_id' => $candidate->periode_bantuan_id,
                        'calon_penerima_id' => $candidate->id,
                        'saw_weight_version_id' => $weightVersion->id,
                        'calculated_by' => $user->id,
                        'monthly_income_value' => $income,
                        'dependents_count_value' => $dependents,
                        'housing_status_score_value' => $housing,
                        'disability_score_value' => $disability,
                        'normalized_income' => round($normalizedIncome, 6),
                        'normalized_dependents' => round($normalizedDependents, 6),
                        'normalized_housing' => round($normalizedHousing, 6),
                        'normalized_disability' => round($normalizedDisability, 6),
                        'final_score' => round($score, 6),
                        'calculation_snapshot' => [
                            'min_income' => $minIncome,
                            'max_dependents' => $maxDependents,
                            'max_housing' => $maxHousing,
                            'max_disability' => $maxDisability,
                            'weights' => $weightVersion->items->mapWithKeys(fn ($item) => [$item->criterion->code => (float) $item->weight]),
                        ],
                        'calculated_at' => now(),
                    ],
                ];
            })
            ->sortByDesc(fn (array $item) => $item['payload']['final_score'])
            ->values();

        return DB::transaction(function () use ($ranked, $period) {
            SawResult::query()->where('periode_bantuan_id', $period->id)->delete();

            return $ranked->map(function (array $item, int $index) {
                $payload = $item['payload'];
                $payload['rank'] = $index + 1;

                $item['candidate']->update([
                    'status' => 'ranked',
                    'ranked_at' => now(),
                ]);

                return SawResult::query()->create($payload);
            });
        });
    }
}
