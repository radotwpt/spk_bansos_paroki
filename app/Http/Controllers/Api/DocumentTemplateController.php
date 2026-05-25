<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\CalonPenerima;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    use RespondsWithApi;

    protected DocumentService $docs;

    public function __construct(DocumentService $docs)
    {
        $this->docs = $docs;
    }

    public function index()
    {
        return $this->success(DocumentTemplate::latest()->get(), 'Daftar template dokumen berhasil diambil.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:document_templates,slug',
            'type' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        $t = DocumentTemplate::create($data);

        return $this->success($t, 'Template dokumen berhasil dibuat.', 201);
    }

    public function show($id)
    {
        return $this->success(DocumentTemplate::findOrFail($id), 'Template dokumen berhasil diambil.');
    }

    public function update(Request $request, $id)
    {
        $t = DocumentTemplate::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string',
            'slug' => 'sometimes|string|unique:document_templates,slug,'.$t->id,
            'type' => 'nullable|string',
            'content' => 'nullable|string',
        ]);
        $t->update($data);

        return $this->success($t->fresh(), 'Template dokumen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $t = DocumentTemplate::findOrFail($id);
        $t->delete();

        return $this->success(null, 'Template dokumen berhasil dihapus.');
    }

    public function render(Request $request, $id, $calonId = null)
    {
        $t = DocumentTemplate::findOrFail($id);
        $calon = $calonId ? CalonPenerima::find($calonId) : null;
        $content = $this->docs->renderTemplate($t, $calon, $request->input('extra', []));

        return $this->success(['content' => $content], 'Template dokumen berhasil dirender.');
    }
}
