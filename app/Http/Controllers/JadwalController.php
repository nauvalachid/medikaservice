<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Http\Requests\JadwalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Pastikan ini diimpor

class JadwalController extends Controller
{
    /**
     * Menampilkan semua jadwal atau jadwal spesifik untuk pasien yang terautentikasi.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Periksa apakah ada pengguna yang terautentikasi
        if (Auth::check()) {
            $user = Auth::user();

            // Jika pengguna adalah 'admin', tampilkan semua jadwal
            if ($user->role === 'admin') { // Asumsi ada kolom 'role' di tabel users
                $jadwals = Jadwal::all();
            }
            // Jika pengguna adalah 'pasien', tampilkan hanya jadwal mereka
            else if ($user->role === 'pasien') { // Asumsi ada kolom 'role' di tabel users
                // Asumsi model Jadwal memiliki foreign key 'user_id' yang terhubung ke ID pengguna.
                $jadwals = Jadwal::where('user_id', $user->id)->get();
            }
            // Untuk role lain yang terautentikasi, bisa disesuaikan
            else {
                // Default: tampilkan semua jadwal jika tidak ada batasan spesifik untuk role ini
                $jadwals = Jadwal::all();
            }
        } else {
            // Jika tidak ada pengguna yang terautentikasi, tampilkan semua jadwal.
            // Ini akan membuat endpoint ini dapat diakses publik untuk melihat semua jadwal.
            // Jika Anda ingin membatasi akses publik, pastikan rute ini dilindungi oleh middleware 'auth'.
            $jadwals = Jadwal::all();
        }

        return response()->json($jadwals);
    }

    /**
     * Menyimpan jadwal baru.
     *
     * @param  \App\Http\Requests\JadwalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JadwalRequest $request)
    {
        // Anda mungkin ingin menambahkan otorisasi di sini,
        // misalnya hanya admin atau dokter yang bisa membuat jadwal.
        // Contoh otorisasi:
        // if (!Auth::check() || Auth::user()->role !== 'admin') {
        //     return response()->json(['message' => 'Unauthorized action.'], 403);
        // }

        $jadwal = Jadwal::create($request->validated());

        return response()->json($jadwal, 201); // Status 201 Created
    }

    /**
     * Menampilkan jadwal berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Menampilkan jadwal berdasarkan ID
        $jadwal = Jadwal::findOrFail($id);

        // Anda mungkin ingin menambahkan otorisasi di sini,
        // misalnya pasien hanya bisa melihat jadwal mereka sendiri,
        // admin bisa melihat semua.
        // Contoh otorisasi:
        // if (Auth::check()) {
        //     $user = Auth::user();
        //     if ($user->role === 'pasien' && $jadwal->user_id !== $user->id) {
        //         return response()->json(['message' => 'Unauthorized access.'], 403);
        //     }
        // }

        return response()->json($jadwal);
    }

    /**
     * Mengupdate jadwal berdasarkan ID.
     *
     * @param  \App\Http\Requests\JadwalRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JadwalRequest $request, $id)
    {
        // Menemukan jadwal berdasarkan ID
        $jadwal = Jadwal::findOrFail($id);

        // Anda harus menambahkan otorisasi di sini,
        // misalnya hanya admin atau pemilik jadwal yang bisa mengupdate.
        // Contoh otorisasi:
        // if (!Auth::check() || (Auth::user()->role !== 'admin' && Auth::user()->id !== $jadwal->user_id)) {
        //     return response()->json(['message' => 'Unauthorized action.'], 403);
        // }

        $jadwal->update($request->validated());

        return response()->json($jadwal);
    }

    /**
     * Menghapus jadwal berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Menemukan jadwal berdasarkan ID dan menghapusnya
        $jadwal = Jadwal::findOrFail($id);

        // Anda harus menambahkan otorisasi di sini,
        // misalnya hanya admin atau pemilik jadwal yang bisa menghapus.
        // Contoh otorisasi:
        // if (!Auth::check() || (Auth::user()->role !== 'admin' && Auth::user()->id !== $jadwal->user_id)) {
        //     return response()->json(['message' => 'Unauthorized action.'], 403);
        // }

        $jadwal->delete();

        return response()->json(['message' => 'Jadwal deleted successfully']);
    }
}
