<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\SawCriterion;
use App\Models\SawResult;
use App\Models\SawWeight;
use App\Models\Stasi;
use App\Services\BansosWorkflowService;
use App\Services\SawCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SawController extends Controller
{
    use RespondsWithApi;

    public function __construct(
        protected SawCalculationService $sawService,
        protected BansosWorkflowService $workflowService
    ) {
    }

    public function periods(Request $request)
    {
        $items = BansosPeriod::query()
            ->withCount([
                'calonPenerimas as approved_count' => fn ($q) => $q->where('status_alur', 'disetujui_stasi'),
                'calonPenerimas as ranked_count' => fn ($q) => $q->where('status_alur', 'diranking_lingkungan_paroki'),
            ])
            ->orderByDesc('tahun')
            ->orderByDesc('id')
            ->get();

        return $this->success($items, 'Daftar periode ranking berhasil diambil.');
    }

    public function weights(Request $request, ?int $periodId = null)
    {
        $resolvedPeriodId = $this->resolvePeriodId($request, $periodId);
        $period = $resolvedPeriodId ? BansosPeriod::findOrFail($resolvedPeriodId) : null;

        $criteria = SawCriterion::query()
            ->with('weights')
            ->orderBy('id')
            ->get();

        $rows = $criteria->map(function (SawCriterion $criterion) use ($resolvedPeriodId) {
            $globalWeight = $criterion->weights->firstWhere('bansos_period_id', null);
            $periodWeight = $resolvedPeriodId
                ? $criterion->weights->firstWhere('bansos_period_id', $resolvedPeriodId)
                : null;

            $effectiveWeight = $periodWeight ? (float) $periodWeight->weight : (float) ($globalWeight->weight ?? 0);

            return [
                'key' => $criterion->key,
                'label' => $criterion->label,
                'type' => $criterion->type,
                'weight' => $effectiveWeight,
                'global_weight' => $globalWeight ? (float) $globalWeight->weight : 0.0,
                'period_weight' => $periodWeight ? (float) $periodWeight->weight : null,
            ];
        })->values();

        $payload = [
            'period' => $period,
            'period_id' => $resolvedPeriodId,
            'criteria' => $rows,
            'total_weight' => round((float) $rows->sum('weight'), 6),
            'use_global' => $resolvedPeriodId
                ? !$rows->contains(fn ($item) => $item['period_weight'] !== null)
                : false,
        ];

        return $this->success($payload, 'Konfigurasi bobot SAW berhasil diambil.');
    }

    public function saveWeights(Request $request, ?int $periodId = null)
    {
        $data = $request->validate([
            'period_id' => 'nullable|integer|exists:bansos_periods,id',
            'use_global' => 'nullable|boolean',
            'weights' => 'required|array|min:1',
        ]);

        $resolvedPeriodId = $periodId ?? ($data['period_id'] ?? null);
        $resolvedPeriodId = $resolvedPeriodId ? (int) $resolvedPeriodId : null;
        $useGlobal = (bool) ($data['use_global'] ?? false);

        $period = $resolvedPeriodId ? BansosPeriod::findOrFail($resolvedPeriodId) : null;
        if ($period && $period->is_locked) {
            return $this->error('Periode sudah terkunci. Bobot tidak dapat diubah.', 409);
        }

        $criteria = SawCriterion::query()->orderBy('id')->get()->keyBy('key');
        $weights = $data['weights'];

        $normalizedWeights = [];
        foreach ($weights as $key => $weightValue) {
            if (! $criteria->has($key)) {
                throw ValidationException::withMessages([
                    "weights.{$key}" => 'Kriteria tidak dikenal.',
                ]);
            }

            if (! is_numeric($weightValue)) {
                throw ValidationException::withMessages([
                    "weights.{$key}" => 'Bobot harus berupa angka.',
                ]);
            }

            $parsed = (float) $weightValue;
            if ($parsed < 0) {
                throw ValidationException::withMessages([
                    "weights.{$key}" => 'Bobot minimal 0.',
                ]);
            }

            $normalizedWeights[$key] = $parsed;
        }

        $totalWeight = array_sum($normalizedWeights);
        if (abs($totalWeight - 1.0) > 0.0001) {
            throw ValidationException::withMessages([
                'weights' => 'Total bobot harus tepat 1.00 (100%).',
            ]);
        }

        DB::transaction(function () use ($criteria, $normalizedWeights, $resolvedPeriodId, $useGlobal) {
            if ($resolvedPeriodId && $useGlobal) {
                SawWeight::query()
                    ->where('bansos_period_id', $resolvedPeriodId)
                    ->whereIn('saw_criterion_id', $criteria->pluck('id'))
                    ->delete();

                return;
            }

            foreach ($normalizedWeights as $key => $value) {
                SawWeight::updateOrCreate(
                    [
                        'saw_criterion_id' => $criteria->get($key)->id,
                        'bansos_period_id' => $resolvedPeriodId,
                    ],
                    ['weight' => $value]
                );
            }
        });

        $message = $resolvedPeriodId && $useGlobal
            ? 'Override bobot periode dihapus. Periode kini memakai bobot global.'
            : 'Bobot SAW berhasil disimpan.';

        return $this->success([
            'period_id' => $resolvedPeriodId,
            'use_global' => $useGlobal,
        ], $message);
    }

    public function preview(Request $request, ?int $periodId = null)
    {
        $resolvedPeriodId = $this->resolveRequiredPeriodId($request, $periodId);
        $period = BansosPeriod::findOrFail($resolvedPeriodId);

        $preview = $this->sawService->buildPreview($resolvedPeriodId);

        return $this->success([
            'period' => $period,
            'preview' => $preview,
        ], 'Preview perhitungan SAW berhasil diambil.');
    }

    public function execute(Request $request)
    {
        $data = $request->validate([
            'period_id' => 'required|integer|exists:bansos_periods,id',
        ]);

        $period = BansosPeriod::findOrFail((int) $data['period_id']);
        if ($period->is_locked) {
            return $this->error('Periode sudah terkunci. Eksekusi ranking ditolak.', 409);
        }

        DB::transaction(function () use ($period) {
            SawResult::query()->where('bansos_period_id', $period->id)->delete();

            CalonPenerima::query()
                ->withoutGlobalScopes()
                ->where('bansos_period_id', $period->id)
                ->where('status_alur', 'diranking_lingkungan_paroki')
                ->update(['status_alur' => 'disetujui_stasi']);

            CalonPenerima::query()
                ->withoutGlobalScopes()
                ->where('bansos_period_id', $period->id)
                ->update([
                    'saw_score' => 0,
                    'rank_global' => null,
                    'rank_internal_stasi' => null,
                ]);
        });

        $rows = $this->workflowService->triggerSaw($period->id, Auth::id());

        if ($period->status_periode === 'aktif') {
            $period->status_periode = 'proses_perankingan';
            $period->save();
        }

        return $this->success([
            'period' => $period->fresh(),
            'ranked_count' => $rows->count(),
            'rows' => $rows->values(),
        ], 'Perankingan SAW berhasil dijalankan.');
    }

    public function results(Request $request, ?int $periodId = null)
    {
        $resolvedPeriodId = $this->resolveRequiredPeriodId($request, $periodId);
        $period = BansosPeriod::findOrFail($resolvedPeriodId);

        $stasiId = $request->query('stasi_id');
        $top = max(0, (int) $request->query('top', 0));
        $sort = $request->query('sort', 'rank');

        $query = SawResult::query()
            ->with(['calon.stasi', 'calon.lingkunganStasi', 'createdBy'])
            ->where('bansos_period_id', $resolvedPeriodId);

        if ($stasiId) {
            $query->whereHas('calon', fn ($q) => $q->where('stasi_id', (int) $stasiId));
        }

        if ($sort === 'score') {
            $query->orderByDesc('score')->orderBy('rank');
        } else {
            $query->orderBy('rank');
        }

        if ($top > 0) {
            $query->limit($top);
        }

        $rows = $query->get();

        $mappedRows = $rows->map(function (SawResult $row) {
            return [
                'id' => $row->id,
                'calon_penerima_id' => $row->calon_penerima_id,
                'rank_global' => $row->rank,
                'rank_internal_stasi' => $row->calon?->rank_internal_stasi,
                'nik' => $row->calon?->nik,
                'nama_lengkap' => $row->calon?->nama_lengkap,
                'stasi_id' => $row->calon?->stasi_id,
                'stasi_nama' => $row->calon?->stasi?->nama_stasi,
                'lingkungan_stasi_nama' => $row->calon?->lingkunganStasi?->nama_lingkungan_stasi,
                'score' => (float) $row->score,
                'weights_used' => $row->weights_used,
                'created_at' => $row->created_at?->toDateTimeString(),
            ];
        })->values();

        $scores = $mappedRows->pluck('score')->filter(fn ($score) => $score !== null);
        $stats = [
            'total_ranked' => $mappedRows->count(),
            'average_score' => $scores->count() ? round((float) $scores->avg(), 4) : 0,
            'min_score' => $scores->count() ? round((float) $scores->min(), 4) : 0,
            'max_score' => $scores->count() ? round((float) $scores->max(), 4) : 0,
            'distribution_per_stasi' => $mappedRows
                ->groupBy('stasi_nama')
                ->map(fn ($items, $name) => ['stasi' => $name, 'count' => $items->count()])
                ->values(),
        ];

        return $this->success([
            'period' => $period,
            'filters' => [
                'period_id' => $resolvedPeriodId,
                'stasi_id' => $stasiId ? (int) $stasiId : null,
                'top' => $top,
                'sort' => $sort,
            ],
            'stasi_options' => Stasi::query()->orderBy('nama_stasi')->get(['id', 'nama_stasi']),
            'rows' => $mappedRows,
            'stats' => $stats,
        ], 'Hasil ranking SAW berhasil diambil.');
    }

    public function sendToParoki(Request $request, int $periodId)
    {
        $period = BansosPeriod::findOrFail($periodId);

        if (! SawResult::query()->where('bansos_period_id', $period->id)->exists()) {
            return $this->error('Belum ada hasil ranking untuk periode ini.', 422);
        }

        $ok = $this->workflowService->sendRankingToParoki($period->id, Auth::id());
        if (! $ok) {
            return $this->error('Gagal mengirim ranking ke paroki.', 409);
        }

        $period->status_periode = 'selesai';
        $period->save();

        return $this->success([
            'period' => $period->fresh(),
            'is_locked' => true,
        ], 'Ranking berhasil dikirim ke paroki dan periode dikunci.');
    }

    private function resolvePeriodId(Request $request, ?int $routePeriodId): ?int
    {
        if ($routePeriodId) {
            return $routePeriodId;
        }

        $periodId = $request->query('period_id', $request->input('period_id'));
        if ($periodId === null || $periodId === '') {
            return null;
        }

        return (int) $periodId;
    }

    private function resolveRequiredPeriodId(Request $request, ?int $routePeriodId): int
    {
        $periodId = $this->resolvePeriodId($request, $routePeriodId);
        if (! $periodId) {
            throw ValidationException::withMessages([
                'period_id' => 'period_id wajib diisi.',
            ]);
        }

        return $periodId;
    }
}

