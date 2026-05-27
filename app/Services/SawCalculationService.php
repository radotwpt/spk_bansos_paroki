<?php

namespace App\Services;

use App\Models\CalonPenerima;
use App\Models\SawCriterion;
use App\Models\SawResult;
use App\Models\SawWeight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SawCalculationService
{
    /**
     * Execute SAW calculation for a given period id.
     * Returns ordered collection desc by score.
     */
    public function calculate(int $periodId, ?int $userId = null, bool $persist = true): Collection
    {
        $calculation = $this->buildCalculation($periodId);
        $rankedItems = $calculation['ranked'];

        if ($persist) {
            DB::transaction(function () use ($rankedItems, $calculation, $periodId, $userId) {
                $internalRankMap = [];

                foreach ($rankedItems as $index => $item) {
                    $rankGlobal = $index + 1;
                    $stasiId = (int) ($item['stasi_id'] ?? 0);
                    $internalRankMap[$stasiId] = ($internalRankMap[$stasiId] ?? 0) + 1;
                    $rankInternalStasi = $internalRankMap[$stasiId];

                    SawResult::updateOrCreate(
                        ['bansos_period_id' => $periodId, 'calon_penerima_id' => $item['id']],
                        [
                            'raw_values' => $item['raw'],
                            'normalized_values' => $item['normalized'],
                            'weights_used' => $calculation['weights'],
                            'score' => $item['score'],
                            'rank' => $rankGlobal,
                            'created_by' => $userId,
                        ]
                    );

                    CalonPenerima::query()
                        ->withoutGlobalScopes()
                        ->where('id', $item['id'])
                        ->update([
                            'saw_score' => $item['score'],
                            'rank_global' => $rankGlobal,
                            'rank_internal_stasi' => $rankInternalStasi,
                            'status_alur' => 'diranking_lingkungan_paroki',
                        ]);
                }
            });
        }

        return $rankedItems;
    }

    public function buildPreview(int $periodId): array
    {
        $calculation = $this->buildCalculation($periodId);
        $ranked = $calculation['ranked'];

        $summary = [
            'period_id' => $periodId,
            'total_candidates' => $calculation['candidates']->count(),
            'total_approved_stasi' => $calculation['approved_count'],
            'weights' => $calculation['weights'],
            'criteria_extents' => $calculation['extents'],
        ];

        $decisionMatrix = $calculation['matrix']->map(fn ($row) => [
            'id' => $row['id'],
            'nik' => $row['nik'],
            'nama_lengkap' => $row['nama_lengkap'],
            'c1_pendapatan' => $row['c1_pendapatan'],
            'c2_tanggungan' => $row['c2_tanggungan'],
            'c3_tempat_tinggal' => $row['c3_tempat_tinggal'],
            'c4_status_hubungan' => $row['c4_status_hubungan'],
        ])->values();

        $normalizationMatrix = $ranked->map(fn ($row) => [
            'id' => $row['id'],
            'nik' => $row['nik'],
            'nama_lengkap' => $row['nama_lengkap'],
            'r1' => $row['normalized']['c1_pendapatan'] ?? 0,
            'r2' => $row['normalized']['c2_tanggungan'] ?? 0,
            'r3' => $row['normalized']['c3_tempat_tinggal'] ?? 0,
            'r4' => $row['normalized']['c4_status_hubungan'] ?? 0,
        ])->values();

        $scoring = $ranked->values()->map(function ($row, $index) {
            return [
                'id' => $row['id'],
                'nik' => $row['nik'],
                'nama_lengkap' => $row['nama_lengkap'],
                'score' => $row['score'],
                'rank' => $index + 1,
                'stasi_id' => $row['stasi_id'],
                'stasi_nama' => $row['stasi_nama'],
                'lingkungan_stasi_nama' => $row['lingkungan_stasi_nama'],
            ];
        });

        return [
            'summary' => $summary,
            'decision_matrix' => $decisionMatrix,
            'normalization_matrix' => $normalizationMatrix,
            'scoring' => $scoring,
        ];
    }

    private function buildCalculation(int $periodId): array
    {
        $criteria = SawCriterion::query()->orderBy('id')->get()->keyBy('key');
        $candidates = $this->getCandidatesForRanking($periodId);

        if ($candidates->isEmpty() || $criteria->isEmpty()) {
            return [
                'candidates' => collect(),
                'approved_count' => 0,
                'matrix' => collect(),
                'weights' => [],
                'extents' => [],
                'ranked' => collect(),
            ];
        }

        $matrix = $candidates->map(function (CalonPenerima $candidate) {
            return [
                'id' => $candidate->id,
                'nik' => $candidate->nik,
                'nama_lengkap' => $candidate->nama_lengkap,
                'stasi_id' => $candidate->stasi_id,
                'stasi_nama' => $candidate->stasi?->nama_stasi,
                'lingkungan_stasi_nama' => $candidate->lingkunganStasi?->nama_lingkungan_stasi,
                'c1_pendapatan' => (float) $candidate->pendapatan_keluarga,
                'c2_tanggungan' => (float) $candidate->jumlah_tanggungan,
                'c3_tempat_tinggal' => $this->mapTempatTinggiToScore($candidate->status_tempat_tinggal),
                'c4_status_hubungan' => $this->mapStatusHubunganToScore($candidate->status_hubungan),
            ];
        })->values();

        $extents = [];
        foreach ($criteria as $key => $criterion) {
            $values = $matrix->pluck($key)->map(fn ($value) => (float) $value);
            if ($criterion->type === 'cost') {
                $min = $values->min();
                $extents[$key] = $min <= 0 ? 1.0 : $min;
            } else {
                $max = $values->max();
                $extents[$key] = $max <= 0 ? 1.0 : $max;
            }
        }

        $weights = $this->getWeightsForPeriod($periodId, $criteria);

        $calculated = $matrix->map(function (array $row) use ($criteria, $extents, $weights) {
            $normalized = [];
            foreach ($criteria as $key => $criterion) {
                $value = (float) ($row[$key] ?? 0);
                if ($criterion->type === 'cost') {
                    $denominator = $value <= 0 ? 1.0 : $value;
                    $normalized[$key] = $extents[$key] / $denominator;
                } else {
                    $normalized[$key] = $value / ($extents[$key] ?: 1.0);
                }
            }

            $score = 0.0;
            foreach ($criteria as $key => $criterion) {
                $weight = (float) ($weights[$key] ?? 0);
                $score += $weight * ($normalized[$key] ?? 0);
            }

            return [
                'id' => $row['id'],
                'nik' => $row['nik'],
                'nama_lengkap' => $row['nama_lengkap'],
                'stasi_id' => $row['stasi_id'],
                'stasi_nama' => $row['stasi_nama'],
                'lingkungan_stasi_nama' => $row['lingkungan_stasi_nama'],
                'raw' => [
                    'c1_pendapatan' => $row['c1_pendapatan'],
                    'c2_tanggungan' => $row['c2_tanggungan'],
                    'c3_tempat_tinggal' => $row['c3_tempat_tinggal'],
                    'c4_status_hubungan' => $row['c4_status_hubungan'],
                ],
                'normalized' => $normalized,
                'score' => round($score, 4),
            ];
        });

        return [
            'candidates' => $candidates,
            'approved_count' => $candidates->where('status_alur', 'disetujui_stasi')->count(),
            'matrix' => $matrix,
            'weights' => $weights,
            'extents' => $extents,
            'ranked' => $calculated->sortByDesc('score')->values(),
        ];
    }

    private function getCandidatesForRanking(int $periodId): Collection
    {
        return CalonPenerima::query()
            ->withoutGlobalScopes()
            ->with(['stasi:id,nama_stasi', 'lingkunganStasi:id,nama_lingkungan_stasi'])
            ->where('bansos_period_id', $periodId)
            ->whereIn('status_alur', ['disetujui_stasi', 'diranking_lingkungan_paroki'])
            ->get();
    }

    private function getWeightsForPeriod(int $periodId, Collection $criteria): array
    {
        $weights = [];
        foreach ($criteria as $key => $criterion) {
            $weight = SawWeight::query()
                ->where('saw_criterion_id', $criterion->id)
                ->where('bansos_period_id', $periodId)
                ->first();

            if (! $weight) {
                $weight = SawWeight::query()
                    ->where('saw_criterion_id', $criterion->id)
                    ->whereNull('bansos_period_id')
                    ->first();
            }

            $weights[$key] = $weight ? (float) $weight->weight : 0.0;
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

