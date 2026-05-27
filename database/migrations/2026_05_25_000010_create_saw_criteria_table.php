<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saw_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->enum('type', ['benefit', 'cost'])->default('benefit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saw_criteria');
    }
};
