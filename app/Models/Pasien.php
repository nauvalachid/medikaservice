<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';

    protected $fillable = [
        'nama',
        'user_id', // Foreign key ke tabel users
        'tanggal_lahir',
        'kelamin',
        'alamat',
        'nomor_telepon',
        'latitude',
        'longitude',
    ];

    /**
     * Mendefinisikan relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
