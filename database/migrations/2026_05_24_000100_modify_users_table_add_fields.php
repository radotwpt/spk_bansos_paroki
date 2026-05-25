<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTableAddFields extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['super_admin', 'paroki', 'ketua_lingkungan_paroki', 'stasi', 'ketua_lingkungan_stasi'])->default('ketua_lingkungan_stasi')->after('password');
            }

            if (! Schema::hasColumn('users', 'stasi_id')) {
                $table->foreignId('stasi_id')->nullable()->constrained('stasis')->onDelete('set null')->after('role');
            }

            if (! Schema::hasColumn('users', 'lingkungan_paroki_id')) {
                $table->foreignId('lingkungan_paroki_id')->nullable()->constrained('lingkungan_parokis')->onDelete('set null')->after('stasi_id');
            }

            if (! Schema::hasColumn('users', 'lingkungan_stasi_id')) {
                $table->foreignId('lingkungan_stasi_id')->nullable()->constrained('lingkungan_stasis')->onDelete('set null')->after('lingkungan_paroki_id');
            }

            // Add index for hierarchy
            $table->index(['role', 'stasi_id', 'lingkungan_paroki_id', 'lingkungan_stasi_id'], 'idx_user_hierarchy');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lingkungan_stasi_id')) {
                $table->dropForeign(['lingkungan_stasi_id']);
                $table->dropColumn('lingkungan_stasi_id');
            }
            if (Schema::hasColumn('users', 'lingkungan_paroki_id')) {
                $table->dropForeign(['lingkungan_paroki_id']);
                $table->dropColumn('lingkungan_paroki_id');
            }
            if (Schema::hasColumn('users', 'stasi_id')) {
                $table->dropForeign(['stasi_id']);
                $table->dropColumn('stasi_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasTable('users')) {
                if (Schema::hasColumn('users', 'role')) {
                    // nothing
                }
                // drop index
                $table->dropIndex('idx_user_hierarchy');
            }
        });
    }
}
