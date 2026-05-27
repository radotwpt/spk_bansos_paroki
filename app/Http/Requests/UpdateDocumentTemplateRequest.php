<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templateId = $this->route('template') ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('document_templates', 'slug')->ignore($templateId)],
            'type' => ['sometimes', Rule::in(['permohonan_stasi', 'edaran_paroki'])],
            'content' => ['sometimes', 'string'],
        ];
    }
}

