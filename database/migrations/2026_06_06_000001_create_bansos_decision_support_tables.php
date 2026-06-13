<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periode_bantuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroki_id')->constrained('parokis')->restrictOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('aid_type', ['tunai'])->default('tunai');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->unsignedInteger('ranking_scope_size')->nullable();
            $table->decimal('default_aid_amount', 14, 2)->nullable();
            $table->decimal('total_budget', 14, 2)->nullable();
            $table->date('planned_disbursement_date')->nullable();
            $table->enum('status', ['draft', 'open', 'closed', 'ranking', 'finalized', 'archived'])->default('draft');
            $table->timestamp('ranking_locked_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['paroki_id', 'code']);
            $table->index(['paroki_id', 'status']);
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['surat_permohonan_stasi', 'laporan_penerima', 'lainnya']);
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('saw_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['benefit', 'cost']);
            $table->string('attribute_key');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('saw_weight_versions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('total_weight', 5, 2)->default(100);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('saw_weight_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saw_weight_version_id')->constrained('saw_weight_versions')->cascadeOnDelete();
            $table->foreignId('saw_criterion_id')->constrained('saw_criteria')->cascadeOnDelete();
            $table->decimal('weight', 5, 2);
            $table->timestamps();

            $table->unique(['saw_weight_version_id', 'saw_criterion_id'], 'saw_weight_item_unique');
        });

        Schema::create('saw_criterion_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saw_criterion_id')->constrained('saw_criteria')->cascadeOnDelete();
            $table->string('value');
            $table->string('label');
            $table->decimal('score', 10, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['saw_criterion_id', 'value']);
        });

        Schema::create('calon_penerimas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_bantuan_id')->constrained('periode_bantuans')->restrictOnDelete();
            $table->foreignId('paroki_id')->constrained('parokis')->restrictOnDelete();
            $table->foreignId('stasi_id')->constrained('stasis')->restrictOnDelete();
            $table->foreignId('lingkungan_id')->constrained('lingkungans')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registration_number')->nullable();
            $table->string('name');
            $table->string('nik', 32);
            $table->string('nomor_kk', 32);
            $table->string('family_head_name')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['laki_laki', 'perempuan'])->nullable();
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->decimal('monthly_income', 14, 2)->default(0);
            $table->unsignedTinyInteger('dependents_count')->default(0);
            $table->enum('housing_status', ['milik_sendiri', 'kontrak', 'menumpang', 'tidak_tetap']);
            $table->unsignedTinyInteger('housing_status_score');
            $table->boolean('has_disability')->default(false);
            $table->unsignedTinyInteger('disability_score')->default(1);
            $table->text('disability_note')->nullable();
            $table->text('urgency_note')->nullable();
            $table->text('economic_condition_note')->nullable();
            $table->enum('status', [
                'draft',
                'submitted_to_stasi',
                'revision_requested',
                'approved_by_stasi',
                'sent_to_paroki',
                'ranked',
                'under_discussion',
                'approved_final',
                'rejected',
            ])->default('draft');
            $table->text('stasi_validation_note')->nullable();
            $table->text('paroki_decision_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('sent_to_paroki_at')->nullable();
            $table->timestamp('ranked_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['periode_bantuan_id', 'nik']);
            $table->unique(['periode_bantuan_id', 'nomor_kk']);
            $table->index(['periode_bantuan_id', 'status']);
            $table->index(['paroki_id', 'stasi_id', 'lingkungan_id']);
        });

        Schema::create('surat_permohonans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_bantuan_id')->constrained('periode_bantuans')->restrictOnDelete();
            $table->foreignId('paroki_id')->constrained('parokis')->restrictOnDelete();
            $table->foreignId('stasi_id')->constrained('stasis')->restrictOnDelete();
            $table->foreignId('document_template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->foreignId('generated_by')->constrained('users')->restrictOnDelete();
            $table->string('letter_number')->unique();
            $table->string('subject');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('total_candidates')->default(0);
            $table->enum('status', ['draft', 'generated', 'sent', 'cancelled'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['periode_bantuan_id', 'stasi_id', 'status']);
            $table->unique(['periode_bantuan_id', 'stasi_id']);
        });

        Schema::create('surat_permohonan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_permohonan_id')->constrained('surat_permohonans')->cascadeOnDelete();
            $table->foreignId('calon_penerima_id')->constrained('calon_penerimas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['surat_permohonan_id', 'calon_penerima_id'], 'surat_item_calon_unique');
        });

        Schema::create('validasi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calon_penerima_id')->constrained('calon_penerimas')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['calon_penerima_id', 'action']);
        });

        Schema::create('saw_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_bantuan_id')->constrained('periode_bantuans')->cascadeOnDelete();
            $table->foreignId('calon_penerima_id')->constrained('calon_penerimas')->cascadeOnDelete();
            $table->foreignId('saw_weight_version_id')->nullable()->constrained('saw_weight_versions')->nullOnDelete();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('monthly_income_value', 14, 2);
            $table->unsignedTinyInteger('dependents_count_value');
            $table->unsignedTinyInteger('housing_status_score_value');
            $table->unsignedTinyInteger('disability_score_value');
            $table->decimal('normalized_income', 10, 6);
            $table->decimal('normalized_dependents', 10, 6);
            $table->decimal('normalized_housing', 10, 6);
            $table->decimal('normalized_disability', 10, 6);
            $table->decimal('final_score', 10, 6);
            $table->unsignedInteger('rank');
            $table->json('calculation_snapshot');
            $table->dateTime('calculated_at');
            $table->timestamps();

            $table->unique(['periode_bantuan_id', 'calon_penerima_id']);
            $table->index(['periode_bantuan_id', 'rank']);
        });

        Schema::create('penerima_bantuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_bantuan_id')->constrained('periode_bantuans')->restrictOnDelete();
            $table->foreignId('calon_penerima_id')->constrained('calon_penerimas')->restrictOnDelete();
            $table->foreignId('decided_by')->constrained('users')->restrictOnDelete();
            $table->enum('final_status', ['selected', 'waiting_list', 'not_selected'])->default('selected');
            $table->decimal('aid_amount', 14, 2)->nullable();
            $table->text('aid_description')->nullable();
            $table->enum('disbursement_status', ['pending', 'scheduled', 'disbursed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'other'])->default('cash');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->timestamp('scheduled_disbursement_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->dateTime('decided_at');
            $table->timestamps();

            $table->unique(['periode_bantuan_id', 'calon_penerima_id']);
            $table->index(['periode_bantuan_id', 'final_status']);
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_bantuan_id')->nullable()->constrained('periode_bantuans')->nullOnDelete();
            $table->foreignId('paroki_id')->nullable()->constrained('parokis')->nullOnDelete();
            $table->foreignId('stasi_id')->nullable()->constrained('stasis')->nullOnDelete();
            $table->foreignId('generated_by')->constrained('users')->restrictOnDelete();
            $table->enum('type', [
                'rekap_calon_per_stasi',
                'rekap_penerima_final',
                'hasil_ranking_saw',
                'surat_permohonan_pdf',
                'berita_acara_paroki',
                'riwayat_penerima_bantuan',
            ]);
            $table->string('title');
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('completed');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['periode_bantuan_id', 'type']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('penerima_bantuans');
        Schema::dropIfExists('saw_results');
        Schema::dropIfExists('validasi_logs');
        Schema::dropIfExists('surat_permohonan_items');
        Schema::dropIfExists('surat_permohonans');
        Schema::dropIfExists('calon_penerimas');
        Schema::dropIfExists('saw_criterion_options');
        Schema::dropIfExists('saw_weight_items');
        Schema::dropIfExists('saw_weight_versions');
        Schema::dropIfExists('saw_criteria');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('periode_bantuans');
    }
};
