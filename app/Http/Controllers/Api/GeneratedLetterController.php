<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\CalonPenerima;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class GeneratedLetterController extends Controller
{
    use RespondsWithApi;

    protected DocumentService $docs;

    public function __construct(DocumentService $docs)
    {
        $this->docs = $docs;
    }

    public function index()
    {
        return $this->success(auth()->user()->generatedLetters()->latest()->get(), 'Daftar surat berhasil diambil.');
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
        $calon = isset($data['calon_penerima_id']) ? CalonPenerima::find($data['calon_penerima_id']) : null;

        $letter = $this->docs->generateLetter($template, $calon, [
            'bansos_period_id' => $data['bansos_period_id'] ?? null,
            'title' => $data['title'] ?? null,
            'created_by' => auth()->id(),
            'extra' => $data['extra'] ?? [],
        ]);

        return $this->success($letter, 'Surat berhasil dibuat dari template.', 201);
    }
}
