<?php

namespace Database\Seeders;

use App\Models\BansosPeriod;
use App\Models\LingkunganParoki;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $stasi = Stasi::firstOrCreate([
            'kode_stasi' => 'ST-001',
        ], [
            'nama_stasi' => 'Stasi Santo Paulus',
            'alamat' => 'Alamat stasi contoh',
        ]);

        $lingkunganStasi = LingkunganStasi::firstOrCreate([
            'kode_lingkungan' => 'LS-001',
        ], [
            'stasi_id' => $stasi->id,
            'nama_lingkungan_stasi' => 'Lingkungan Stasi 1',
        ]);

        $lingkunganParoki = LingkunganParoki::firstOrCreate([
            'kode_wilayah' => 'LP-001',
        ], [
            'nama_lingkungan_paroki' => 'Lingkungan Paroki 1',
        ]);

        BansosPeriod::firstOrCreate([
            'nama_periode' => 'Bansos 2026',
            'tahun' => 2026,
        ], [
            'status_periode' => 'aktif',
        ]);

        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Super Admin Demo',
            'password' => 'password',
            'role' => 'super_admin',
            'stasi_id' => null,
            'lingkungan_stasi_id' => null,
            'lingkungan_paroki_id' => null,
        ]);

        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Ketua Lingkungan Stasi Demo',
            'password' => 'password',
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkunganStasi->id,
            'lingkungan_paroki_id' => null,
        ]);

        User::updateOrCreate([
            'email' => 'stasi@example.com',
        ], [
            'name' => 'Stasi Demo',
            'password' => 'password',
            'role' => 'stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => null,
            'lingkungan_paroki_id' => null,
        ]);

        User::updateOrCreate([
            'email' => 'ketua.paroki@example.com',
        ], [
            'name' => 'Ketua Lingkungan Paroki Demo',
            'password' => 'password',
            'role' => 'ketua_lingkungan_paroki',
            'stasi_id' => null,
            'lingkungan_stasi_id' => null,
            'lingkungan_paroki_id' => $lingkunganParoki->id,
        ]);

        User::updateOrCreate([
            'email' => 'paroki@example.com',
        ], [
            'name' => 'Paroki Demo',
            'password' => 'password',
            'role' => 'paroki',
            'stasi_id' => null,
            'lingkungan_stasi_id' => null,
            'lingkungan_paroki_id' => $lingkunganParoki->id,
        ]);
    }
}
