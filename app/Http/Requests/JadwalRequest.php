<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JadwalRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk melakukan request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Set to true to allow any user, or add authorization logic if needed
    }

    /**
     * Mendefinisikan aturan validasi untuk request ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_bidan' => 'required|string|max:255',  // Nama bidan wajib diisi dan harus string dengan panjang maksimal 255 karakter
            'tanggal'    => 'required|date',             // Tanggal wajib diisi dan harus berupa tanggal yang valid
            'start_time' => 'required|date_format:H:i',  // Jam mulai wajib diisi dan harus dalam format HH:MM
            'end_time'   => 'required|date_format:H:i|after:start_time', // Jam selesai wajib diisi dan harus setelah jam mulai
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
            'nama_bidan.required' => 'Nama bidan harus diisi.',
            'nama_bidan.string'   => 'Nama bidan harus berupa teks.',
            'nama_bidan.max'      => 'Nama bidan tidak boleh lebih dari 255 karakter.',
            'tanggal.required'    => 'Tanggal harus diisi.',
            'tanggal.date'        => 'Tanggal harus berupa tanggal yang valid.',
            'start_time.required' => 'Jam mulai harus diisi.',
            'start_time.date_format' => 'Jam mulai harus dalam format HH:MM.',
            'end_time.required'   => 'Jam selesai harus diisi.',
            'end_time.date_format' => 'Jam selesai harus dalam format HH:MM.',
            'end_time.after'      => 'Jam selesai harus setelah jam mulai.',
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
            'nama_bidan' => 'Nama Bidan',
            'tanggal'    => 'Tanggal Jadwal',
            'start_time' => 'Jam Mulai',
            'end_time'   => 'Jam Selesai',
        ];
    }
}
