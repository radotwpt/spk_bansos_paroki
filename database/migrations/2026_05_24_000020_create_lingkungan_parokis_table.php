<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLingkunganParokisTable extends Migration
{
    public function up()
    {
        Schema::create('lingkungan_parokis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lingkungan_paroki', 100)->unique();
            $table->string('kode_wilayah', 20)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lingkungan_parokis');
    }
}
