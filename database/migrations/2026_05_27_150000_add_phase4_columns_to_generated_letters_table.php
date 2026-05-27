<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            if (! Schema::hasColumn('generated_letters', 'jenis_surat')) {
                $table->enum('jenis_surat', ['permohonan_stasi', 'edaran_paroki'])
                    ->nullable()
                    ->after('title');
            }

            if (! Schema::hasColumn('generated_letters', 'nomor_surat')) {
                $table->string('nomor_surat')->nullable()->after('jenis_surat');
                $table->unique('nomor_surat', 'generated_letters_nomor_surat_unique');
            }

            if (! Schema::hasColumn('generated_letters', 'final_html_content')) {
                $table->longText('final_html_content')->nullable()->after('content');
            }

            if (! Schema::hasColumn('generated_letters', 'metadata_json')) {
                $table->json('metadata_json')->nullable()->after('final_html_content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            if (Schema::hasColumn('generated_letters', 'metadata_json')) {
                $table->dropColumn('metadata_json');
            }

            if (Schema::hasColumn('generated_letters', 'final_html_content')) {
                $table->dropColumn('final_html_content');
            }

            if (Schema::hasColumn('generated_letters', 'nomor_surat')) {
                $table->dropUnique('generated_letters_nomor_surat_unique');
                $table->dropColumn('nomor_surat');
            }

            if (Schema::hasColumn('generated_letters', 'jenis_surat')) {
                $table->dropColumn('jenis_surat');
            }
        });
    }
};

