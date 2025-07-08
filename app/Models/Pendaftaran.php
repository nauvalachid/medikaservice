<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak menggunakan nama default (pendaftarans)
    protected $table = 'pendaftaran';

    // Tentukan kolom yang boleh diisi massal (Mass Assignment)
    protected $fillable = [
        'patient_id',
        'jadwal_id',
        'keluhan',
        'durasi',
        'tanggal_pendaftaran',
        'status',
    ];

    // Tentukan kolom yang tidak bisa diisi massal
    protected $guarded = [];

    // Relasi dengan model Patient
    public function patient()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    // Relasi dengan model Jadwal
    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    // Mengatur format tanggal pendaftaran
    protected $casts = [
        'tanggal_pendaftaran' => 'datetime:Y-m-d', // Mengubah format tanggal
    ];

    // Accessor untuk durasi jika perlu format khusus
    public function getDurasiAttribute($value)
    {
        return (int) $value; // Mengubah durasi ke format integer
    }

    public function keluhan()
{
    return $this->hasMany(Keluhan::class, 'patient_id'); // Periksa nama kolom 'patient_id'
}
}
