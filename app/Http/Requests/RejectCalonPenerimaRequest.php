<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectCalonPenerimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['stasi', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:1000|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 5 karakter.',
            'reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ];
    }
}
