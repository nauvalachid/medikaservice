<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KeluhanRequest extends FormRequest
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
            'patient_id' => 'required|exists:pendaftaran,patient_id',  // Validasi bahwa patient_id ada di tabel pendaftaran
            'keluhan' => 'required|string|max:255',      // Keluhan harus berupa string dan maksimal 255 karakter
            'durasi' => 'required|integer|min:1',        // Durasi harus berupa angka dan lebih dari 0
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
            'patient_id.required' => 'Patient ID harus diisi.',
            'patient_id.exists' => 'Patient ID tidak ditemukan di tabel pendaftaran.',
            'keluhan.required' => 'Keluhan harus diisi.',
            'keluhan.string' => 'Keluhan harus berupa teks.',
            'keluhan.max' => 'Keluhan tidak boleh lebih dari 255 karakter.',
            'durasi.required' => 'Durasi harus diisi.',
            'durasi.integer' => 'Durasi harus berupa angka.',
            'durasi.min' => 'Durasi harus lebih dari 0.',
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
            'patient_id' => 'Patient ID',
            'keluhan' => 'Keluhan',
            'durasi' => 'Durasi',
        ];
    }
}
