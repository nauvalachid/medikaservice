<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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
     *   "status_code": 201,
     *   "message": "User created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "john",
     *     "email": "john@example.com",
     *     "role": "admin"
     *   }
     * }
     *
     * @response 400 {
     *   "status_code": 400,
     *   "message": "The email has already been taken.",
     *   "data": null
     * }
     *
     * @response 500 {
     *   "status_code": 500,
     *   "message": "Internal server error"
     * }
     */
   public function register(RegisterRequest $request)
{
    try {
        // Buat user baru
        $user = new User;
        $user->name     = $request->name;  // Menggunakan 'name' sesuai permintaan
        $user->email    = $request->email;
        $user->password = Hash::make($request->password);
        $user->role     = 'pasien';  
        $user->save();

        // Mengembalikan respons sukses
        return response()->json([
            'status_code' => 201,
            'message' => 'User created successfully',
            'data'    => $user,
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
     *   "message": "Login berhasil",
     *   "status_code": 200,
     *   "data": {
     *     "id": 1,
     *     "name": "john",
     *     "email": "john@example.com",
     *     "role": "admin",
     *     "token": "eyJ0eXAiOiJKV1Qi..."
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Email atau password salah",
     *   "status_code": 401,
     *   "data": null
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
            $user = Auth::guard('api')->user();

            $formatedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,  // Directly access 'role' here instead of using $user->role->name
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
     *   "message": "User ditemukan",
     *   "status_code": 200,
     *   "data": {
     *     "id": 1,
     *     "name": "john",
     *     "email": "john@example.com",
     *     "role": "admin"
     *   }
     * }
     */
    public function me()
    {
        try {
            $user = Auth::guard('api')->user();

            $formatedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,  // Directly access 'role' here
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
     *   "message": "Logout berhasil",
     *   "status_code": 200,
     *   "data": null
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
