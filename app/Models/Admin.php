<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin'; // Nama tabel 'admin'
    protected $fillable = [
        'user_id',
        'nama',
        'tanggal_lahir',
        'kelamin',
        'alamat',
        'nomor_telepon',
        'foto_profil',
    ];
    protected $guarded = []; // Ini berarti tidak ada kolom yang dilindungi dari mass assignment
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}