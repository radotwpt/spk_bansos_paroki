<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectCalonPenerimaRequest;
use App\Models\CalonPenerima;
use App\Services\BansosWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class StasiController extends Controller
{
    use AuthorizesRequests;
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;

    public function __construct(BansosWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    public function indexCalonPenerima(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && ! $user->stasi_id) {
            return $this->error('User belum terhubung ke stasi.', 422, [
                'stasi_id' => ['Stasi wajib tersedia untuk role ini.'],
            ]);
        }

        $items = CalonPenerima::query()
            ->when($user->role !== 'super_admin', fn ($query) => $query->where('stasi_id', $user->stasi_id))
            ->latest()
            ->get();

        return $this->success($items, 'Rekap calon penerima berhasil diambil.');
    }

    public function approveByStasi(Request $request, $id)
    {
        $calon = CalonPenerima::findOrFail($id);
        $this->authorize('approve', $calon);

        $user = $request->user();
        $ok = $this->workflow->approveByStasi((int) $id, $user->id);

        if (! $ok) {
            return $this->error('Calon penerima tidak dapat disetujui dari status saat ini.', 409);
        }

        return $this->success(['ok' => true], 'Calon penerima berhasil disetujui stasi.');
    }

    public function rejectByStasi(RejectCalonPenerimaRequest $request, $id)
    {
        $calon = CalonPenerima::findOrFail($id);
        $this->authorize('reject', $calon);

        $data = $request->validated();

        $user = $request->user();
        $ok = $this->workflow->rejectData((int) $id, $data['reason'], $user->id);

        if (! $ok) {
            return $this->error('Calon penerima tidak dapat ditolak dari status saat ini.', 409);
        }

        return $this->success(['ok' => true], 'Calon penerima berhasil ditolak.');
    }

    public function generateSuratPermohonan(Request $request)
    {
        // placeholder to generate surat using templates
        return $this->success(null, 'Surat permohonan berhasil dibuat.');
    }

    public function updateTemplateSurat(Request $request)
    {
        // placeholder to update template
        return $this->success(null, 'Template surat berhasil diperbarui.');
    }

    public function historyLogAndSubmissions(Request $request)
    {
        // placeholder return
        return $this->success([], 'Riwayat berhasil diambil.');
    }
}
