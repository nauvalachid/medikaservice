<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table) {
            // Menambahkan kolom 'jadwal_id' ke tabel 'pendaftaran'
            $table->unsignedBigInteger('jadwal_id')->after('patient_id');
            
            // Menambahkan foreign key constraint untuk relasi dengan tabel jadwal
            $table->foreign('jadwal_id')->references('id')->on('jadwal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table) {
            // Menghapus foreign key constraint dan kolom 'jadwal_id' jika migrasi dibatalkan
            $table->dropForeign(['jadwal_id']);
            $table->dropColumn('jadwal_id');
        });
    }
};
