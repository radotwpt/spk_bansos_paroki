<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalonPenerimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['ketua_lingkungan_stasi', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'bansos_period_id' => 'required|exists:bansos_periods,id',
            'nik' => 'required|string|max:16|min:16|unique:calon_penerimas,nik,NULL,id,bansos_period_id,' . $this->input('bansos_period_id'),
            'nama_lengkap' => 'required|string|max:150|min:3',
            'alamat_kristen' => 'nullable|string|max:500',
            'pendapatan_keluarga' => 'required|numeric|min:0',
            'jumlah_tanggungan' => 'required|integer|min:0',
            'status_tempat_tinggal' => 'required|in:milik_sendiri,sewa,numpang',
            'status_hubungan' => 'required|in:lajang,menikah,cerai',
            'urgensi_tambahan_tekstual' => 'nullable|string|max:1000',
            'stasi_id' => 'sometimes|exists:stasis,id',
            'lingkungan_stasi_id' => 'sometimes|exists:lingkungan_stasis,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.max' => 'NIK harus 16 digit.',
            'nik.min' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar untuk periode ini.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'pendapatan_keluarga.required' => 'Pendapatan keluarga wajib diisi.',
            'pendapatan_keluarga.min' => 'Pendapatan keluarga tidak boleh negatif.',
            'jumlah_tanggungan.required' => 'Jumlah tanggungan wajib diisi.',
            'status_tempat_tinggal.required' => 'Status tempat tinggal wajib diisi.',
            'status_hubungan.required' => 'Status hubungan wajib diisi.',
        ];
    }
}
