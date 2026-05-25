<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLingkunganStasisTable extends Migration
{
    public function up()
    {
        Schema::create('lingkungan_stasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stasi_id')->constrained('stasis')->onDelete('cascade');
            $table->string('nama_lingkungan_stasi', 100);
            $table->string('kode_lingkungan', 20)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lingkungan_stasis');
    }
}
