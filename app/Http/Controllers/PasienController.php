<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Http\Requests\PasienRequest;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    /**
     * Menampilkan daftar pasien dengan pencarian berdasarkan nama.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Menerima parameter pencarian (misalnya query string ?search=nama)
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
}
