<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\KunjunganController; // Import KunjunganController
use App\Http\Controllers\ResepController;
use Illuminate\Support\Facades\Route;

// Registrasi dan Login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Admin
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/admin', [AdminController::class, 'store']);
    Route::get('/admin/{id}', [AdminController::class, 'show']);
    Route::put('/admin/{id}', [AdminController::class, 'update']);
    Route::delete('/admin/{id}', [AdminController::class, 'destroy']);
    
    // Rute untuk operasi admin terkait pasien
    Route::resource('pasiens', PasienController::class);  // Menggunakan resource controller untuk CRUDS
    Route::get('pasiens/search', [PasienController::class, 'index']); // Menambahkan fitur search

    // Route untuk menampilkan semua jadwal
    Route::get('jadwals', [JadwalController::class, 'index']);
    Route::post('jadwals', [JadwalController::class, 'store']);
    Route::get('jadwals/{id}', [JadwalController::class, 'show']);
    Route::put('jadwals/{id}', [JadwalController::class, 'update']);
    Route::delete('jadwals/{id}', [JadwalController::class, 'destroy']);

    // Rute untuk mengupdate data pendaftaran pasien yang sedang login
    Route::put('/pendaftaran/status/{id}', [PendaftaranController::class, 'update']);
    Route::post('/keluhan', [KeluhanController::class, 'store']);  // Route untuk mencatat keluhan baru
    Route::get('/keluhan/{patient_id}', [KeluhanController::class, 'showRiwayat']);  // Route untuk melihat riwayat keluhan pasien

     // Route untuk mencatat resep baru
    Route::post('/resep', [ResepController::class, 'store']);
    
    // Route untuk melihat riwayat resep pasien berdasarkan patient_id
    Route::get('/resep/{patient_id}', [ResepController::class, 'showRiwayat']);
    
    // Route untuk melihat semua resep
    Route::get('/resep', [ResepController::class, 'index']);
});

// Pasien
Route::middleware(['auth:api', 'role:pasien'])->group(function () {
    Route::get('/pasien', [PasienController::class, 'show']);  // Menampilkan data pasien sendiri
    Route::put('/pasien/{id}', [PasienController::class, 'update']);  // Memperbarui data pasien
    Route::post('/pasien', [PasienController::class, 'store']);  // Menyimpan data pasien baru
    Route::get('/pasien/{id}', [PasienController::class, 'show']);  // Menampilkan data pasien berdasarkan ID
    
    // Menambahkan route untuk operasi pendaftaran pasien
    Route::get('/pendaftaran', [PendaftaranController::class, 'show']);   // Menampilkan pendaftaran pasien
    Route::post('/pendaftaran', [PendaftaranController::class, 'store']);  // Membuat pendaftaran baru
    Route::put('/pendaftaran/{id}', [PendaftaranController::class, 'update']);  // Memperbarui pendaftaran pasien
    Route::delete('/pendaftaran/{id}', [PendaftaranController::class, 'destroy']);  // Menghapus pendaftaran pasien

    // **Route untuk pasien melihat keluhan mereka sendiri**
    Route::get('/keluhan', [KeluhanController::class, 'showKeluhanPasien']); // Pasien hanya bisa melihat keluhan mereka sendiri

    // Route untuk melihat resep pasien yang sedang login
    Route::get('/resep', [ResepController::class, 'showPasienRiwayat']);
});
