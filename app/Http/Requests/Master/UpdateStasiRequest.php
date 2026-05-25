<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('stasi');

        return [
            'nama_stasi' => ['sometimes', 'string', 'max:100', Rule::unique('stasis', 'nama_stasi')->ignore($id)],
            'kode_stasi' => ['sometimes', 'string', 'max:20', Rule::unique('stasis', 'kode_stasi')->ignore($id)],
            'alamat' => 'sometimes|nullable|string',
        ];
    }
}
