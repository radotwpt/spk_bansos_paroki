<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStasisTable extends Migration
{
    public function up()
    {
        Schema::create('stasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_stasi', 100)->unique();
            $table->string('kode_stasi', 20)->unique();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stasis');
    }
}
