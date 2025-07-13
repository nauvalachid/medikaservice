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
        Schema::table('pasien', function (Blueprint $table) {
            // Menambahkan kolom user_id
            // Memastikan tipe data cocok dengan 'id' di tabel 'users' (biasanya BIGINT UNSIGNED)
            // 'nullable()' berarti kolom ini bisa kosong. Jika tidak ingin kosong, hapus 'nullable()'
            // dan pastikan semua data yang sudah ada memiliki user_id yang valid.
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            // Menghapus foreign key constraint terlebih dahulu
            $table->dropForeign(['user_id']); // Atau 'pasien_user_id_foreign' jika Laravel 8+
            // Menghapus kolom user_id
            $table->dropColumn('user_id');
        });
    }
};