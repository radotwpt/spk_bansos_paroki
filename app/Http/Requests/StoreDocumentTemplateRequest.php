<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('document_templates', 'slug')],
            'type' => ['required', Rule::in(['permohonan_stasi', 'edaran_paroki'])],
            'content' => ['required', 'string'],
        ];
    }
}

