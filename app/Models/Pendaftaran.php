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

    // Tambahkan accessor 'has_resep' agar selalu disertakan dalam output JSON
    protected $appends = ['has_resep']; // <--- TAMBAHKAN BARIS INI

    // Relasi dengan model Pasien
    public function patient()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    // Relasi dengan model Jadwal
    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    /**
     * Get the resep associated with the Pendaftaran.
     * Asumsi: tabel 'reseps' memiliki kolom 'pendaftaran_id'
     * yang merujuk ke 'id' dari tabel 'pendaftaran'.
     */
    public function resep()
    {
        return $this->hasOne(Resep::class, 'pendaftaran_id', 'id'); // <--- TAMBAHKAN RELASI INI
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

    /**
     * Accessor untuk memeriksa apakah pendaftaran ini memiliki resep.
     * Ini akan membuat atribut 'has_resep' di output JSON.
     */
    public function getHasResepAttribute() // <--- TAMBAHKAN ACCESSOR INI
    {
        // Menggunakan relasi 'resep' yang baru saja kita definisikan
        // exists() akan mengembalikan true jika ada setidaknya satu resep terkait
        return $this->resep()->exists();
    }

    public function keluhan()
    {
        return $this->hasMany(Keluhan::class, 'patient_id');
    }
}