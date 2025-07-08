<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk melakukan request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan aturan validasi untuk request ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Aturan validasi dasar untuk semua operasi
        $rules = [
            'nama' => 'string|max:255',
            'tanggal_lahir' => 'date',
            'kelamin' => 'in:laki-laki,perempuan',
            'alamat' => 'string|max:255',
            'nomor_telepon' => 'string|max:255',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        // Jika request adalah POST (untuk membuat data baru)
        if ($this->isMethod('post')) {
            $rules['user_id'] = 'required|exists:users,id';
            $rules['nama'] = 'required|string|max:255';
            $rules['tanggal_lahir'] = 'required|date';
            $rules['kelamin'] = 'required|in:laki-laki,perempuan';
            $rules['alamat'] = 'required|string|max:255';
            $rules['nomor_telepon'] = 'required|string|max:255';
            // foto_profil sudah nullable
        }

        // Jika request adalah PUT atau PATCH (untuk memperbarui data)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // 'user_id' mungkin tidak perlu diperbarui atau bisa menjadi 'sometimes'
            // Jika user_id tidak boleh diubah saat update, Anda bisa menghapusnya dari $rules di sini
            // atau membuatnya 'sometimes' agar hanya divalidasi jika dikirim.
            // Untuk kebanyakan kasus update admin, user_id tidak perlu diubah.
            $rules['user_id'] = 'sometimes|exists:users,id';

            // Bidang lainnya menjadi 'sometimes|required'
            // 'sometimes' berarti hanya divalidasi jika bidang tersebut ada dalam request
            // 'required' hanya akan diterapkan jika bidang itu ada.
            // Namun, dalam konteks update, 'sometimes' saja sudah cukup jika Anda ingin opsional
            // Jika Anda ingin field tersebut wajib ada JIKA DIKIRIMKAN, maka 'sometimes|string|max:255'
            // Jika Anda ingin field tersebut wajib ada SETIAP KALI UPDATE, maka tetap 'required'
            // Paling umum untuk update, gunakan 'sometimes' untuk bidang yang mungkin tidak selalu diupdate.
            $rules['nama'] = 'sometimes|string|max:255';
            $rules['tanggal_lahir'] = 'sometimes|date';
            $rules['kelamin'] = 'sometimes|in:laki-laki,perempuan';
            $rules['alamat'] = 'sometimes|string|max:255';
            $rules['nomor_telepon'] = 'sometimes|string|max:255';
            // 'foto_profil' sudah 'nullable' dan 'sometimes' tidak diperlukan jika sudah 'nullable'
            // karena 'nullable' sudah berarti opsional.
        }

        return $rules;
    }

    /**
     * Tentukan pesan error untuk aturan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID harus diisi.',
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'user_id.exists' => 'User ID tidak ditemukan.',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'kelamin.required' => 'Kelamin harus diisi.',
            'kelamin.in' => 'Kelamin harus salah satu dari "laki-laki" atau "perempuan".',
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'nomor_telepon.required' => 'Nomor telepon harus diisi.',
            'nomor_telepon.string' => 'Nomor telepon harus berupa teks.',
            'nomor_telepon.max' => 'Nomor telepon tidak boleh lebih dari 255 karakter.',
            'foto_profil.image' => 'Foto profil harus berupa gambar.',
            'foto_profil.mimes' => 'Foto profil harus berformat jpeg, png, jpg, gif, svg.',
            'foto_profil.max' => 'Foto profil maksimal 2MB.',
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
            'user_id' => 'User ID',
            'nama' => 'Nama',
            'tanggal_lahir' => 'Tanggal Lahir',
            'kelamin' => 'Jenis Kelamin',
            'alamat' => 'Alamat',
            'nomor_telepon' => 'Nomor Telepon',
            'foto_profil' => 'Foto Profil',
        ];
    }
}