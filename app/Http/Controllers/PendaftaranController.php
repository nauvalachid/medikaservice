<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Http\Requests\PendaftaranRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class PendaftaranController extends Controller
{
    

    /**
     * Menyimpan data pendaftaran baru.
     *
     * @param  \App\Http\Requests\PendaftaranRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PendaftaranRequest $request)
    {
        // Menyimpan data pendaftaran baru
        $pendaftaran = Pendaftaran::create([
            'patient_id' => Auth::id(),  // Mengambil patient_id dari pasien yang sedang login
            'jadwal_id' => $request->jadwal_id,
            'keluhan' => $request->keluhan,
            'durasi' => $request->durasi,
            'tanggal_pendaftaran' => $request->tanggal_pendaftaran,
            'status' => 'pending',  // Status default bisa disesuaikan
        ]);

        return response()->json($pendaftaran, 201);  // Status 201 Created
    }

    /**
     * Mengupdate data pendaftaran pasien yang sedang login.
     *
     * @param  \App\Http\Requests\PendaftaranRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function update(PendaftaranRequest $request, $id) // Menggunakan PendaftaranRequest untuk validasi
    {
        Log::info('Memulai proses update pendaftaran.', ['request_id' => $id, 'user_id' => Auth::id()]);

        $pendaftaran = null;
        $userRole = Auth::check() ? strtolower(Auth::user()->role) : null; // Pastikan user login dan role di-lowercase

        // Logika untuk menemukan pendaftaran berdasarkan role
        if ($userRole === 'admin') {
            // Admin bisa mengupdate pendaftaran berdasarkan ID saja
            $pendaftaran = Pendaftaran::find($id);
            Log::info('Admin mencoba mencari pendaftaran.', ['pendaftaran_id_dicari' => $id, 'ditemukan' => (bool)$pendaftaran]);
        } else if ($userRole === 'pasien') {
            // Pasien hanya bisa mengupdate pendaftarannya sendiri
            $pendaftaran = Pendaftaran::where('patient_id', Auth::id())->find($id);
            Log::info('Pasien mencoba mencari pendaftaran.', ['pendaftaran_id_dicari' => $id, 'user_id' => Auth::id(), 'ditemukan' => (bool)$pendaftaran]);
        } else {
            // Role tidak valid atau tidak terautentikasi
            Log::warning('User bukan pasien atau admin atau tidak terautentikasi. Update tidak diizinkan.', ['user_role' => Auth::user()->role ?? 'guest', 'user_id' => Auth::id() ?? 'N/A']);
            return response()->json(['message' => 'Anda tidak memiliki izin untuk melakukan update ini atau tidak terautentikasi.'], 403);
        }

        if (!$pendaftaran) {
            Log::warning('Pendaftaran tidak ditemukan atau tidak memiliki akses.', ['request_id' => $id, 'user_id' => Auth::id(), 'user_role' => $userRole]);
            return response()->json(['message' => 'Pendaftaran tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        Log::info('Pendaftaran ditemukan.', ['pendaftaran_id' => $pendaftaran->id, 'current_role' => Auth::user()->role]);

        $updated = false; // Flag untuk melacak apakah update terjadi

        if ($userRole === 'pasien') {
            Log::info('User adalah pasien, mencoba update data pendaftaran.', ['requested_data' => $request->only(['keluhan', 'durasi', 'tanggal_pendaftaran'])]);
            $updated = $pendaftaran->update(
                $request->only(['keluhan', 'durasi', 'tanggal_pendaftaran']) // Pastikan hanya field yang diizinkan untuk pasien
            );
        } else if ($userRole === 'admin') {
            Log::info('User adalah admin, mencoba update status.', ['requested_status' => $request->status]);
            $updated = $pendaftaran->update([
                'status' => $request->status,
            ]);
        }

        if ($updated) {
            Log::info('Pendaftaran berhasil diupdate.', ['pendaftaran_id' => $pendaftaran->id, 'role' => $userRole]);
            // Refresh model setelah update untuk memastikan data respons terbaru
            $pendaftaran->refresh(); // atau $pendaftaran = $pendaftaran->fresh();
        } else {
            Log::error('Gagal mengupdate pendaftaran.', ['pendaftaran_id' => $pendaftaran->id, 'role' => $userRole, 'request_data' => $request->all()]);
        }

        return response()->json($pendaftaran);
    }
    /**
     * Menghapus data pendaftaran pasien yang sedang login.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Menemukan data pendaftaran berdasarkan ID dan pasien yang sedang login
        $pendaftaran = Pendaftaran::where('patient_id', Auth::id())->findOrFail($id);

        // Menghapus data pendaftaran
        $pendaftaran->delete();

        return response()->json(['message' => 'Pendaftaran deleted successfully']);
    }
}
