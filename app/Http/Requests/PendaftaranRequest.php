<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class PendaftaranRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk melakukan request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Anda bisa menambahkan logika otorisasi di sini jika diperlukan
    }

    /**
     * Mendefinisikan aturan validasi untuk request ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Mendapatkan role pengguna yang sedang login (lowercase untuk konsistensi)
        $userRole = Auth::check() ? strtolower(Auth::user()->role) : null;

        // Aturan dasar yang selalu berlaku atau untuk pasien
        $rules = [
            'jadwal_id' => 'required|exists:jadwal,id',
            'keluhan' => 'required|string|max:255',
            'durasi' => 'required|integer|min:1',
            'tanggal_pendaftaran' => 'required|date',
        ];

        // Jika request adalah PATCH/PUT (untuk update)
        // Dan jika pengguna adalah admin, kita hanya perlu validasi status.
        // Untuk pasien yang update, mereka tetap perlu semua field yang ada di rules dasar.
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            if ($userRole === 'admin') {
                // Untuk admin yang mengupdate status, hanya 'status' yang diperlukan
                // dan field lainnya menjadi tidak diperlukan
                $rules = [
                    'status' => 'required|string|in:pending,diterima,ditolak,selesai', // Tambahkan validasi status yang sesuai
                ];
            } else if ($userRole === 'pasien') {
                // Pasien saat update mungkin tidak selalu mengirim semua data,
                // jadi gunakan 'sometimes' agar hanya divalidasi jika ada
                $rules = [
                    'keluhan' => 'sometimes|required|string|max:255',
                    'durasi' => 'sometimes|required|integer|min:1',
                    'tanggal_pendaftaran' => 'sometimes|required|date',
                    // jadwal_id mungkin tidak boleh diubah setelah pendaftaran
                    // Jika boleh, ubah menjadi 'sometimes|required|exists:jadwal,id'
                ];
            }
        }
        // Jika request adalah POST (untuk store), aturan dasar sudah memadai

        return $rules;
    }

    // ... (metode messages dan attributes tetap sama)

     /**
      * Tentukan pesan error untuk aturan validasi.
      *
      * @return array<string, string>
      */
     public function messages(): array
     {
         return [
             'jadwal_id.required' => 'Jadwal ID harus diisi.',
             'jadwal_id.exists' => 'Jadwal ID tidak ditemukan.',
             'keluhan.required' => 'Keluhan harus diisi.',
             'keluhan.string' => 'Keluhan harus berupa teks.',
             'keluhan.max' => 'Keluhan tidak boleh lebih dari 255 karakter.',
             'durasi.required' => 'Durasi harus diisi.',
             'durasi.integer' => 'Durasi harus berupa angka.',
             'durasi.min' => 'Durasi harus lebih dari 0.',
             'tanggal_pendaftaran.required' => 'Tanggal pendaftaran harus diisi.',
             'tanggal_pendaftaran.date' => 'Tanggal pendaftaran harus berupa tanggal yang valid.',
             'status.required' => 'Status harus diisi.',
             'status.string' => 'Status harus berupa teks.',
             'status.in' => 'Status tidak valid. Pilihan yang tersedia: pending, diterima, ditolak, selesai.',
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
             'jadwal_id' => 'Jadwal ID',
             'keluhan' => 'Keluhan',
             'durasi' => 'Durasi',
             'tanggal_pendaftaran' => 'Tanggal Pendaftaran',
             'status' => 'Status',
         ];
     }
}