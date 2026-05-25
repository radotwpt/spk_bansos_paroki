<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['super_admin','paroki','ketua_lingkungan_paroki','stasi','ketua_lingkungan_stasi'])],
            'stasi_id' => 'sometimes|nullable|exists:stasis,id',
            'lingkungan_paroki_id' => 'sometimes|nullable|exists:lingkungan_parokis,id',
            'lingkungan_stasi_id' => 'sometimes|nullable|exists:lingkungan_stasis,id',
        ];
    }
}
