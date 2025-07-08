<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi
    protected $table = 'resep';

    // Tentukan kolom yang boleh diisi massal (Mass Assignment)
    protected $fillable = [
        'patient_id',  // Relasi dengan tabel pasien
        'poto_obat',
    ];

    // Tentukan kolom yang tidak bisa diisi massal
    protected $guarded = [];

    // Relasi: Setiap resep dimiliki oleh seorang pasien
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    // Relasi ke tabel pendaftaran
    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'jadwal_id');
    }
}
