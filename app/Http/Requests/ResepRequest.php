<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResepRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk melakukan request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Anda bisa menambahkan logika otorisasi di sini jika perlu
    }

    /**
     * Mendefinisikan aturan validasi untuk request ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:pasiens,id',  // Pastikan patient_id ada di tabel pasien
            'poto_obat' => 'required|string|max:255',      // Poto_obat harus diisi dan berupa string dengan panjang maksimal 255 karakter
        ];
    }

    /**
     * Tentukan pesan error untuk aturan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Pasien ID harus diisi.',
            'patient_id.exists' => 'Pasien ID tidak ditemukan.',
            'poto_obat.required' => 'Poto Obat harus diisi.',
            'poto_obat.string' => 'Poto Obat harus berupa teks.',
            'poto_obat.max' => 'Poto Obat tidak boleh lebih dari 255 karakter.',
        ];
    }

    /**
     * Tentukan atribut khusus untuk pesan validasi (optional).
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'Pasien ID',
            'poto_obat' => 'Poto Obat',
        ];
    }
}
