<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Models\Pasien; // Import model Pasien
use App\Http\Requests\PendaftaranRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class PendaftaranController extends Controller
{
    /**
     * Menyimpan data pendaftaran baru.
     *
     * @param  \App\Http\Requests\PendaftaranRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PendaftaranRequest $request): JsonResponse
    {
        // Mengambil ID dari pengguna (user) yang sedang login dari tabel 'users'
        $loggedInUserId = Auth::id();

        // Mencari data pasien yang terkait dengan user_id yang sedang login
        // Asumsi: satu user memiliki satu data pasien di tabel 'pasien'
        $pasien = Pasien::where('user_id', $loggedInUserId)->first();

        // Memeriksa apakah data pasien ditemukan
        if (!$pasien) {
            Log::error('Gagal membuat pendaftaran: Data pasien untuk user ID ' . $loggedInUserId . ' tidak ditemukan di tabel pasien.', ['user_id' => $loggedInUserId]);
            return response()->json([
                'message' => 'Gagal membuat pendaftaran. Data pasien Anda tidak ditemukan. Harap pastikan akun Anda terdaftar sebagai pasien.'
            ], 400); // Menggunakan status 400 Bad Request
        }

        // Sekarang kita memiliki ID pasien yang benar dari tabel 'pasien'
        $patientIdForPendaftaran = $pasien->id;

        try {
            // Menyimpan data pendaftaran baru
            $pendaftaran = Pendaftaran::create([
                'patient_id' => $patientIdForPendaftaran, // Menggunakan ID pasien yang ditemukan
                'jadwal_id' => $request->jadwal_id,
                'keluhan' => $request->keluhan,
                'durasi' => $request->durasi,
                'tanggal_pendaftaran' => $request->tanggal_pendaftaran,
                'status' => 'pending',  // Status default, bisa disesuaikan
            ]);

            Log::info('Pendaftaran baru berhasil dibuat.', ['pendaftaran_id' => $pendaftaran->id, 'patient_id' => $patientIdForPendaftaran, 'user_id' => $loggedInUserId]);
            return response()->json([
                'message' => 'Pendaftaran berhasil dibuat.',
                'data' => $pendaftaran
            ], 201); // Status 201 Created

        } catch (\Exception $e) {
            // Tangani error jika ada masalah saat menyimpan pendaftaran
            Log::error('Terjadi kesalahan saat menyimpan pendaftaran: ' . $e->getMessage(), [
                'user_id' => $loggedInUserId,
                'request_data' => $request->all(),
                'exception' => $e
            ]);
            return response()->json([
                'message' => 'Gagal membuat pendaftaran. Terjadi kesalahan server.'
            ], 500); // Status 500 Internal Server Error
        }
    }

    /**
     * Mengupdate data pendaftaran pasien yang sedang login.
     *
     * @param  \App\Http\Requests\PendaftaranRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function update(PendaftaranRequest $request, $id)
{
    Log::info('Memulai proses update pendaftaran.', ['request_id' => $id, 'user_id' => Auth::id()]);

    $pendaftaran = null;
    $userRole = Auth::check() ? strtolower(Auth::user()->role) : null;

    // Logika untuk mencari pendaftaran berdasarkan role
    if ($userRole === 'admin') {
        // Admin dapat mengakses semua pendaftaran berdasarkan ID
        $pendaftaran = Pendaftaran::find($id);
        Log::info('Admin mencoba mencari pendaftaran.', ['pendaftaran_id_dicari' => $id, 'ditemukan' => (bool)$pendaftaran]);
    } else if ($userRole === 'pasien') {
        // Dapatkan pasien ID dari user yang sedang login
        $pasien = Pasien::where('user_id', Auth::id())->first();

        if (!$pasien) {
            Log::warning('Pasien tidak ditemukan untuk user ID.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'Data pasien tidak ditemukan.'], 404);
        }

        // Cari pendaftaran yang hanya milik pasien tersebut
        $pendaftaran = Pendaftaran::where('patient_id', $pasien->id)->find($id);
        Log::info('Pasien mencoba mencari pendaftaran.', ['pendaftaran_id_dicari' => $id, 'patient_id' => $pasien->id, 'ditemukan' => (bool)$pendaftaran]);
    } else {
        // Role tidak valid atau tidak login
        Log::warning('User bukan pasien atau admin atau tidak terautentikasi.', [
            'user_role' => Auth::user()->role ?? 'guest',
            'user_id' => Auth::id() ?? 'N/A'
        ]);
        return response()->json(['message' => 'Anda tidak memiliki izin untuk melakukan update ini.'], 403);
    }

    if (!$pendaftaran) {
        Log::warning('Pendaftaran tidak ditemukan atau user tidak memiliki akses.', [
            'request_id' => $id,
            'user_id' => Auth::id(),
            'user_role' => $userRole
        ]);
        return response()->json(['message' => 'Pendaftaran tidak ditemukan atau Anda tidak memiliki akses.'], 404);
    }

    Log::info('Pendaftaran ditemukan.', ['pendaftaran_id' => $pendaftaran->id, 'role' => $userRole]);

    $updated = false;

    if ($userRole === 'pasien') {
        Log::info('Pasien mengupdate data.', ['data' => $request->only(['keluhan', 'durasi', 'tanggal_pendaftaran'])]);

        $updated = $pendaftaran->update(
            $request->only(['jadwal_id','keluhan', 'durasi', 'tanggal_pendaftaran'])
        );
    } else if ($userRole === 'admin') {
        Log::info('Admin mengupdate status.', ['status' => $request->status]);

        $updated = $pendaftaran->update([
            'status' => $request->status,
        ]);
    }

    if ($updated) {
        Log::info('Pendaftaran berhasil diupdate.', ['pendaftaran_id' => $pendaftaran->id]);
        $pendaftaran->refresh();
        return response()->json([
            'message' => 'Pendaftaran berhasil diupdate.',
            'data' => $pendaftaran
        ]);
    } else {
        Log::error('Gagal mengupdate pendaftaran.', ['pendaftaran_id' => $pendaftaran->id]);
        return response()->json([
            'message' => 'Tidak ada perubahan atau update gagal.'
        ], 400);
    }
}

    /**
     * Menghapus data pendaftaran pasien yang sedang login.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Dapatkan user yang sedang login
        $loggedInUserId = Auth::id();
        $userRole = Auth::check() ? strtolower(Auth::user()->role) : null;

        if (!$loggedInUserId) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $pendaftaran = null;

        if ($userRole === 'admin') {
            // Admin bisa menghapus pendaftaran apapun
            $pendaftaran = Pendaftaran::find($id);
        } elseif ($userRole === 'pasien') {
            // Dapatkan ID pasien dari user yang sedang login
            $pasien = Pasien::where('user_id', $loggedInUserId)->first();

            if (!$pasien) {
                return response()->json(['message' => 'Data pasien tidak ditemukan.'], 404);
            }
            // Pasien hanya bisa menghapus pendaftaran miliknya
            $pendaftaran = Pendaftaran::where('patient_id', $pasien->id)->find($id);
        } else {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'], 403);
        }

        if (!$pendaftaran) {
            return response()->json(['message' => 'Pendaftaran tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        // Menghapus data pendaftaran
        try {
            $pendaftaran->delete();
            Log::info('Pendaftaran berhasil dihapus.', ['pendaftaran_id' => $id, 'user_id' => $loggedInUserId, 'role' => $userRole]);
            return response()->json(['message' => 'Pendaftaran berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pendaftaran: ' . $e->getMessage(), ['pendaftaran_id' => $id, 'user_id' => $loggedInUserId, 'exception' => $e]);
            return response()->json(['message' => 'Gagal menghapus pendaftaran. Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Menampilkan semua data pendaftaran berdasarkan peran pengguna.
     * Jika admin, tampilkan semua. Jika pasien, tampilkan hanya milik sendiri.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        // Memeriksa apakah pengguna terautentikasi
        if (!Auth::check()) {
            Log::warning('Akses tidak sah: Pengguna tidak terautentikasi mencoba melihat pendaftaran.');
            return response()->json(['message' => 'Anda harus login untuk melihat pendaftaran.'], 401); // Unauthorized
        }

        $user = Auth::user();
        $userRole = strtolower($user->role);

        if ($userRole === 'admin') {
            // Admin: Ambil semua data pendaftaran
            $pendaftarans = Pendaftaran::all();
            Log::info('Admin berhasil mengambil semua data pendaftaran.');
            return response()->json($pendaftarans);
        } elseif ($userRole === 'pasien') {
            // Pasien: Ambil ID pasien yang terkait dengan user yang login
            $pasien = Pasien::where('user_id', $user->id)->first();

            if (!$pasien) {
                Log::warning('Data pasien tidak ditemukan untuk user ID.', ['user_id' => $user->id]);
                return response()->json(['message' => 'Data pasien Anda tidak ditemukan. Harap pastikan akun Anda terdaftar sebagai pasien.'], 404);
            }

            // Ambil pendaftaran yang hanya dimiliki oleh pasien tersebut
            $pendaftarans = Pendaftaran::where('patient_id', $pasien->id)->get();
            Log::info('Pasien berhasil mengambil data pendaftaran miliknya sendiri.', ['patient_id' => $pasien->id, 'user_id' => $user->id]);
            return response()->json($pendaftarans);
        } else {
            // Peran lain atau tidak dikenal
            Log::warning('Akses tidak sah untuk melihat pendaftaran.', ['user_id' => $user->id, 'user_role' => $userRole]);
            return response()->json(['message' => 'Anda tidak memiliki izin untuk melihat pendaftaran.'], 403); // Forbidden
        }
    }
}