<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required Nama pengguna yang unik. Contoh: JohnDoe
 * @bodyParam email string required Alamat email yang unik. Contoh: john@example.com
 * @bodyParam password string required Kata sandi minimal 6 karakter. Contoh: rahasia123
 * @bodyParam role string required Role yang tersedia. Contoh: pasien, admin
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [
        'name'      => 'required|string|max:255|regex:/^[a-zA-Z0-9 ]+$/|unique:users,name',
        'email'     => 'required|string|email|max:255|unique:users,email',
        'password'  => 'required|string|min:6',
    ];
}

}
