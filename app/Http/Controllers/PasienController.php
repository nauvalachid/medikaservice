<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Http\Requests\PasienRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasienController extends Controller
{
    /**
     * Menampilkan daftar pasien dengan pencarian berdasarkan nama.
     * Metode ini umumnya ditujukan untuk peran **Admin atau Dokter** yang membutuhkan akses ke semua data pasien.
     * Pasien individu tidak seharusnya menggunakan endpoint ini untuk melihat daftar pasien lain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            // Mencari pasien berdasarkan nama yang mengandung keyword pencarian
            $pasiens = Pasien::where('nama', 'like', '%' . $search . '%')->get();
        } else {
            // Jika tidak ada pencarian, tampilkan semua pasien
            $pasiens = Pasien::all();
        }

        return response()->json($pasiens);
    }

    /**
     * Menyimpan data pasien baru.
     * Metode ini idealnya digunakan oleh **Admin atau Staf Medis** untuk mendaftarkan pasien baru.
     * Seorang pasien yang sudah memiliki akun tidak perlu 'menambah' data pasien baru lagi;
     * mereka akan 'melengkapi' atau 'memperbarui' profil mereka sendiri.
     *
     * @param  \App\Http\Requests\PasienRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PasienRequest $request)
    {
        // Validasi dan simpan pasien baru
        $pasien = Pasien::create($request->validated());

        return response()->json($pasien, 201); // Status 201 Created
    }

    /**
     * Menampilkan data pasien berdasarkan ID.
     * Metode ini juga lebih cocok untuk **Admin atau Dokter** yang ingin melihat detail pasien tertentu.
     * Pasien individu seharusnya menggunakan `getAuthenticatedPasienProfile` untuk melihat data mereka sendiri.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Menampilkan pasien berdasarkan ID
        $pasien = Pasien::findOrFail($id);
        return response()->json($pasien);
    }

    /**
     * Mengupdate data pasien berdasarkan ID.
     * Sama seperti `show`, metode ini lebih ditujukan untuk **Admin atau Dokter**
     * yang memiliki hak untuk memodifikasi data pasien lain.
     * Pasien individu harus menggunakan `updateAuthenticatedPasienProfile`.
     *
     * @param  \App\Http\Requests\PasienRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PasienRequest $request, $id)
    {
        // Menemukan pasien berdasarkan ID
        $pasien = Pasien::findOrFail($id);

        // Memperbarui data pasien
        $pasien->update($request->validated());

        return response()->json($pasien);
    }

    /**
     * Menghapus data pasien berdasarkan ID.
     * Metode ini seharusnya hanya dapat diakses oleh **Admin atau Dokter**
     * untuk menghapus record pasien.
     * Pasien individu harus menggunakan `deleteAuthenticatedPasienProfile` untuk profil mereka.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Menemukan pasien berdasarkan ID dan menghapusnya
        $pasien = Pasien::findOrFail($id);
        $pasien->delete();

        return response()->json(['message' => 'Pasien deleted successfully']);
    }

    // --- Metode Khusus untuk Pasien Terautentikasi ---

    /**
     * Mengambil profil pasien yang sedang diautentikasi.
     * Hanya dapat diakses oleh pengguna dengan peran 'pasien'.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedPasienProfile()
    {
        $user = Auth::user(); // Mendapatkan user yang sedang login

        // Memastikan user yang login adalah pasien (meskipun sudah ada middleware 'role:pasien')
        if (strtolower($user->role) !== 'pasien') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya pasien yang dapat melihat profil mereka sendiri.'
            ], 403);
        }

        // Mengambil detail pasien yang terkait dengan user yang login
        // Asumsi: Ada relasi 'pasienDetail' di model User yang mengarah ke model Pasien
        $pasien = $user->pasienDetail;

        if (!$pasien) {
            return response()->json([
                'message' => 'Profil pasien tidak ditemukan untuk pengguna ini. Harap lengkapi profil Anda.'
            ], 404);
        }

        return response()->json([
            'message' => 'Profil pasien berhasil diambil.',
            'data' => $pasien // Mengembalikan objek pasien lengkap
        ], 200);
    }

    /**
     * Membuat atau melengkapi profil pasien yang sedang diautentikasi.
     * Ini digunakan jika pasien register dan belum memiliki record Pasien yang terkait.
     * Hanya dapat diakses oleh pengguna dengan peran 'pasien'.
     *
     * @param  \App\Http\Requests\PasienRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAuthenticatedPasienProfile(PasienRequest $request)
    {
        $user = Auth::user();

        if (strtolower($user->role) !== 'pasien') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya pasien yang dapat membuat atau melengkapi profil mereka sendiri.'
            ], 403);
        }

        // Cek apakah pasien sudah punya profil
        if ($user->pasienDetail) {
            return response()->json([
                'message' => 'Anda sudah memiliki profil pasien. Gunakan endpoint update untuk memperbarui.'
            ], 409); // 409 Conflict
        }

        // Buat profil pasien yang terhubung ke user yang sedang login
        $pasien = Pasien::create(array_merge(
            $request->validated(),
            ['user_id' => $user->id] // Hubungkan ke user saat ini
        ));

        return response()->json([
            'message' => 'Profil pasien berhasil dibuat.',
            'data' => $pasien
        ], 201);
    }

    /**
     * Mengupdate profil pasien yang sedang diautentikasi.
     * Hanya dapat diakses oleh pengguna dengan peran 'pasien'.
     *
     * @param  \App\Http\Requests\PasienRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAuthenticatedPasienProfile(PasienRequest $request)
    {
        $user = Auth::user();

        if (strtolower($user->role) !== 'pasien') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya pasien yang dapat memperbarui profil mereka sendiri.'
            ], 403);
        }

        $pasien = $user->pasienDetail;

        if (!$pasien) {
            return response()->json([
                'message' => 'Profil pasien tidak ditemukan untuk pengguna ini. Tidak dapat memperbarui.'
            ], 404);
        }

        // Memperbarui data profil pasien yang terautentikasi
        $pasien->update($request->validated());

        return response()->json([
            'message' => 'Profil pasien berhasil diperbarui.',
            'data' => $pasien
        ], 200);
    }

    /**
     * Menghapus profil pasien yang sedang diautentikasi.
     * Perhatian: Ini akan menghapus data pasien yang terkait.
     * Pastikan ini adalah fitur yang diinginkan atau tambahkan konfirmasi yang kuat.
     * Hanya dapat diakses oleh pengguna dengan peran 'pasien'.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAuthenticatedPasienProfile()
    {
        $user = Auth::user();

        if (strtolower($user->role) !== 'pasien') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya pasien yang dapat menghapus profil mereka sendiri.'
            ], 403);
        }

        $pasien = $user->pasienDetail;

        if (!$pasien) {
            return response()->json([
                'message' => 'Profil pasien tidak ditemukan untuk pengguna ini. Tidak dapat menghapus.'
            ], 404);
        }

        // Menghapus profil pasien yang terautentikasi
        $pasien->delete();

        return response()->json([
            'message' => 'Profil pasien Anda berhasil dihapus.'
        ], 200);
    }
}