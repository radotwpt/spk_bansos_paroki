<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bansos_periods', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode', 100);
            $table->unsignedSmallInteger('tahun');
            $table->enum('status_periode', ['aktif', 'proses_perankingan', 'selesai', 'arsip'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bansos_periods');
    }
};
