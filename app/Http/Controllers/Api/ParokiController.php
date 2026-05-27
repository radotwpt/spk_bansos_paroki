<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\DocumentTemplate;
use App\Models\GeneratedLetter;
use App\Services\BansosWorkflowService;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class ParokiController extends Controller
{
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;
    protected DocumentService $docs;

    public function __construct(BansosWorkflowService $workflow, DocumentService $docs)
    {
        $this->workflow = $workflow;
        $this->docs = $docs;
    }

    public function viewRankedData(Request $request, $periodId)
    {
        $stasiId = $request->query('stasi_id');
        $top = max(0, (int) $request->query('top', 0));

        $query = CalonPenerima::query()
            ->withoutGlobalScopes()
            ->with(['stasi:id,nama_stasi', 'lingkunganStasi:id,nama_lingkungan_stasi'])
            ->where('bansos_period_id', $periodId)
            ->whereIn('status_alur', ['diranking_lingkungan_paroki', 'disetujui_paroki'])
            ->orderBy('rank_global');

        if ($stasiId) {
            $query->where('stasi_id', (int) $stasiId);
        }

        if ($top > 0) {
            $query->limit($top);
        }

        $items = $query->get();

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
        $data = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'period_id' => 'required|exists:bansos_periods,id',
            'stasi_ids' => 'nullable|array',
            'stasi_ids.*' => 'integer|exists:stasis,id',
            'nomor_surat' => 'nullable|string|max:255|unique:generated_letters,nomor_surat',
            'title' => 'nullable|string|max:255',
        ]);

        $template = DocumentTemplate::findOrFail($data['template_id']);
        $period = BansosPeriod::findOrFail($data['period_id']);

        $letter = $this->docs->generateEdaranParoki(
            $template,
            $period,
            $request->user(),
            $data['stasi_ids'] ?? [],
            $data['nomor_surat'] ?? null,
            $data['title'] ?? null
        );

        $letter = $this->docs->generatePdf($letter);

        return $this->success($letter->load(['template', 'period']), 'Surat edaran berhasil dibuat.', 201);
    }

    public function updateTemplateEdaran(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'name' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        $template = DocumentTemplate::findOrFail($data['template_id']);
        if (($template->type ?? null) !== 'edaran_paroki') {
            return $this->error('Template ini bukan jenis edaran paroki.', 422);
        }

        $template->update([
            'name' => $data['name'] ?? $template->name,
            'content' => $data['content'],
        ]);

        return $this->success($template->fresh(), 'Template edaran berhasil diperbarui.');
    }

    public function historyInformasiLengkap(Request $request)
    {
        $items = GeneratedLetter::query()
            ->with(['template:id,name,type', 'period:id,nama_periode,tahun'])
            ->where('jenis_surat', DocumentService::LETTER_TYPE_EDARAN_PAROKI)
            ->where('created_by', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(max(5, min(100, (int) $request->query('per_page', 20))));

        return $this->success($items, 'Riwayat informasi berhasil diambil.');
    }
}
