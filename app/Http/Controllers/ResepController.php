<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use App\Http\Requests\ResepRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResepController extends Controller
{
    /**
     * Menyimpan resep baru.
     *
     * @param  \App\Http\Requests\ResepRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ResepRequest $request)
{
    // Pastikan hanya admin yang dapat menyimpan resep
    if (Auth::user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized access'], 403);
    }

    // Validasi dan simpan gambar (poto_obat)
    if ($request->hasFile('poto_obat') && $request->file('poto_obat')->isValid()) {
        $image = $request->file('poto_obat');
        
        // Menyimpan gambar ke direktori 'images/resep'
        $path = $image->store('images/resep', 'public');

        // Simpan data resep baru
        $resep = Resep::create([
            'patient_id' => $request->patient_id,  // Mengambil patient_id dari request
            'poto_obat' => $path,    // Menyimpan path gambar ke database
        ]);

        return response()->json($resep, 201);  // Mengembalikan data resep yang berhasil disimpan dengan status 201 Created
    }

    return response()->json(['message' => 'No image uploaded or invalid file'], 400);
}


    /**
     * Menampilkan riwayat resep pasien berdasarkan patient_id (untuk admin).
     *
     * @param  int  $patient_id
     * @return \Illuminate\Http\Response
     */
    public function showRiwayat($patient_id)
    {
        // Pastikan hanya admin yang dapat mengakses riwayat resep pasien
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Ambil data resep berdasarkan patient_id
        $resep = Resep::where('patient_id', $patient_id)->get();

        // Jika tidak ada resep untuk pasien tersebut
        if ($resep->isEmpty()) {
            return response()->json(['message' => 'No prescriptions found for this patient'], 404);
        }

        return response()->json($resep);  // Mengembalikan daftar resep pasien
    }

    /**
     * Menampilkan riwayat resep pasien yang sedang login (untuk pasien).
     *
     * @return \Illuminate\Http\Response
     */
    public function showPasienRiwayat()
    {
        // Pastikan pasien yang sedang login hanya bisa melihat resep mereka sendiri
        $patient_id = Auth::id();  // Mengambil patient_id berdasarkan user yang sedang login
        
        // Ambil data resep berdasarkan patient_id
        $resep = Resep::where('patient_id', $patient_id)->get();

        // Jika tidak ada resep untuk pasien tersebut
        if ($resep->isEmpty()) {
            return response()->json(['message' => 'No prescriptions found for this patient'], 404);
        }

        return response()->json($resep);  // Mengembalikan daftar resep pasien
    }

    /**
     * Menampilkan daftar resep yang sudah ada.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Ambil semua resep
        $resep = Resep::all();

        return response()->json($resep);  // Mengembalikan daftar semua resep
    }
}
