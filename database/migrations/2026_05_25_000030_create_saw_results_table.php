<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saw_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bansos_period_id')->constrained('bansos_periods')->cascadeOnDelete();
            $table->foreignId('calon_penerima_id')->constrained('calon_penerimas')->cascadeOnDelete();
            $table->json('raw_values')->nullable();
            $table->json('normalized_values')->nullable();
            $table->json('weights_used')->nullable();
            $table->decimal('score', 8, 4)->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['bansos_period_id', 'calon_penerima_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saw_results');
    }
};
