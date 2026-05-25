<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreBansosPeriodRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_periode' => 'required|string|max:100',
            'tahun' => 'required|integer|min:2000|max:9999',
            'status_periode' => 'sometimes|in:aktif,proses_perankingan,selesai,arsip',
        ];
    }
}
