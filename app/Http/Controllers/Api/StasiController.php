<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectCalonPenerimaRequest;
use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\DocumentTemplate;
use App\Models\GeneratedLetter;
use App\Models\Stasi;
use App\Services\BansosWorkflowService;
use App\Services\DocumentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class StasiController extends Controller
{
    use AuthorizesRequests;
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;
    protected DocumentService $docs;

    public function __construct(BansosWorkflowService $workflow, DocumentService $docs)
    {
        $this->workflow = $workflow;
        $this->docs = $docs;
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
        $data = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'period_id' => 'required|exists:bansos_periods,id',
            'stasi_id' => 'nullable|exists:stasis,id',
            'nomor_surat' => 'nullable|string|max:255|unique:generated_letters,nomor_surat',
            'title' => 'nullable|string|max:255',
        ]);

        $template = DocumentTemplate::findOrFail($data['template_id']);
        $period = BansosPeriod::findOrFail($data['period_id']);
        $stasiId = $data['stasi_id'] ?? $request->user()->stasi_id;
        if (! $stasiId) {
            return $this->error('stasi_id wajib diisi untuk user ini.', 422, [
                'stasi_id' => ['stasi_id tidak ditemukan pada akun.'],
            ]);
        }
        $stasi = Stasi::findOrFail((int) $stasiId);

        $letter = $this->docs->generatePermohonanStasi(
            $template,
            $period,
            $stasi,
            $request->user(),
            $data['nomor_surat'] ?? null,
            $data['title'] ?? null
        );

        $letter = $this->docs->generatePdf($letter);

        return $this->success($letter->load(['template', 'period']), 'Surat permohonan berhasil dibuat.', 201);
    }

    public function updateTemplateSurat(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'name' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        $template = DocumentTemplate::findOrFail($data['template_id']);
        if (($template->type ?? null) !== 'permohonan_stasi') {
            return $this->error('Template ini bukan jenis permohonan stasi.', 422);
        }

        $template->update([
            'name' => $data['name'] ?? $template->name,
            'content' => $data['content'],
        ]);

        return $this->success($template->fresh(), 'Template surat berhasil diperbarui.');
    }

    public function historyLogAndSubmissions(Request $request)
    {
        $user = $request->user();
        $items = GeneratedLetter::query()
            ->with(['template:id,name,type', 'period:id,nama_periode,tahun'])
            ->where('jenis_surat', DocumentService::LETTER_TYPE_PERMOHONAN_STASI)
            ->where('created_by', $user->id)
            ->orderByDesc('created_at')
            ->paginate(max(5, min(100, (int) $request->query('per_page', 20))));

        return $this->success($items, 'Riwayat berhasil diambil.');
    }
}
