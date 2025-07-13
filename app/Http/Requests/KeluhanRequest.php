<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KeluhanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sesuaikan otorisasi. Misalnya, hanya admin yang bisa menggunakan request ini untuk store manual.
        // return auth()->check() && auth()->user()->role === 'admin';
        return true;
    }

    public function rules(): array
    {
        return [
            // Jika ini digunakan untuk manual entry oleh admin, patient_id harus valid di tabel pasien
            'patient_id' => 'required|exists:pasien,id', // <<< UBAH: validasi ke tabel 'pasiens' dan kolom 'id'
            'keluhan' => 'required|string|max:255',
            'diagnosis' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Patient ID harus diisi.',
            'patient_id.exists' => 'Patient ID tidak ditemukan di tabel pasien.', // <<< UBAH PESAN
            'keluhan.required' => 'Keluhan harus diisi.',
            'keluhan.string' => 'Keluhan harus berupa teks.',
            'keluhan.max' => 'Keluhan tidak boleh lebih dari 255 karakter.',
            'diagnosis.string' => 'Diagnosis harus berupa teks.',
            'diagnosis.max' => 'Diagnosis tidak boleh lebih dari 255 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'Patient ID',
            'keluhan' => 'Keluhan',
            'diagnosis' => 'Diagnosis',
        ];
    }
}