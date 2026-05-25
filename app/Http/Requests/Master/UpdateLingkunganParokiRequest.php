<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLingkunganParokiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('lingkungan_paroki');

        return [
            'nama_lingkungan_paroki' => 'sometimes|string|max:100',
            'kode_wilayah' => ['sometimes', 'string', 'max:20', Rule::unique('lingkungan_parokis', 'kode_wilayah')->ignore($id)],
        ];
    }
}
