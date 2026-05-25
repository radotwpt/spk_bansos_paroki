<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('generated_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained('document_templates')->cascadeOnDelete();
            // keep IDs without FK constraints to avoid migration order issues
            $table->unsignedBigInteger('calon_penerima_id')->nullable();
            $table->unsignedBigInteger('bansos_period_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('generated_letters');
    }
};
