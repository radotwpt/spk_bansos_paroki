<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLingkunganStasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('lingkungan_stasi');

        return [
            'stasi_id' => 'sometimes|exists:stasis,id',
            'nama_lingkungan_stasi' => 'sometimes|string|max:100',
            'kode_lingkungan' => ['sometimes', 'string', 'max:20', Rule::unique('lingkungan_stasis', 'kode_lingkungan')->ignore($id)],
        ];
    }
}
