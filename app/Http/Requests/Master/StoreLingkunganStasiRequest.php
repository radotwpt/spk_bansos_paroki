<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreLingkunganStasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'stasi_id' => 'required|exists:stasis,id',
            'nama_lingkungan_stasi' => 'required|string|max:100',
            'kode_lingkungan' => 'required|string|max:20|unique:lingkungan_stasis,kode_lingkungan',
        ];
    }
}
