<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\CalonPenerima;
use App\Services\BansosWorkflowService;
use Illuminate\Http\Request;

class ParokiController extends Controller
{
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;

    public function __construct(BansosWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    public function viewRankedData(Request $request, $periodId)
    {
        $items = CalonPenerima::where('bansos_period_id', $periodId)
            ->orderByDesc('saw_score')
            ->get();

        return $this->success($items, 'Data ranking berhasil diambil.');
    }

    public function finalizeDecision(Request $request, $id)
    {
        $data = $request->validate([
            'nominal' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        $ok = $this->workflow->finalizeParoki((int) $id, (float) $data['nominal'], $user->id);

        if (! $ok) {
            return $this->error('Keputusan final tidak dapat disimpan.', 409);
        }

        return $this->success(['ok' => true], 'Keputusan final berhasil disimpan.');
    }

    public function generateSuratEdaran(Request $request)
    {
        return $this->success(null, 'Surat edaran berhasil dibuat.');
    }

    public function updateTemplateEdaran(Request $request)
    {
        return $this->success(null, 'Template edaran berhasil diperbarui.');
    }

    public function historyInformasiLengkap(Request $request)
    {
        return $this->success([], 'Riwayat informasi berhasil diambil.');
    }
}
