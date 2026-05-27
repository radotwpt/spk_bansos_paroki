<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentTemplateRequest;
use App\Http\Requests\UpdateDocumentTemplateRequest;
use App\Models\DocumentTemplate;
use App\Services\ActivityLogService;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    use RespondsWithApi;

    public function __construct(
        protected DocumentService $docs,
        protected ActivityLogService $logger
    )
    {
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $q = $request->query('q');
        $perPage = max(5, min(100, (int) $request->query('per_page', 20)));

        $items = DocumentTemplate::query()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('slug', 'like', '%'.$q.'%');
                });
            })
            ->withCount('generatedLetters')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return $this->success($items, 'Daftar template dokumen berhasil diambil.');
    }

    public function store(StoreDocumentTemplateRequest $request)
    {
        $template = DocumentTemplate::create($request->validated());
        $this->logger->log('template_created', DocumentTemplate::class, $template->id, $request->user()?->id, [
            'slug' => $template->slug,
            'type' => $template->type,
        ]);

        return $this->success($template, 'Template dokumen berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $template = DocumentTemplate::withCount('generatedLetters')->findOrFail($id);
        return $this->success($template, 'Template dokumen berhasil diambil.');
    }

    public function update(UpdateDocumentTemplateRequest $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $template->update($request->validated());

        $this->logger->log('template_updated', DocumentTemplate::class, $template->id, $request->user()?->id, [
            'slug' => $template->slug,
            'type' => $template->type,
        ]);

        return $this->success($template->fresh(), 'Template dokumen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $template = DocumentTemplate::findOrFail($id);

        if ($template->generatedLetters()->exists()) {
            return $this->error(
                'Template tidak bisa dihapus karena sudah dipakai surat yang di-generate.',
                422,
                ['template' => ['Template sudah direferensikan oleh generated letter.']]
            );
        }

        $templateId = $template->id;
        $meta = [
            'slug' => $template->slug,
            'type' => $template->type,
        ];

        $template->delete();
        $this->logger->log('template_deleted', DocumentTemplate::class, $templateId, request()->user()?->id, $meta);

        return $this->success(null, 'Template dokumen berhasil dihapus.');
    }

    public function render(Request $request, $id, $calonId = null)
    {
        $template = DocumentTemplate::findOrFail($id);
        $extra = (array) $request->input('extra', []);
        $content = $this->docs->renderTemplate($template, null, $extra);

        return $this->success([
            'content' => $content,
            'placeholders' => $this->docs->getOfficialPlaceholders(),
        ], 'Template dokumen berhasil dirender.');
    }
}
