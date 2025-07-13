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
    Schema::table('keluhan', function (Blueprint $table) {
        $table->string('keluhan')->after('patient_id');
    });
}

public function down()
{
    Schema::table('keluhan', function (Blueprint $table) {
        $table->dropColumn('keluhan');
    });
}

};
