<?php

namespace App\Services;

use App\Models\CalonPenerima;
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
    public function calculate(int $periodId): Collection
    {
        $kandidats = CalonPenerima::where('bansos_period_id', $periodId)
            ->whereIn('status_alur', ['diajukan_ke_stasi', 'disetujui_stasi', 'diranking_lingkungan_paroki'])
            ->get();

        if ($kandidats->isEmpty()) {
            return collect();
        }

        $matrixX = $kandidats->map(function ($item) {
            return [
                'id' => $item->id,
                'c1' => (float) $item->pendapatan_keluarga,
                'c2' => (float) $item->jumlah_tanggungan,
                'c3' => $this->mapTempatTinggiToScore($item->status_tempat_tinggal),
                'c4' => $this->mapStatusHubunganToScore($item->status_hubungan),
            ];
        });

        $minC1 = $matrixX->min('c1');
        $maxC2 = $matrixX->max('c2');
        $maxC3 = $matrixX->max('c3');
        $maxC4 = $matrixX->max('c4');

        $minC1 = $minC1 <= 0 ? 1.0 : $minC1;
        $maxC2 = $maxC2 <= 0 ? 1.0 : $maxC2;
        $maxC3 = $maxC3 <= 0 ? 1.0 : $maxC3;
        $maxC4 = $maxC4 <= 0 ? 1.0 : $maxC4;

        $calculatedScores = $matrixX->map(function ($row) use ($minC1, $maxC2, $maxC3, $maxC4) {
            $r1 = $minC1 / ($row['c1'] <= 0 ? 1 : $row['c1']);
            $r2 = $row['c2'] / $maxC2;
            $r3 = $row['c3'] / $maxC3;
            $r4 = $row['c4'] / $maxC4;

            $v = ($this->weights['c1_pendapatan'] * $r1) +
                 ($this->weights['c2_tanggungan'] * $r2) +
                 ($this->weights['c3_tempat_tinggal'] * $r3) +
                 ($this->weights['c4_status_hubungan'] * $r4);

            return [
                'id' => $row['id'],
                'score' => round($v, 4)
            ];
        });

        $rankedItems = $calculatedScores->sortByDesc('score')->values();

        DB::transaction(function () use ($rankedItems) {
            foreach ($rankedItems as $index => $ranked) {
                $rankNumber = $index + 1;
                CalonPenerima::where('id', $ranked['id'])->update([
                    'saw_score' => $ranked['score'],
                    'rank_global' => $rankNumber,
                    'status_alur' => 'diranking_lingkungan_paroki'
                ]);
            }
        });

        return $rankedItems;
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
