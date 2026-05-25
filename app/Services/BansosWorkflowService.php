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
        $ok = CalonPenerima::where('id', $calonId)->where('status_alur', 'draft')
            ->update(['status_alur' => 'diajukan_ke_stasi']) > 0;

        if ($ok) {
            $this->logger->log('submit_to_stasi', CalonPenerima::class, $calonId, $userId, ['to' => 'diajukan_ke_stasi']);
        }

        return $ok;
    }

    public function approveByStasi(int $calonId, int $userId): bool
    {
        $ok = CalonPenerima::where('id', $calonId)->where('status_alur', 'diajukan_ke_stasi')
            ->update(['status_alur' => 'disetujui_stasi']) > 0;

        if ($ok) {
            $this->logger->log('approve_by_stasi', CalonPenerima::class, $calonId, $userId, ['to' => 'disetujui_stasi']);
        }

        return $ok;
    }

    public function triggerSaw(int $periodId)
    {
        $res = $this->sawService->calculate($periodId);
        $this->logger->log('saw_executed', null, $periodId, null, ['result_count' => is_array($res) ? count($res) : null]);
        return $res;
    }

    public function sendRankingToParoki(int $periodId): bool
    {
        // This could set a flag or change state; for now we'll keep status as-is
        // and rely on finalizeParoki to mark winners.
        $this->logger->log('ranking_sent_to_paroki', null, $periodId, null, []);
        return true;
    }

    public function finalizeParoki(int $calonId, float $nominal, int $userId): bool
    {
        $ok = CalonPenerima::where('id', $calonId)->update([
            'is_penerima_sah' => true,
            'nominal_bansos_disetujui' => $nominal,
            'status_alur' => 'disetujui_paroki'
        ]) > 0;

        if ($ok) {
            $this->logger->log('finalize_paroki', CalonPenerima::class, $calonId, $userId, ['nominal' => $nominal]);
        }

        return $ok;
    }

    public function rejectData(int $calonId, string $reason, int $userId): bool
    {
        $ok = CalonPenerima::where('id', $calonId)->update([
            'status_alur' => 'ditolak'
        ]) > 0;

        if ($ok) {
            $this->logger->log('reject_data', CalonPenerima::class, $calonId, $userId, ['reason' => $reason]);
        }

        return $ok;
    }
}
