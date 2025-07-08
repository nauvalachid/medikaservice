<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Requests\AdminRequest; // Menggunakan AdminRequest untuk validasi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        // Validasi data sudah dilakukan oleh AdminRequest
        try {
            $data = $request->validated();

            // Menyimpan foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                $file = $request->file('foto_profil');
                $path = $file->store('public/foto_profil'); // Menyimpan gambar di folder storage/app/public/foto_profil
                $data['foto_profil'] = $path; // Menambahkan path gambar ke dalam data
            }

            // Menyimpan data admin
            $admin = Admin::create($data);
            
            return response()->json([
                'message' => 'Admin data has been created successfully',
                'data' => $admin
            ], 201);  // Status 201 Created
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);  // Internal Server Error
        }
    }

    /**
     * Menampilkan data admin berdasarkan ID.
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
            ], 200);  // Status 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Admin not found'
            ], 404);  // Not Found
        }
    }

    /**
     * Memperbarui data admin berdasarkan ID.
     *
     * @param  \App\Http\Requests\AdminRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   /**
     * Memperbarui data admin berdasarkan ID.
     *
     * @param  \Illuminate\Http\Request  $request // UBAH TIPE HINT MENJADI Illuminate\Http\Request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) // <-- UBAH DI SINI
    {
        // dd($request->all()); // Biarkan ini dulu untuk melihat apakah ada perubahan

        try {
            // 1. Ambil data admin sebelum update
            $admin = Admin::findOrFail($id);
            Log::info('Admin data BEFORE update:', $admin->toArray());

            // 2. Dapatkan data dari request secara langsung
            // KARENA validated() GAGAL, KITA AKAN AMBIL LANGSUNG DARI REQUEST
            // Kita akan mencoba semua input kecuali _method jika ada
            $data = $request->except('_method'); // Ambil semua input kecuali _method

            // Ini adalah VALIDASI MANUAL yang harus Anda tambahkan
            // jika Anda tidak menggunakan AdminRequest::validated()
            // Contoh validasi dasar:
            // if (isset($data['nama']) && !is_string($data['nama'])) {
            //     throw new \Exception("Nama harus berupa teks.");
            // }
            // if (isset($data['nama']) && empty($data['nama'])) {
            //     throw new \Exception("Nama harus diisi.");
            // }
            // DAN SETERUSNYA UNTUK SEMUA FIELD YANG ANDA BUTUHKAN

            Log::info('Raw data from request:', $data); // Log data yang diambil langsung

            // 3. Tangani upload foto profil jika ada
            // Penting: $request->hasFile() dan $request->file() tetap berfungsi
            if ($request->hasFile('foto_profil')) {
                // Hapus foto lama jika ada
                if ($admin->foto_profil) {
                    Storage::delete($admin->foto_profil);
                    Log::info('Old foto_profil deleted:', ['path' => $admin->foto_profil]);
                }

                $file = $request->file('foto_profil');
                $path = $file->store('public/foto_profil');
                $data['foto_profil'] = $path;
                Log::info('New foto_profil uploaded:', ['path' => $path]);
            } else {
                // Jika foto_profil tidak diupload dan ada di $data, hapus dari $data
                // agar tidak mengupdate foto_profil menjadi null jika tidak diinginkan
                // jika foto_profil bersifat opsional dan bisa null, ini tidak diperlukan
                // unset($data['foto_profil']);
            }


            // 4. Lakukan update data admin
            $updateSuccess = $admin->update($data);
            Log::info('Update operation result:', ['success' => $updateSuccess]);

            // 5. Refresh objek admin untuk mendapatkan data terbaru dari database
            $admin->refresh();
            Log::info('Admin data AFTER refresh:', $admin->toArray());

            // Jika update berhasil, kembalikan respons sukses
            if ($updateSuccess) {
                return response()->json([
                    'message' => 'Admin data updated successfully',
                    'data' => $admin
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to update admin data, no changes detected or internal issue.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating admin data:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
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

            // Hapus foto profil jika ada
            if ($admin->foto_profil) {
                Storage::delete($admin->foto_profil);
            }

            $admin->delete();

            return response()->json([
                'message' => 'Admin data deleted successfully'
            ], 200);  // Status 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);  // Internal Server Error
        }
    }
}
