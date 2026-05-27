<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saw_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saw_criterion_id')->constrained('saw_criteria')->cascadeOnDelete();
            $table->foreignId('bansos_period_id')->nullable()->constrained('bansos_periods')->nullOnDelete();
            $table->decimal('weight', 8, 4);
            $table->unique(['saw_criterion_id', 'bansos_period_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saw_weights');
    }
};
