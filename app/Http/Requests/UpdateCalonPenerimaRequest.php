<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalonPenerimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['ketua_lingkungan_stasi', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'sometimes|string|max:150|min:3',
            'alamat_kristen' => 'sometimes|nullable|string|max:500',
            'pendapatan_keluarga' => 'sometimes|numeric|min:0',
            'jumlah_tanggungan' => 'sometimes|integer|min:0',
            'status_tempat_tinggal' => 'sometimes|in:milik_sendiri,sewa,numpang',
            'status_hubungan' => 'sometimes|in:lajang,menikah,cerai',
            'urgensi_tambahan_tekstual' => 'sometimes|nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'pendapatan_keluarga.min' => 'Pendapatan keluarga tidak boleh negatif.',
            'status_tempat_tinggal.in' => 'Status tempat tinggal tidak valid.',
            'status_hubungan.in' => 'Status hubungan tidak valid.',
        ];
    }
}
