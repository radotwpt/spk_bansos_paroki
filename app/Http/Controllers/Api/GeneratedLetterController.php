<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateEdaranParokiLetterRequest;
use App\Http\Requests\GeneratePermohonanStasiLetterRequest;
use App\Models\BansosPeriod;
use App\Models\DocumentTemplate;
use App\Models\GeneratedLetter;
use App\Models\Stasi;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeneratedLetterController extends Controller
{
    use RespondsWithApi;

    public function __construct(protected DocumentService $docs)
    {
    }

    public function index(Request $request)
    {
        $query = GeneratedLetter::query()
            ->with(['template:id,name,type', 'period:id,nama_periode,tahun', 'generatedBy:id,name', 'calon:id,nama_lengkap,nik,stasi_id']);

        $user = $request->user();
        if ($user && ! in_array($user->role, ['super_admin', 'paroki'])) {
            $query->where('created_by', $user->id);
        }

        $query->when($request->query('jenis_surat'), fn ($q, $jenis) => $q->where('jenis_surat', $jenis));
        $query->when($request->query('period_id'), fn ($q, $periodId) => $q->where('bansos_period_id', (int) $periodId));
        $query->when($request->query('stasi_id'), function ($q, $stasiId) {
            $sid = (int) $stasiId;
            $q->where(function ($inner) use ($sid) {
                $inner->whereHas('calon', fn ($cq) => $cq->where('stasi_id', $sid))
                    ->orWhereJsonContains('metadata_json->stasi_ids', $sid)
                    ->orWhere('metadata_json->stasi_id', $sid);
            });
        });
        $query->when($request->query('date_from'), fn ($q, $dateFrom) => $q->whereDate('created_at', '>=', $dateFrom));
        $query->when($request->query('date_to'), fn ($q, $dateTo) => $q->whereDate('created_at', '<=', $dateTo));
        $query->when($request->query('q'), function ($q, $term) {
            $q->where(function ($inner) use ($term) {
                $inner->where('title', 'like', '%'.$term.'%')
                    ->orWhere('nomor_surat', 'like', '%'.$term.'%');
            });
        });

        $perPage = max(5, min(100, (int) $request->query('per_page', 20)));
        $items = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success($items, 'Daftar arsip surat berhasil diambil.');
    }

    public function periods()
    {
        $items = BansosPeriod::query()
            ->orderByDesc('tahun')
            ->orderByDesc('id')
            ->get(['id', 'nama_periode', 'tahun', 'status_periode']);

        return $this->success($items, 'Daftar periode surat berhasil diambil.');
    }

    public function stasis()
    {
        $items = Stasi::query()->orderBy('nama_stasi')->get(['id', 'nama_stasi', 'kode_stasi']);
        return $this->success($items, 'Daftar stasi berhasil diambil.');
    }

    public function show($id)
    {
        $letter = GeneratedLetter::with(['template', 'period', 'generatedBy', 'calon'])->findOrFail($id);
        return $this->success($letter, 'Detail surat berhasil diambil.');
    }

    public function destroy($id)
    {
        $letter = GeneratedLetter::findOrFail($id);

        if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
            Storage::disk('public')->delete($letter->file_path);
        }

        $letter->delete();
        return $this->success(null, 'Arsip surat berhasil dihapus.');
    }

    public function nextNumber(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:permohonan_stasi,edaran_paroki',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $nextNumber = $this->docs->getNextLetterNumber($data['type'], (int) $data['year']);

        return $this->success([
            'type' => $data['type'],
            'year' => (int) $data['year'],
            'next_number' => $nextNumber,
        ], 'Nomor surat berikutnya berhasil diambil.');
    }

    public function generatePermohonanStasi(GeneratePermohonanStasiLetterRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $template = DocumentTemplate::findOrFail($data['template_id']);
        $period = BansosPeriod::findOrFail($data['period_id']);

        $stasiId = $data['stasi_id'] ?? $user?->stasi_id;
        if (! $stasiId) {
            return $this->error('Stasi harus ditentukan untuk generate surat permohonan.', 422, [
                'stasi_id' => ['stasi_id wajib diisi untuk role ini.'],
            ]);
        }

        $stasi = Stasi::findOrFail((int) $stasiId);

        $letter = $this->docs->generatePermohonanStasi(
            $template,
            $period,
            $stasi,
            $user,
            $data['nomor_surat'] ?? null,
            $data['title'] ?? null
        );

        $letter = $this->docs->generatePdf($letter);

        return $this->success($letter->load(['template', 'period', 'generatedBy']), 'Surat permohonan stasi berhasil dibuat.', 201);
    }

    public function generateEdaranParoki(GenerateEdaranParokiLetterRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $template = DocumentTemplate::findOrFail($data['template_id']);
        $period = BansosPeriod::findOrFail($data['period_id']);

        $letter = $this->docs->generateEdaranParoki(
            $template,
            $period,
            $user,
            $data['stasi_ids'] ?? [],
            $data['nomor_surat'] ?? null,
            $data['title'] ?? null
        );

        $letter = $this->docs->generatePdf($letter);

        return $this->success($letter->load(['template', 'period', 'generatedBy']), 'Surat edaran paroki berhasil dibuat.', 201);
    }

    public function generateFromTemplate(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'calon_penerima_id' => 'nullable|exists:calon_penerimas,id',
            'bansos_period_id' => 'nullable|exists:bansos_periods,id',
            'title' => 'nullable|string',
            'extra' => 'nullable|array',
        ]);

        $template = DocumentTemplate::findOrFail($data['template_id']);
        $calon = isset($data['calon_penerima_id']) ? \App\Models\CalonPenerima::find($data['calon_penerima_id']) : null;

        $letter = $this->docs->generateLetter($template, $calon, [
            'bansos_period_id' => $data['bansos_period_id'] ?? null,
            'title' => $data['title'] ?? null,
            'created_by' => auth()->id(),
            'extra' => $data['extra'] ?? [],
        ]);

        return $this->success($letter, 'Surat berhasil dibuat dari template.', 201);
    }

    public function downloadPdf($id)
    {
        $letter = GeneratedLetter::findOrFail($id);
        if (! $letter->file_path || ! Storage::disk('public')->exists($letter->file_path)) {
            $letter = $this->docs->generatePdf($letter);
        }

        $absolute = Storage::disk('public')->path($letter->file_path);
        $downloadName = ($letter->nomor_surat ?: 'surat-'.$letter->id).'.pdf';
        $downloadName = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', $downloadName);

        return response()->download($absolute, $downloadName);
    }
}
