<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\BansosWorkflowService;
use Illuminate\Http\Request;

class KetuaLingkunganParokiController extends Controller
{
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;

    public function __construct(BansosWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    public function executeSawRanking(Request $request, $periodId)
    {
        $user = $request->user();
        $result = $this->workflow->triggerSaw((int) $periodId, $user ? $user->id : null);

        return $this->success($result, 'Perankingan SAW berhasil dijalankan.');
    }

    public function sendRankingToParoki(Request $request, $periodId)
    {
        $user = $request->user();
        $ok = $this->workflow->sendRankingToParoki((int) $periodId, $user ? $user->id : null);

        return $this->success(['ok' => (bool) $ok], 'Ranking berhasil dikirim ke paroki.');
    }

    public function activityLogs(Request $request)
    {
        return $this->success([], 'Log aktivitas berhasil diambil.');
    }
}
