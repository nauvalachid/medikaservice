<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Jadwal extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak menggunakan nama default (jadwals)
    protected $table = 'jadwal';

    // Tentukan kolom yang boleh diisi massal (Mass Assignment)
    protected $fillable = [
        'nama_bidan',
        'tanggal',
        'start_time',
        'end_time',
    ];

    // Tentukan kolom yang tidak bisa diisi massal
    protected $guarded = [];

    // Jika Anda ingin menangani format tanggal dan waktu khusus, Anda bisa menggunakan cast
    protected $casts = [
        'tanggal' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Accessor untuk format tanggal
    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d'); // Format sesuai dengan yang diinginkan
    }

    // Accessor untuk format start_time
    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    // Accessor untuk format end_time
    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }
}
