<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluhan extends Model
{
    use HasFactory;

    protected $table = 'keluhan';

    // Tentukan kolom yang boleh diisi massal
    protected $fillable = [
        'pasien_id', // Relasi dengan pasien
        'keluhan',
        'durasi',
        'patient_id', // Relasi dengan pendaftaran
    ];

    // Relasi: Setiap keluhan memiliki satu pasien
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');  // Relasi dengan pasien
    }

    // Relasi ke tabel pendaftaran
    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'patient_id');  // Relasi dengan pendaftaran
    }
}
