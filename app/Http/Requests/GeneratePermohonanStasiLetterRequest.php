<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneratePermohonanStasiLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'exists:document_templates,id'],
            'period_id' => ['required', 'exists:bansos_periods,id'],
            'stasi_id' => ['nullable', 'exists:stasis,id'],
            'nomor_surat' => ['nullable', 'string', 'max:255', Rule::unique('generated_letters', 'nomor_surat')],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }
}

