<?php

namespace App\Http\Controllers;

use App\Http\Requests\KeluhanRequest;
use App\Models\Keluhan;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KeluhanController extends Controller
{
    // Admin melihat riwayat keluhan pasien
    public function showRiwayat($patient_id)
    {
        if (!Auth::check() || strtolower(Auth::user()->role) !== 'admin') {
            Log::warning('Akses tidak sah ke riwayat keluhan.', ['user_id' => Auth::id() ?? 'N/A', 'requested_patient_id' => $patient_id]);
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $keluhan = Keluhan::where('patient_id', $patient_id)->get();

        if ($keluhan->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada riwayat keluhan untuk pasien ini.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Riwayat keluhan berhasil diambil.',
            'data' => $keluhan
        ], 200);
    }

    // Admin mencatat keluhan pasien yang sudah pernah berobat
    public function store(KeluhanRequest $request)
{
    try {
        $pernahBerobat = Pendaftaran::where('patient_id', $request->patient_id)->exists();

        if (!$pernahBerobat) {
            return response()->json([
                'message' => 'Pasien belum pernah berobat, tidak dapat mencatat keluhan.'
            ], 422);
        }

        $keluhan = Keluhan::create([
            'patient_id' => $request->patient_id,
            'keluhan' => $request->keluhan,
            'diagnosis' => $request->diagnosis,
        ]);

        return response()->json([
            'message' => 'Keluhan berhasil dicatat.',
            'data' => $keluhan
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan server saat mencatat keluhan.',
            'error' => $e->getMessage(), // ← tampilkan pesan error sebenarnya untuk debugging
        ], 500);
    }
}
}
