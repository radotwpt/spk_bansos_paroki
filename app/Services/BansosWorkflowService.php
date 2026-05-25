<?php

namespace App\Services;

use App\Models\CalonPenerima;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;

class BansosWorkflowService
{
    protected SawCalculationService $sawService;
    protected ActivityLogService $logger;

    public function __construct(SawCalculationService $sawService, ActivityLogService $logger)
    {
        $this->sawService = $sawService;
        $this->logger = $logger;
    }
    public function submitToStasi(int $calonId, int $userId): bool
    {
        $calon = CalonPenerima::find($calonId);
        if (! $calon) return false;

        if ($calon->status_alur !== 'draft') return false;

        $from = $calon->status_alur;
        $calon->status_alur = 'diajukan_ke_stasi';
        $ok = (bool) $calon->save();

        if ($ok) {
            $this->logger->log('submit_to_stasi', CalonPenerima::class, $calonId, $userId, ['from' => $from, 'to' => 'diajukan_ke_stasi']);
        }

        return $ok;
    }

    public function approveByStasi(int $calonId, int $userId): bool
    {
        $calon = CalonPenerima::find($calonId);
        if (! $calon) return false;

        if ($calon->status_alur !== 'diajukan_ke_stasi') return false;

        $from = $calon->status_alur;
        $calon->status_alur = 'disetujui_stasi';
        $ok = (bool) $calon->save();

        if ($ok) {
            $this->logger->log('approve_by_stasi', CalonPenerima::class, $calonId, $userId, ['from' => $from, 'to' => 'disetujui_stasi']);
        }

        return $ok;
    }

    public function triggerSaw(int $periodId, ?int $userId = null)
    {
        // capture current statuses before SAW updates
        $kandidats = CalonPenerima::where('bansos_period_id', $periodId)
            ->whereIn('status_alur', ['diajukan_ke_stasi', 'disetujui_stasi', 'diranking_lingkungan_paroki'])
            ->get(['id', 'status_alur']);

        $beforeMap = $kandidats->pluck('status_alur', 'id')->toArray();

        $res = $this->sawService->calculate($periodId);

        $count = is_countable($res) ? count($res) : null;

        $ids = collect($res)->pluck('id')->all();
        $afterItems = CalonPenerima::whereIn('id', $ids)->get()->keyBy('id');

        foreach ($res as $row) {
            $id = $row['id'] ?? null;
            $from = $beforeMap[$id] ?? null;
            $to = $afterItems->has($id) ? $afterItems->get($id)->status_alur : null;
            $score = $row['score'] ?? null;
            $rank = $afterItems->has($id) ? $afterItems->get($id)->rank_global : null;

            $this->logger->log('saw_ranking_updated', CalonPenerima::class, $id, $userId, [
                'from' => $from,
                'to' => $to,
                'score' => $score,
                'rank' => $rank,
            ]);
        }

        $this->logger->log('saw_executed', null, $periodId, $userId, ['result_count' => $count]);

        return $res;
    }

    public function sendRankingToParoki(int $periodId): bool
    {
        $this->logger->log('ranking_sent_to_paroki', null, $periodId, null, []);
        return true;
    }

    public function finalizeParoki(int $calonId, float $nominal, int $userId): bool
    {
        $calon = CalonPenerima::find($calonId);
        if (! $calon) return false;

        if (! in_array($calon->status_alur, ['diranking_lingkungan_paroki', 'disetujui_stasi'])) return false;

        $from = $calon->status_alur;
        $calon->is_penerima_sah = true;
        $calon->nominal_bansos_disetujui = $nominal;
        $calon->status_alur = 'disetujui_paroki';
        $ok = (bool) $calon->save();

        if ($ok) {
            $this->logger->log('finalize_paroki', CalonPenerima::class, $calonId, $userId, ['from' => $from, 'to' => 'disetujui_paroki', 'nominal' => $nominal]);
        }

        return $ok;
    }

    public function rejectData(int $calonId, string $reason, int $userId): bool
    {
        $calon = CalonPenerima::find($calonId);
        if (! $calon) return false;

        if ($calon->status_alur !== 'diajukan_ke_stasi') return false;

        $from = $calon->status_alur;
        $calon->status_alur = 'ditolak';
        $ok = (bool) $calon->save();

        if ($ok) {
            $this->logger->log('reject_data', CalonPenerima::class, $calonId, $userId, ['from' => $from, 'to' => 'ditolak', 'reason' => $reason]);
        }

        return $ok;
    }
}
