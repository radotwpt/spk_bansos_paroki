<?php

namespace App\Services;

use App\Models\CalonPenerima;
use App\Models\SawCriterion;
use App\Models\SawWeight;
use App\Models\SawResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SawCalculationService
{
    private array $weights = [
        'c1_pendapatan' => 0.40,
        'c2_tanggungan' => 0.30,
        'c3_tempat_tinggal' => 0.15,
        'c4_status_hubungan' => 0.15
    ];

    /**
     * Execute SAW calculation for a given period id.
     * Returns a collection of ['id' => ..., 'score' => ...] ordered desc.
     */
    public function calculate(int $periodId, ?int $userId = null, bool $persist = true): Collection
    {
        $kandidats = CalonPenerima::where('bansos_period_id', $periodId)
            ->whereIn('status_alur', ['diajukan_ke_stasi', 'disetujui_stasi', 'diranking_lingkungan_paroki'])
            ->get();

        if ($kandidats->isEmpty()) {
            return collect();
        }

        // build raw matrix keyed by criterion code
        $matrix = $kandidats->map(function ($item) {
            return [
                'id' => $item->id,
                'c1_pendapatan' => (float) $item->pendapatan_keluarga,
                'c2_tanggungan' => (float) $item->jumlah_tanggungan,
                'c3_tempat_tinggal' => $this->mapTempatTinggiToScore($item->status_tempat_tinggal),
                'c4_status_hubungan' => $this->mapStatusHubunganToScore($item->status_hubungan),
            ];
        });

        // load criteria and determine min/max depending on criterion type
        $criteria = SawCriterion::all()->keyBy('key');

        $extents = [];
        foreach ($criteria as $key => $crit) {
            $values = $matrix->pluck($key)->map(fn ($v) => (float) $v);
            if ($crit->type === 'cost') {
                $min = $values->min();
                $extents[$key] = $min <= 0 ? 1.0 : $min;
            } else {
                $max = $values->max();
                $extents[$key] = $max <= 0 ? 1.0 : $max;
            }
        }

        // load weights (period-specific override global)
        $weights = $this->getWeightsForPeriod($periodId, $criteria);

        // compute normalized and final scores
        $calculated = $matrix->map(function ($row) use ($criteria, $extents, $weights) {
            $normalized = [];
            foreach ($criteria as $key => $crit) {
                $value = (float) ($row[$key] ?? 0);
                if ($crit->type === 'cost') {
                    $den = $value <= 0 ? 1 : $value;
                    $normalized[$key] = $extents[$key] / $den;
                } else {
                    $normalized[$key] = $value / ($extents[$key] ?: 1);
                }
            }

            $score = 0.0;
            foreach ($criteria as $key => $crit) {
                $w = (float) ($weights[$key] ?? 0);
                $score += $w * ($normalized[$key] ?? 0);
            }

            return [
                'id' => $row['id'],
                'raw' => array_filter($row, fn($k) => $k !== 'id', ARRAY_FILTER_USE_KEY),
                'normalized' => $normalized,
                'score' => round($score, 4),
            ];
        });

        $rankedItems = $calculated->sortByDesc('score')->values();

        if ($persist) {
            DB::transaction(function () use ($rankedItems, $weights, $periodId, $userId) {
                foreach ($rankedItems as $index => $item) {
                    $rankNumber = $index + 1;

                    // persist saw_result audit
                    $result = SawResult::updateOrCreate(
                        ['bansos_period_id' => $periodId, 'calon_penerima_id' => $item['id']],
                        [
                            'raw_values' => $item['raw'],
                            'normalized_values' => $item['normalized'],
                            'weights_used' => $weights,
                            'score' => $item['score'],
                            'rank' => $rankNumber,
                            'created_by' => $userId,
                        ]
                    );

                    // update calon penerima summary
                    CalonPenerima::where('id', $item['id'])->update([
                        'saw_score' => $item['score'],
                        'rank_global' => $rankNumber,
                        'status_alur' => 'diranking_lingkungan_paroki'
                    ]);
                }
            });
        }

        return $rankedItems;
    }

    private function getWeightsForPeriod(int $periodId, $criteria): array
    {
        $weights = [];
        foreach ($criteria as $key => $crit) {
            $w = SawWeight::where('saw_criterion_id', $crit->id)->where('bansos_period_id', $periodId)->first();
            if (!$w) {
                $w = SawWeight::where('saw_criterion_id', $crit->id)->whereNull('bansos_period_id')->first();
            }
            $weights[$key] = $w ? (float) $w->weight : 0.0;
        }

        return $weights;
    }

    public function mapTempatTinggiToScore(string $value): float
    {
        return match ($value) {
            'numpang' => 3.0,
            'sewa' => 2.0,
            'milik_sendiri' => 1.0,
            default => 1.0,
        };
    }

    public function mapStatusHubunganToScore(string $value): float
    {
        return match ($value) {
            'cerai' => 3.0,
            'menikah' => 2.0,
            'lajang' => 1.0,
            default => 1.0,
        };
    }
}
