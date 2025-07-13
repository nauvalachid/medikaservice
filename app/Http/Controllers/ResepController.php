<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use App\Models\User; // Pastikan User diimpor jika digunakan di relasi pasien
use App\Models\Pasien; // Pastikan Pasien diimpor jika masih digunakan di tempat lain
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // Import Facade PDF

class ResepController extends Controller
{
    // Hapus metode prepareResepForResponse karena sudah ditangani oleh accessor di model Resep

    /**
     * Menampilkan daftar resep.
     * Hanya admin yang dapat melihat semua resep.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (strtolower(Auth::user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat semua resep.'
            ], 403);
        }

        // Laravel akan otomatis menambahkan 'poto_obat_url' karena $appends di model Resep
        $reseps = Resep::with('pendaftaran', 'pasien')->get();

        if ($reseps->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada resep yang tercatat.',
                'data' => []
            ], 200);
        }

        // Tidak perlu lagi memanggil prepareResepForResponse di sini
        return response()->json([
            'message' => 'Daftar resep berhasil diambil.',
            'data' => $reseps
        ], 200);
    }

    /**
     * Menyimpan resep baru.
     * Hanya admin yang dapat membuat resep.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (strtolower(Auth::user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk membuat resep.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:pasien,id',
            'pendaftaran_id' => 'nullable|exists:pendaftaran,id',
            'diagnosa' => 'required|string|max:255',
            'poto_obat' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'keterangan_obat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = $request->except('poto_obat');

            if ($request->hasFile('poto_obat')) {
                $path = $request->file('poto_obat')->store('resep_fotos', 'public');
                $data['poto_obat'] = $path; // Simpan path relatif ke database
            }

            $resep = Resep::create($data);

            // Tidak perlu lagi memanggil prepareResepForResponse di sini
            return response()->json([
                'message' => 'Resep berhasil ditambahkan.',
                'data' => $resep
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan resep.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail resep berdasarkan ID.
     * Admin bisa melihat resep apapun.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (strtolower(Auth::user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat detail resep ini.'
            ], 403);
        }

        // Laravel akan otomatis menambahkan 'poto_obat_url' karena $appends di model Resep
        $resep = Resep::with('pendaftaran', 'pasien')->find($id);

        if (!$resep) {
            return response()->json([
                'message' => 'Resep tidak ditemukan.'
            ], 404);
        }

        // Tidak perlu lagi memanggil prepareResepForResponse di sini
        return response()->json([
            'message' => 'Detail resep berhasil diambil.',
            'data' => $resep
        ], 200);
    }

    /**
     * Memperbarui resep berdasarkan ID.
     * Hanya admin yang dapat memperbarui resep.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (strtolower(Auth::user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk memperbarui resep.'
            ], 403);
        }

        $resep = Resep::find($id);

        if (!$resep) {
            return response()->json([
                'message' => 'Resep tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => 'nullable|exists:pasien,id',
            'pendaftaran_id' => 'nullable|exists:pendaftaran,id',
            'diagnosa' => 'nullable|string|max:255',
            'poto_obat' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'keterangan_obat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = $request->except('poto_obat');

            if ($request->hasFile('poto_obat')) {
                // Hapus foto lama jika ada, menggunakan path relatif dari database
                if ($resep->poto_obat && Storage::disk('public')->exists($resep->poto_obat)) {
                    Storage::disk('public')->delete($resep->poto_obat);
                }

                // Simpan foto baru dan dapatkan path relatif
                $path = $request->file('poto_obat')->store('resep_fotos', 'public');
                $data['poto_obat'] = $path; // Simpan path relatif ke database
            } else if ($request->has('poto_obat') && $request->poto_obat === null) {
                // Jika tidak ada file baru diupload, dan request secara eksplisit mengirim 'poto_obat' null
                if ($resep->poto_obat && Storage::disk('public')->exists($resep->poto_obat)) {
                    Storage::disk('public')->delete($resep->poto_obat);
                }
                $data['poto_obat'] = null;
            }

            $resep->update($data);

            // Tidak perlu lagi memanggil prepareResepForResponse di sini
            return response()->json([
                'message' => 'Resep berhasil diperbarui.',
                'data' => $resep
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui resep.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus resep.
     * Hanya admin yang dapat menghapus resep.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (strtolower(Auth::user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk menghapus resep.'
            ], 403);
        }

        $resep = Resep::find($id);

        if (!$resep) {
            return response()->json([
                'message' => 'Resep tidak ditemukan.'
            ], 404);
        }

        try {
            // Hapus file poto_obat dari storage jika ada, menggunakan path relatif
            if ($resep->poto_obat && Storage::disk('public')->exists($resep->poto_obat)) {
                Storage::disk('public')->delete($resep->poto_obat);
            }

            $resep->delete();
            return response()->json([
                'message' => 'Resep berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus resep.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan riwayat resep berdasarkan patient_id.
     * Ini adalah rute yang digunakan oleh admin untuk melihat resep pasien tertentu.
     *
     * @param  int  $patient_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showRiwayat($patient_id)
    {
        if (strtolower(Auth::user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat riwayat resep pasien lain.'
            ], 403);
        }

        $pasien = Pasien::find($patient_id);
        if (!$pasien) {
             return response()->json([
                 'message' => 'Pasien tidak ditemukan.'
               ], 404);
        }

        // Laravel akan otomatis menambahkan 'poto_obat_url' karena $appends di model Resep
        $reseps = Resep::where('patient_id', $pasien->id)->with('pendaftaran')->get();

        if ($reseps->isEmpty()) {
            return response()->json([
                'message' => 'Pasien ini belum memiliki resep.',
                'data' => []
            ], 200);
        }

        // Tidak perlu lagi memanggil prepareResepForResponse di sini
        return response()->json([
            'message' => 'Riwayat resep pasien berhasil diambil.',
            'data' => $reseps
        ], 200);
    }


    /**
     * Menampilkan riwayat resep untuk pasien yang sedang login.
     * Ini adalah rute yang digunakan oleh pasien itu sendiri.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showPasienRiwayat()
    {
        $user = Auth::user();

        if (strtolower($user->role) !== 'pasien') {
            return response()->json([
                'message' => 'Hanya pasien yang dapat mengakses data ini.'
            ], 403);
        }

        // Pastikan relasi pasienDetail sudah dimuat
        $user->load('pasienDetail');
        $pasien = $user->pasienDetail;

        if (!$pasien) {
            return response()->json([
                'message' => 'Profil pasien Anda tidak ditemukan. Silakan hubungi administrator.',
                'data' => []
            ], 404);
        }

        // Laravel akan otomatis menambahkan 'poto_obat_url' karena $appends di model Resep
        $reseps = Resep::where('patient_id', $pasien->id)->with('pendaftaran')->get();

        if ($reseps->isEmpty()) {
            return response()->json([
                'message' => 'Anda belum memiliki resep.',
                'data' => []
            ], 200);
        }

        // Tidak perlu lagi memanggil prepareResepForResponse di sini
        return response()->json([
            'message' => 'Riwayat resep berhasil diambil.',
            'data' => $reseps
        ], 200);
    }

    /**
     * Menampilkan daftar resep berdasarkan ID Pendaftaran.
     * Dapat diakses oleh admin atau pasien yang bersangkutan.
     *
     * @param  int  $pendaftaran_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showResepByPendaftaran($pendaftaran_id)
    {
        $user = Auth::user();

        $pendaftaran = Pendaftaran::find($pendaftaran_id);
        if (!$pendaftaran) {
            return response()->json([
                'message' => 'ID Pendaftaran tidak ditemukan.'
            ], 404);
        }

        if (strtolower($user->role) === 'admin') {
            // Laravel akan otomatis menambahkan 'poto_obat_url' karena $appends di model Resep
            $reseps = Resep::where('pendaftaran_id', $pendaftaran_id)->with('pendaftaran')->get();
            // Tidak perlu lagi memanggil prepareResepForResponse di sini
            return response()->json([
                'message' => 'Daftar resep untuk pendaftaran berhasil diambil.',
                'data' => $reseps
            ], 200);
        }

        if (strtolower($user->role) === 'pasien') {
            $pasien = $user->pasienDetail;

            if (!$pasien) {
                return response()->json([
                    'message' => 'Profil pasien Anda tidak ditemukan. Silakan hubungi administrator.',
                    'data' => []
                ], 404);
            }

            if ($pendaftaran->patient_id !== $pasien->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk melihat resep pendaftaran ini.'
                ], 403);
            }

            // Laravel akan otomatis menambahkan 'poto_obat_url' karena $appends di model Resep
            $reseps = Resep::where('pendaftaran_id', $pendaftaran_id)->with('pendaftaran')->get();
            if ($reseps->isEmpty()) {
                return response()->json([
                    'message' => 'Pendaftaran ini belum memiliki resep.',
                    'data' => []
                ], 200);
            }
            // Tidak perlu lagi memanggil prepareResepForResponse di sini
            return response()->json([
                'message' => 'Daftar resep untuk pendaftaran berhasil diambil.',
                'data' => $reseps
            ], 200);
        }

        return response()->json([
            'message' => 'Anda tidak memiliki akses.',
        ], 403);
    }

    /**
     * Export resep ke PDF.
     * Admin bisa mengekspor resep apapun.
     * Pasien hanya bisa mengekspor resep miliknya sendiri.
     *
     * @param  int  $resepId
     * @return \Illuminate\Http\Response
     */
    public function exportPdf($resepId)
    {
        $user = Auth::user();
        $resep = Resep::with('pendaftaran', 'pasien.user')->find($resepId); // Muat relasi yang diperlukan

        if (!$resep) {
            abort(404, 'Resep tidak ditemukan.');
        }

        // Otorisasi:
        // Admin bisa mengekspor resep apapun
        // Pasien hanya bisa mengekspor resep yang patient_id-nya sesuai dengan patient_id mereka
        if (strtolower($user->role) === 'pasien') {
            $user->load('pasienDetail'); // Pastikan relasi pasienDetail dimuat
            $loggedInPatientId = $user->pasienDetail->id ?? null;

            if ($resep->patient_id !== $loggedInPatientId) {
                abort(403, 'Anda tidak memiliki akses untuk mengekspor resep ini.');
            }
        } elseif (strtolower($user->role) !== 'admin') {
            // Jika bukan admin dan bukan pasien yang memiliki resep tersebut
            abort(403, 'Anda tidak memiliki akses untuk mengekspor resep.');
        }

        // Buat PDF dari view
        $pdf = Pdf::loadView('pdfs.resep_pdf', compact('resep'));

        // Kembalikan PDF sebagai download
        return $pdf->download('resep_medis_' . $resep->id . '.pdf');
    }
}
