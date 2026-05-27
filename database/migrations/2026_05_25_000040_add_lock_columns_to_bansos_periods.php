<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bansos_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('bansos_periods', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('status_periode');
            }
            if (!Schema::hasColumn('bansos_periods', 'locked_by')) {
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete()->after('is_locked');
            }
            if (!Schema::hasColumn('bansos_periods', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('locked_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bansos_periods', function (Blueprint $table) {
            if (Schema::hasColumn('bansos_periods', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
            if (Schema::hasColumn('bansos_periods', 'locked_by')) {
                $table->dropConstrainedForeignId('locked_by');
            }
            if (Schema::hasColumn('bansos_periods', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
