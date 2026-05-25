<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_penerimas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bansos_period_id')->constrained('bansos_periods')->cascadeOnDelete();
            $table->foreignId('lingkungan_stasi_id')->constrained('lingkungan_stasis')->cascadeOnDelete();
            $table->foreignId('stasi_id')->constrained('stasis')->cascadeOnDelete();
            $table->string('nik', 16);
            $table->string('nama_lengkap', 150);
            $table->text('alamat_kristen')->nullable();
            $table->decimal('pendapatan_keluarga', 12, 2);
            $table->unsignedInteger('jumlah_tanggungan');
            $table->enum('status_tempat_tinggal', ['milik_sendiri', 'sewa', 'numpang']);
            $table->enum('status_hubungan', ['lajang', 'menikah', 'cerai']);
            $table->text('urgensi_tambahan_tekstual')->nullable();
            $table->decimal('saw_score', 5, 4)->default(0);
            $table->unsignedInteger('rank_global')->nullable();
            $table->unsignedInteger('rank_internal_stasi')->nullable();
            $table->enum('status_alur', [
                'draft',
                'diajukan_ke_stasi',
                'disetujui_stasi',
                'diranking_lingkungan_paroki',
                'disetujui_paroki',
                'ditolak',
            ])->default('draft');
            $table->boolean('is_penerima_sah')->default(false);
            $table->decimal('nominal_bansos_disetujui', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['bansos_period_id', 'status_alur'], 'idx_calon_bansos_period_status');
            $table->index('nik', 'idx_calon_nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_penerimas');
    }
};
