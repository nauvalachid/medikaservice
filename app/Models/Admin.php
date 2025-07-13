<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin'; // Nama tabel 'admin'
    protected $fillable = [
        'user_id', // Ini penting
        'nama',
        'tanggal_lahir',
        'kelamin',
        'alamat',
        'nomor_telepon',
        'foto_profil',
    ];
    // protected $guarded = []; // Jika Anda menggunakan $fillable, $guarded tidak diperlukan atau bisa menjadi []

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}