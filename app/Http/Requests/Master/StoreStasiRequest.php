<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreStasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_stasi' => 'required|string|max:100|unique:stasis,nama_stasi',
            'kode_stasi' => 'required|string|max:20|unique:stasis,kode_stasi',
            'alamat' => 'nullable|string',
        ];
    }
}
