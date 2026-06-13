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
        // Index untuk calon_penerimas filtering & searching
        Schema::table('calon_penerimas', function (Blueprint $table) {
            // Status filtering
            $table->index(['status', 'periode_bantuan_id'], 'idx_calon_status_periode');

            // Organizational hierarchy filtering
            $table->index(['paroki_id', 'stasi_id', 'lingkungan_id'], 'idx_calon_org_hierarchy');

            // Full-text indexes are only supported by selected database drivers.
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'nik', 'nomor_kk', 'address'], 'idx_calon_fulltext');
            }

            // Numeric filtering for SAW calculation
            $table->index(['monthly_income', 'dependents_count', 'housing_status'], 'idx_calon_saw_criteria');

            // Sorting by creation & update
            $table->index(['periode_bantuan_id', 'created_at'], 'idx_calon_period_created');
            $table->index(['periode_bantuan_id', 'updated_at'], 'idx_calon_period_updated');
        });

        // Index untuk periode_bantuans filtering
        Schema::table('periode_bantuans', function (Blueprint $table) {
            $table->index(['paroki_id', 'status'], 'idx_periode_paroki_status');
            $table->index(['status', 'created_at'], 'idx_periode_status_created');
        });

        // Index untuk saw_results for ranking queries
        Schema::table('saw_results', function (Blueprint $table) {
            $table->index(['periode_bantuan_id', 'rank'], 'idx_saw_periode_rank');
            $table->index(['periode_bantuan_id', 'final_score'], 'idx_saw_periode_score');
            $table->index(['calon_penerima_id'], 'idx_saw_candidate');
        });

        // Index untuk penerima_bantuans
        Schema::table('penerima_bantuans', function (Blueprint $table) {
            $table->index(['periode_bantuan_id', 'final_status'], 'idx_penerima_period_final_status');
            $table->index(['disbursement_status', 'scheduled_disbursement_at'], 'idx_penerima_disbursement');
        });

        // Index untuk users
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role_id', 'is_active'], 'idx_users_role_active');
            $table->index(['paroki_id', 'stasi_id', 'lingkungan_id'], 'idx_users_org');
        });

        // Index untuk audit_logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['event', 'created_at'], 'idx_audit_event_date');
            $table->index(['actor_id', 'created_at'], 'idx_audit_actor_date');
            $table->index(['auditable_type', 'auditable_id'], 'idx_audit_morphable');
        });

        // Index untuk validasi_logs
        Schema::table('validasi_logs', function (Blueprint $table) {
            $table->index(['calon_penerima_id', 'created_at'], 'idx_validasi_candidate_date');
            $table->index(['action', 'from_status', 'to_status'], 'idx_validasi_workflow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_penerimas', function (Blueprint $table) {
            $table->dropIndex('idx_calon_status_periode');
            $table->dropIndex('idx_calon_org_hierarchy');
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropFullText('idx_calon_fulltext');
            }
            $table->dropIndex('idx_calon_saw_criteria');
            $table->dropIndex('idx_calon_period_created');
            $table->dropIndex('idx_calon_period_updated');
        });

        Schema::table('periode_bantuans', function (Blueprint $table) {
            $table->dropIndex('idx_periode_paroki_status');
            $table->dropIndex('idx_periode_status_created');
        });

        Schema::table('saw_results', function (Blueprint $table) {
            $table->dropIndex('idx_saw_periode_rank');
            $table->dropIndex('idx_saw_periode_score');
            $table->dropIndex('idx_saw_candidate');
        });

        Schema::table('penerima_bantuans', function (Blueprint $table) {
            $table->dropIndex('idx_penerima_period_final_status');
            $table->dropIndex('idx_penerima_disbursement');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_org');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_event_date');
            $table->dropIndex('idx_audit_actor_date');
            $table->dropIndex('idx_audit_morphable');
        });

        Schema::table('validasi_logs', function (Blueprint $table) {
            $table->dropIndex('idx_validasi_candidate_date');
            $table->dropIndex('idx_validasi_workflow');
        });
    }
};
