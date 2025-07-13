<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('resep', function (Blueprint $table) {
            $table->unsignedBigInteger('pendaftaran_id')->nullable()->after('patient_id');

            // Tambahkan foreign key ke tabel pendaftaran
            $table->foreign('pendaftaran_id')
                  ->references('id')
                  ->on('pendaftaran')
                  ->onDelete('set null'); // jika data pendaftaran dihapus, set null
        });
    }

    public function down()
    {
        Schema::table('resep', function (Blueprint $table) {
            $table->dropForeign(['pendaftaran_id']);
            $table->dropColumn('pendaftaran_id');
        });
    }
};
