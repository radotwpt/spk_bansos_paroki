<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'sometimes|nullable|string|min:6',
            'role' => ['sometimes', Rule::in(['super_admin','paroki','ketua_lingkungan_paroki','stasi','ketua_lingkungan_stasi'])],
            'stasi_id' => [
                Rule::requiredIf(fn() => $this->role && in_array($this->role, ['stasi', 'ketua_lingkungan_stasi'])),
                'nullable',
                'exists:stasis,id'
            ],
            'lingkungan_paroki_id' => 'sometimes|nullable|exists:lingkungan_parokis,id',
            'lingkungan_stasi_id' => [
                Rule::requiredIf(fn() => $this->role && $this->role === 'ketua_lingkungan_stasi'),
                'nullable',
                'exists:lingkungan_stasis,id'
            ],
        ];
    }

    public function messages()
    {
        return [
            'stasi_id.required' => 'Stasi wajib dipilih untuk role ini.',
            'lingkungan_stasi_id.required' => 'Lingkungan Stasi wajib dipilih untuk role ini.',
        ];
    }
}
