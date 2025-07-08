<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\Pendaftaran;
use App\Models\User; // Biasanya model user adalah App\Models\User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KeluhanController extends Controller
{
    public function showRiwayat($patient_id)
{
    // Pastikan admin yang mengakses
    if (strtolower(Auth::user()->role) !== 'admin') {  // Pastikan perbandingan tidak case-sensitive
        return response()->json(['message' => 'Unauthorized access'], 403);
    }

    // Ambil data keluhan berdasarkan patient_id
    $keluhan = Keluhan::where('patient_id', $patient_id)->get();

    if ($keluhan->isEmpty()) {
        return response()->json(['message' => 'No complaints found for this patient'], 404);
    }

    return response()->json($keluhan);
}

    public function store(Request $request)
{
    // Pastikan data pendaftaran sudah ada
    $pendaftaran = Pendaftaran::find($request->pendaftaran_id);
    if (!$pendaftaran) {
        return response()->json(['message' => 'Pendaftaran tidak ditemukan.'], 404);
    }

    // Simpan keluhan
    $keluhan = Keluhan::create([
        'patient_id' => $pendaftaran->patient_id,  // pastikan kita ambil patient_id dari pendaftaran
        'keluhan' => $request->keluhan,
        'durasi' => $request->durasi,
    ]);

    return response()->json($keluhan, 201);
}

}