<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreLingkunganParokiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_lingkungan_paroki' => 'required|string|max:100',
            'kode_wilayah' => 'required|string|max:20|unique:lingkungan_parokis,kode_wilayah',
        ];
    }
}
