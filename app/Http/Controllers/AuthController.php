<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Pasien; // Import model Pasien
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Register a user with name, email, password, and role.
     *
     * @bodyParam name string required Nama pengguna. Example: john
     * @bodyParam email string required Email pengguna. Example: john@example.com
     * @bodyParam password string required Kata sandi minimal 6 karakter. Example: rahasia123
     * @bodyParam role string required Role yang tersedia. Example: pasien, admin
     *
     * @response 201 {
     * "status_code": 201,
     * "message": "User created successfully",
     * "data": {
     * "id": 1,
     * "name": "john",
     * "email": "john@example.com",
     * "role": "pasien",
     * "patient_id": 123 // ID pasien jika peran adalah pasien
     * }
     * }
     *
     * @response 400 {
     * "status_code": 400,
     * "message": "The email has already been taken.",
     * "data": null
     * }
     *
     * @response 500 {
     * "status_code": 500,
     * "message": "Internal server error"
     * }
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Buat user baru
            $user = new User;
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $user->role     = 'pasien'; // Peran diatur ke 'pasien' secara default
            $user->save();

            $patientId = null;
            // Jika peran adalah 'pasien', buat entri di tabel 'pasien'
            // Perhatikan penggunaan $user->role di sini akan mengembalikan 'pasien' (huruf kecil)
            // karena belum melewati accessor getRoleAttribute.
            if ($user->role === 'pasien') {
                $pasien = Pasien::create([
                    'user_id' => $user->id,
                    'nama' => $user->name, // Menyimpan nama user sebagai nama pasien
                    // Anda bisa menambahkan kolom lain seperti 'tanggal_lahir', 'kelamin', dll.,
                    // jika data tersebut tersedia di request register atau memiliki nilai default.
                    // Contoh: 'tanggal_lahir' => $request->input('tanggal_lahir'),
                ]);
                $patientId = $pasien->id; // Ambil ID dari entri pasien yang baru dibuat
            }

            // Format respons untuk menyertakan patient_id
            $responseData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => ucfirst($user->role), // Gunakan ucfirst() untuk mengkapitalisasi peran di respons
                'patient_id' => $patientId, // Tambahkan patient_id di sini
            ];

            // Mengembalikan respons sukses
            return response()->json([
                'status_code' => 201,
                'message' => 'User created successfully',
                'data'      => $responseData, // Kembalikan data yang sudah diformat
            ], 201);
        } catch (Exception $e) {
            // Mengembalikan respons gagal jika terjadi kesalahan
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Login
     *
     * @bodyParam email string required Email pengguna. Example: john@example.com
     * @bodyParam password string required Kata sandi pengguna. Example: rahasia123
     *
     * @response 200 {
     * "message": "Login berhasil",
     * "status_code": 200,
     * "data": {
     * "id": 1,
     * "name": "john",
     * "email": "john@example.com",
     * "role": "admin",
     * "patient_id": 123, // Contoh jika peran adalah pasien
     * "token": "eyJ0eXAiOiJKV1Qi..."
     * }
     * }
     *
     * @response 401 {
     * "message": "Email atau password salah",
     * "status_code": 401,
     * "data": null
     * }
     *
     * @response 500 {
     * "message": "Internal server error"
     * }
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
                'status_code' => 401,
                'data' => null
            ], 401);
        }

        try {
            // Muat user dengan relasi 'pasienDetail' (sesuai nama method di User model)
            $user = Auth::guard('api')->user()->load('pasienDetail'); // <--- PERBAIKAN DI SINI

            $patientId = null;
            // Cek apakah peran user adalah 'Pasien' (dengan 'P' kapital, sesuai accessor)
            // dan apakah relasi pasienDetail ada (tidak null)
            if ($user->role === 'Pasien' && $user->pasienDetail) { // <--- PERBAIKAN DI SINI
                $patientId = $user->pasienDetail->id; // Ambil ID dari model Pasien
            }

            $formatedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // $user->role sudah diformat oleh accessor
                'patient_id' => $patientId, // Tambahkan patient_id di sini
                'token' => $token
            ];

            return response()->json([
                'message' => 'Login berhasil',
                'status_code' => 200,
                'data' => $formatedUser
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Get current authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     * "message": "User ditemukan",
     * "status_code": 200,
     * "data": {
     * "id": 1,
     * "name": "john",
     * "email": "john@example.com",
     * "role": "admin",
     * "patient_id": 123 // Contoh jika peran adalah pasien
     * }
     * }
     */
    public function me()
    {
        try {
            // Muat user dengan relasi 'pasienDetail' (sesuai nama method di User model)
            $user = Auth::guard('api')->user()->load('pasienDetail'); // <--- PERBAIKAN DI SINI

            $patientId = null;
            // Cek apakah peran user adalah 'Pasien' (dengan 'P' kapital)
            // dan apakah relasi pasienDetail ada (tidak null)
            if ($user->role === 'Pasien' && $user->pasienDetail) { // <--- PERBAIKAN DI SINI
                $patientId = $user->pasienDetail->id;
            }

            $formatedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // $user->role sudah diformat oleh accessor
                'patient_id' => $patientId, // Tambahkan patient_id di sini
            ];

            return response()->json([
                'message' => 'User ditemukan',
                'status_code' => 200,
                'data' => $formatedUser
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @authenticated
     *
     * @response 200 {
     * "message": "Logout berhasil",
     * "status_code": 200,
     * "data": null
     * }
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Logout berhasil',
            'status_code' => 200,
            'data' => null
        ]);
    }
}