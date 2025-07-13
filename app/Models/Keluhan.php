<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluhan extends Model
{
    use HasFactory;

    protected $table = 'keluhan'; // Sesuai dengan konfirmasi Anda

    // Tentukan kolom yang boleh diisi massal
    protected $fillable = [
        'patient_id', // <<< INI YANG BENAR, Gantikan 'pasien_id'
        'diagnosis',
        'keluhan',
        // 'pendaftaran_id', // Tambahkan jika Anda menambahkan kolom ini di DB untuk melacak asal pendaftaran
    ];

    // Relasi: Setiap keluhan memiliki satu pasien
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'patient_id'); // <<< INI JUGA HARUS 'patient_id'
    }

    // Relasi ke tabel pendaftaran (Sangat disarankan untuk dihapus)
    // Kecuali Anda menambahkan kolom 'pendaftaran_id' ke tabel 'keluhan' di database.
    // Jika Anda TIDAK menambahkan kolom pendaftaran_id, relasi ini salah karena patient_id merujuk ke Pasien, bukan Pendaftaran.
    /*
    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'patient_id'); // Kesalahan logis jika patient_id merujuk ke pasien
    }
    */
}