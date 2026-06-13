<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Get role IDs
        $roles = DB::table('roles')->pluck('id', 'name');
        $parokis = DB::table('parokis')->pluck('id', 'code');
        $stasis = DB::table('stasis')->pluck('id', 'code');
        $lingkungans = DB::table('lingkungans')->pluck('id', 'code');

        DB::table('users')->upsert([
            // Super Admin
            [
                'role_id' => $roles['super_admin'],
                'paroki_id' => null,
                'stasi_id' => null,
                'lingkungan_id' => null,
                'name' => 'Administrator',
                'email' => 'admin@spk-bansos.local',
                'password' => Hash::make('admin12345'),
                'phone' => '081234567890',
                'position_title' => 'System Administrator',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Paroki Leaders
            [
                'role_id' => $roles['paroki'],
                'paroki_id' => $parokis['PAROKI001'],
                'stasi_id' => null,
                'lingkungan_id' => null,
                'name' => 'Romo Alexander (Paroki Santo Petrus)',
                'email' => 'paroki1@spk-bansos.local',
                'password' => Hash::make('paroki12345'),
                'phone' => '081234567890',
                'position_title' => 'Ketua Paroki',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id' => $roles['paroki'],
                'paroki_id' => $parokis['PAROKI002'],
                'stasi_id' => null,
                'lingkungan_id' => null,
                'name' => 'Romo Benediktus (Paroki Santa Maria)',
                'email' => 'paroki2@spk-bansos.local',
                'password' => Hash::make('paroki12345'),
                'phone' => '081234567891',
                'position_title' => 'Ketua Paroki',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Stasi Coordinators
            [
                'role_id' => $roles['stasi'],
                'paroki_id' => $parokis['PAROKI001'],
                'stasi_id' => $stasis['STASI001'],
                'lingkungan_id' => null,
                'name' => 'Ibu Siti Nurhaliza (Stasi Pusat)',
                'email' => 'stasi1@spk-bansos.local',
                'password' => Hash::make('stasi12345'),
                'phone' => '089876543210',
                'position_title' => 'Koordinator Stasi',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id' => $roles['stasi'],
                'paroki_id' => $parokis['PAROKI001'],
                'stasi_id' => $stasis['STASI002'],
                'lingkungan_id' => null,
                'name' => 'Pak Budi Santoso (Stasi Timur)',
                'email' => 'stasi2@spk-bansos.local',
                'password' => Hash::make('stasi12345'),
                'phone' => '089876543211',
                'position_title' => 'Koordinator Stasi',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id' => $roles['stasi'],
                'paroki_id' => $parokis['PAROKI002'],
                'stasi_id' => $stasis['STASI003'],
                'lingkungan_id' => null,
                'name' => 'Ibu Dewi Lestari (Stasi Utama)',
                'email' => 'stasi3@spk-bansos.local',
                'password' => Hash::make('stasi12345'),
                'phone' => '089876543212',
                'position_title' => 'Koordinator Stasi',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Lingkungan Leaders
            [
                'role_id' => $roles['ketua_lingkungan_stasi'],
                'paroki_id' => $parokis['PAROKI001'],
                'stasi_id' => $stasis['STASI001'],
                'lingkungan_id' => $lingkungans['LING001'],
                'name' => 'Pak Hendra (Lingkungan Kebayoran)',
                'email' => 'ling1@spk-bansos.local',
                'password' => Hash::make('lingkungan12345'),
                'phone' => '087654321098',
                'position_title' => 'Ketua Lingkungan',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id' => $roles['ketua_lingkungan_stasi'],
                'paroki_id' => $parokis['PAROKI001'],
                'stasi_id' => $stasis['STASI001'],
                'lingkungan_id' => $lingkungans['LING002'],
                'name' => 'Bu Ratna (Lingkungan Pondok Indah)',
                'email' => 'ling2@spk-bansos.local',
                'password' => Hash::make('lingkungan12345'),
                'phone' => '087654321099',
                'position_title' => 'Ketua Lingkungan',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id' => $roles['ketua_lingkungan_stasi'],
                'paroki_id' => $parokis['PAROKI001'],
                'stasi_id' => $stasis['STASI002'],
                'lingkungan_id' => $lingkungans['LING003'],
                'name' => 'Pak Dimas (Lingkungan Mampang)',
                'email' => 'ling3@spk-bansos.local',
                'password' => Hash::make('lingkungan12345'),
                'phone' => '087654321100',
                'position_title' => 'Ketua Lingkungan',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id' => $roles['ketua_lingkungan_stasi'],
                'paroki_id' => $parokis['PAROKI002'],
                'stasi_id' => $stasis['STASI003'],
                'lingkungan_id' => $lingkungans['LING004'],
                'name' => 'Bu Sinta (Lingkungan Darmo)',
                'email' => 'ling4@spk-bansos.local',
                'password' => Hash::make('lingkungan12345'),
                'phone' => '087654321101',
                'position_title' => 'Ketua Lingkungan',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['email']);

        $this->command->info('Users seeded successfully!');
    }
}
