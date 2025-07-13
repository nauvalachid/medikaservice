<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Penting: Tambahkan ini

class AdminController extends Controller
{
    /**
     * Menyimpan data admin baru.
     *
     * @param  \App\Http\Requests\AdminRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminRequest $request)
{
    try {
        $data = $request->validated();

        // Pastikan user_id diambil dari user yang terautentikasi
        $data['user_id'] = Auth::id(); // Ambil ID user yang sedang login

        // Cek apakah sudah ada profil admin untuk user yang terautentikasi
        $existingAdmin = Admin::where('user_id', $data['user_id'])->first();

        if ($existingAdmin) {
            return response()->json([
                'message' => 'Anda sudah memiliki profil admin.'
            ], 400); // Kembalikan respons 400 jika sudah ada profil admin
        }

        // Menyimpan foto profil jika ada
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $path = $file->store('public/foto_profil');
            $data['foto_profil'] = $path;
        }

        // Menyimpan data admin
        $admin = Admin::create($data);
        
        return response()->json([
            'message' => 'Admin data has been created successfully',
            'data' => $admin
        ], 201);
    } catch (\Exception $e) {
        Log::error('Error creating admin profile: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Menampilkan data admin berdasarkan user yang terautentikasi.
     * Ini adalah endpoint untuk 'profil saya'.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAuthenticatedAdminProfile()
{
    try {
        $userId = Auth::id();
        // Log 1: Periksa apakah Auth::id() mendapatkan ID pengguna
        Log::info('GET AUTHENTICATED ADMIN PROFILE: User ID from Auth::id() is ' . ($userId ?? 'NULL'));

        if (!$userId) { // Ini seharusnya tidak terjadi jika tidak ada "Unauthenticated"
            Log::warning('GET AUTHENTICATED ADMIN PROFILE: User is not authenticated.');
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Log 2: Periksa query yang akan dijalankan
        Log::info('GET AUTHENTICATED ADMIN PROFILE: Attempting to find Admin where user_id = ' . $userId);
        
        $admin = Admin::where('user_id', $userId)->first();

        if (!$admin) {
            // Log 3: Jika admin tidak ditemukan
            Log::warning('GET AUTHENTICATED ADMIN PROFILE: Admin profile NOT found for user_id ' . $userId);
            return response()->json([
                'message' => 'Admin profile not found for this user.'
            ], 404); // Not Found
        }

        // Log 4: Jika admin ditemukan
        Log::info('GET AUTHENTICATED ADMIN PROFILE: Admin profile FOUND for user_id ' . $userId, $admin->toArray());
        return response()->json([
            'message' => 'Admin profile fetched successfully',
            'data' => $admin
        ], 200); // OK
    } catch (\Exception $e) {
        Log::error('GET AUTHENTICATED ADMIN PROFILE ERROR: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Menampilkan data admin berdasarkan ID.
     * (Pertahankan ini jika Anda masih memerlukan endpoint untuk ID spesifik)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            return response()->json([
                'message' => 'Admin data fetched successfully',
                'data' => $admin
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Admin not found'
            ], 404);
        }
    }

    /**
     * Memperbarui data admin berdasarkan ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
{
    try {
        $userId = Auth::id(); // Mendapatkan user_id dari user yang sedang login

        // Pastikan admin yang login hanya dapat mengupdate profilnya sendiri
        $admin = Admin::where('user_id', $userId)->first(); // Cukup mencari admin berdasarkan user_id

        if (!$admin || $admin->id != $id) {
            return response()->json([
                'message' => 'Admin profile not found or unauthorized action.'
            ], 404); // Status 404: Not Found or Unauthorized
        }

        // Melanjutkan proses update seperti sebelumnya
        $data = $request->except('_method');
        if ($request->hasFile('foto_profil')) {
            if ($admin->foto_profil) {
                Storage::delete($admin->foto_profil);
            }
            $file = $request->file('foto_profil');
            $path = $file->store('public/foto_profil');
            $data['foto_profil'] = $path;
        }

        $updateSuccess = $admin->update($data);
        $admin->refresh();

        if ($updateSuccess) {
            return response()->json([
                'message' => 'Admin data updated successfully',
                'data' => $admin
            ], 200); // Status 200: OK
        } else {
            return response()->json([
                'message' => 'Failed to update admin data.'
            ], 500); // Status 500: Internal Server Error
        }
    } catch (\Exception $e) {
        Log::error('Error updating admin data:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json([
            'message' => 'Error: ' . $e->getMessage()
        ], 500); // Status 500: Internal Server Error
    }
}



    /**
     * Menghapus data admin berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            if ($admin->foto_profil) {
                Storage::delete($admin->foto_profil);
            }

            $admin->delete();

            return response()->json([
                'message' => 'Admin data deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}