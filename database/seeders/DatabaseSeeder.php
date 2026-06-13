<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        // ===== SEED ROLES FIRST =====
        DB::table('roles')->upsert([
            [
                'name' => 'ketua_lingkungan_stasi',
                'label' => 'Ketua Lingkungan Stasi',
                'description' => 'Menginput dan mengajukan data calon penerima bantuan dari lingkungan stasi.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'stasi',
                'label' => 'Stasi',
                'description' => 'Memvalidasi calon penerima, membuat surat permohonan, dan mengirim pengajuan ke paroki.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'paroki',
                'label' => 'Paroki',
                'description' => 'Menjalankan ranking SAW, meninjau urgensi, dan menetapkan penerima bantuan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'super_admin',
                'label' => 'Super Admin',
                'description' => 'Akses penuh untuk konfigurasi, data master, dan seluruh proses bantuan sosial.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['label', 'description', 'updated_at']);

        // ===== CALL OTHER SEEDERS =====
        $this->call([
            MasterDataSeeder::class,
            UserSeeder::class,
        ]);
    }
}
