<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Penting: Import Storage

class Resep extends Model
{
    use HasFactory;

    protected $table = 'resep'; // Sesuaikan jika nama tabel beda

    protected $fillable = [
        'patient_id',
        'pendaftaran_id',
        'diagnosa',
        'poto_obat', // Ini adalah path relatif yang disimpan di DB
        'keterangan_obat',
    ];

    // Hapus 'poto_obat_url' dari $appends.
    // Kita tidak lagi ingin properti terpisah 'poto_obat_url' di JSON.
    protected $appends = [];

    // Accessor untuk atribut 'poto_obat'.
    // Ini akan mengubah nilai 'poto_obat' dari path relatif menjadi URL lengkap
    // setiap kali model diakses sebagai array atau JSON.
    public function getPotoObatAttribute($value) // $value adalah path relatif dari database
    {
        // Pastikan 'poto_obat' memiliki nilai dan file tersebut ada di storage
        if ($value && Storage::disk('public')->exists($value)) {
            // Mengembalikan URL lengkap menggunakan Storage::url()
            // Pastikan Anda sudah menjalankan `php artisan storage:link`
            return url(Storage::url($value));
        }
        // Mengembalikan null jika tidak ada foto atau file tidak ditemukan
        return null;
    }

    // Mutator untuk atribut 'poto_obat'.
    // Ini memastikan bahwa saat Anda menyimpan atau memperbarui 'poto_obat',
    // yang tersimpan di database tetaplah path relatif, bukan URL lengkap.
    public function setPotoObatAttribute($value)
    {
        // Jika nilai yang masuk adalah URL lengkap (misalnya dari input form),
        // Anda mungkin perlu logika untuk mengekstrak path relatifnya.
        // Namun, jika Anda selalu menyimpan path relatif dari `store()`,
        // maka cukup set atribut langsung.
        $this->attributes['poto_obat'] = $value;
    }

    // Relasi ke Pendaftaran
    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    // Relasi ke Pasien (opsional, tergantung kebutuhan Anda)
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }
}
