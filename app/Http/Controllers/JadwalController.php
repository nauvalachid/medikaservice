<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Http\Requests\JadwalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{

    /**
     * Menampilkan semua jadwal.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Menampilkan semua jadwal tanpa pembatasan role
        $jadwals = Jadwal::all();
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
        // Validasi dan simpan jadwal baru
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

        // Memperbarui data jadwal
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
        $jadwal->delete();

        return response()->json(['message' => 'Jadwal deleted successfully']);
    }
}
