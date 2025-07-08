<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasienRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan untuk melakukan request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Set to true if you want to allow any user to make the request
    }

    /**
     * Mendefinisikan aturan validasi untuk request ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama'         => 'required|string|max:100', // Validasi nama pasien
            'tanggal_lahir'=> 'required|date', // Validasi tanggal
            'kelamin'      => 'required|in:laki-laki,perempuan', // Validasi jenis kelamin
            'alamat'       => 'required|string|max:255', // Validasi alamat
            'nomor_telepon'=> 'required|string|max:15', // Validasi nomor telepon
            'latitude'     => 'nullable|numeric|between:-90,90', // Validasi latitude
            'longitude'    => 'nullable|numeric|between:-180,180', // Validasi longitude
        ];
    }

    /**
     * Menentukan pesan error untuk aturan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required'         => 'Nama pasien harus diisi.',
            'tanggal_lahir.required'=> 'Tanggal lahir harus diisi.',
            'kelamin.required'      => 'Jenis kelamin harus diisi.',
            'kelamin.in'            => 'Jenis kelamin hanya boleh "laki-laki" atau "perempuan".',
            'alamat.required'       => 'Alamat harus diisi.',
            'nomor_telepon.required'=> 'Nomor telepon harus diisi.',
            'latitude.numeric'      => 'Latitude harus berupa angka.',
            'longitude.numeric'     => 'Longitude harus berupa angka.',
        ];
    }
}
