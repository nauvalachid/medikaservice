<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\KunjunganController; // Tidak digunakan di rute ini, bisa dihapus jika tidak ada
use App\Http\Controllers\ResepController;
use Illuminate\Support\Facades\Route;

// Registrasi dan Login (Akses Publik)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute Jadwal (Dapat Diakses Publik untuk melihat daftar jadwal, logika filtering ada di controller)
Route::get('/jadwals', [JadwalController::class, 'index']);
Route::get('/jadwals/{id}', [JadwalController::class, 'show']);

// ---

// Grup Rute yang Memerlukan Autentikasi (untuk semua pengguna yang login)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Pendaftaran: Pasien bisa membuat, melihat, dan mengupdate pendaftaran miliknya.
    // Admin juga dapat melihat semua pendaftaran melalui index.
    Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
    Route::get('/pendaftaran', [PendaftaranController::class, 'index']); // Di controller akan filter berdasarkan peran
    Route::get('/pendaftaran/{id}', [PendaftaranController::class, 'show']); // Di controller akan filter berdasarkan peran
    Route::put('/pendaftaran/{id}', [PendaftaranController::class, 'update']); // Di controller akan filter berdasarkan peran
    Route::delete('/pendaftaran/{id}', [PendaftaranController::class, 'destroy']); // Di controller akan filter berdasarkan peran

    // Rute Resep berdasarkan Pendaftaran ID (dapat diakses Admin dan Pasien dengan otorisasi internal controller)
    Route::get('/resep/pendaftaran/{pendaftaran_id}', [ResepController::class, 'showResepByPendaftaran']);

    // **RUTE BARU UNTUK EXPORT PDF**
    // Admin bisa mengekspor resep apapun
    // Pasien hanya bisa mengekspor resep miliknya sendiri
    Route::get('/resep/{resepId}/export-pdf', [ResepController::class, 'exportPdf']);
});

// ---

// Admin Routes
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Admin Profile
    Route::get('/admin/profile', [AdminController::class, 'getAuthenticatedAdminProfile']);
    Route::post('/admin', [AdminController::class, 'store']); // Membuat admin baru
    Route::get('/admin/{id}', [AdminController::class, 'show']);
    Route::put('/admin/{id}', [AdminController::class, 'update']);
    Route::delete('/admin/{id}', [AdminController::class, 'destroy']);

    // Pasien Management (CRUD oleh Admin)
    Route::resource('pasiens', PasienController::class); // Ini hanya untuk Admin

    // Jadwal Management (CRUD oleh Admin)
    Route::post('jadwals', [JadwalController::class, 'store']);
    Route::put('jadwals/{id}', [JadwalController::class, 'update']);
    Route::delete('jadwals/{id}', [JadwalController::class, 'destroy']);

    // Keluhan Management (Admin dapat melihat riwayat keluhan pasien tertentu)
    Route::get('/keluhan/{patient_id}', [KeluhanController::class, 'showRiwayat']);

    // Resep Management (Admin dapat membuat, melihat semua/tertentu, dan mengupdate resep)
    Route::post('/resep', [ResepController::class, 'store']); // Admin membuat resep
    Route::get('/resep', [ResepController::class, 'index']); // Admin melihat semua resep
    Route::put('/resep/{id}', [ResepController::class, 'update']); // Admin mengupdate resep
    Route::get('/resep/pasien/{patient_id}', [ResepController::class, 'showRiwayat']); // Admin melihat riwayat resep pasien tertentu
});

// ---

// Pasien Routes
Route::middleware(['auth:api', 'role:pasien'])->group(function () {
    // Pasien Profile
    Route::get('/pasien/profile', [PasienController::class, 'getAuthenticatedPasienProfile']);
    Route::post('/pasien/profile', [PasienController::class, 'createAuthenticatedPasienProfile']); // <--- BARIS INI DITAMBAHKAN
    Route::put('/pasien/profile', [PasienController::class, 'updateAuthenticatedPasienProfile']);
    Route::delete('/pasien/profile', [PasienController::class, 'deleteAuthenticatedPasienProfile']); // <--- BARIS INI DITAMBAHKAN JUGA

    // Keluhan (Pasien membuat keluhan dan melihat keluhan sendiri)
    Route::post('/keluhan', [KeluhanController::class, 'store']); // Pasien membuat keluhan
    Route::get('/keluhan', [KeluhanController::class, 'showKeluhanPasien']); // Pasien melihat keluhan sendiri

    // Resep (Pasien melihat riwayat resep sendiri)
    Route::get('/resep', [ResepController::class, 'showPasienRiwayat']);
});
