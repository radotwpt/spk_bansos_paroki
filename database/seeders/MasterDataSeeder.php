<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // ===== PAROKIS (Parishes) =====
        DB::table('parokis')->upsert([
            [
                'code' => 'PAROKI001',
                'name' => 'Paroki Santo Petrus',
                'leader_name' => 'Romo Alexander',
                'phone' => '081234567890',
                'address' => 'Jl. Gereja No. 1, Jakarta',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PAROKI002',
                'name' => 'Paroki Santa Maria',
                'leader_name' => 'Romo Benediktus',
                'phone' => '081234567891',
                'address' => 'Jl. Katedral No. 2, Surabaya',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PAROKI003',
                'name' => 'Paroki Santo Yusuf',
                'leader_name' => 'Romo Kristoforus',
                'phone' => '081234567892',
                'address' => 'Jl. Kapela No. 3, Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code']);

        $parokis = DB::table('parokis')->pluck('id', 'code');

        // ===== DEFAULT AID PERIODS =====
        DB::table('periode_bantuans')->upsert([
            [
                'paroki_id' => $parokis['PAROKI001'],
                'code' => 'BANSOS-2026-PAROKI001',
                'name' => 'Bantuan Sosial 2026 - Paroki Santo Petrus',
                'description' => 'Periode default agar modul Ketua Lingkungan dapat langsung digunakan untuk input calon penerima.',
                'aid_type' => 'tunai',
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-12-31',
                'quota' => 100,
                'ranking_scope_size' => 100,
                'default_aid_amount' => 250000,
                'total_budget' => 25000000,
                'planned_disbursement_date' => '2026-12-20',
                'status' => 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'paroki_id' => $parokis['PAROKI002'],
                'code' => 'BANSOS-2026-PAROKI002',
                'name' => 'Bantuan Sosial 2026 - Paroki Santa Maria',
                'description' => 'Periode default agar modul Ketua Lingkungan dapat langsung digunakan untuk input calon penerima.',
                'aid_type' => 'tunai',
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-12-31',
                'quota' => 100,
                'ranking_scope_size' => 100,
                'default_aid_amount' => 250000,
                'total_budget' => 25000000,
                'planned_disbursement_date' => '2026-12-20',
                'status' => 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['paroki_id', 'code'], [
            'name',
            'description',
            'aid_type',
            'starts_at',
            'ends_at',
            'quota',
            'ranking_scope_size',
            'default_aid_amount',
            'total_budget',
            'planned_disbursement_date',
            'status',
            'updated_at',
        ]);

        // ===== STASIS (Sub-parish divisions) =====

        DB::table('stasis')->upsert([
            [
                'paroki_id' => $parokis['PAROKI001'],
                'code' => 'STASI001',
                'name' => 'Stasi Pusat',
                'leader_name' => 'Ibu Siti Nurhaliza',
                'phone' => '089876543210',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'paroki_id' => $parokis['PAROKI001'],
                'code' => 'STASI002',
                'name' => 'Stasi Timur',
                'leader_name' => 'Pak Budi Santoso',
                'phone' => '089876543211',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'paroki_id' => $parokis['PAROKI002'],
                'code' => 'STASI003',
                'name' => 'Stasi Utama',
                'leader_name' => 'Ibu Dewi Lestari',
                'phone' => '089876543212',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['paroki_id', 'code']);

        // ===== LINGKUNGANS (Community groups) =====
        $stasis = DB::table('stasis')->pluck('id', 'code');

        DB::table('lingkungans')->upsert([
            [
                'stasi_id' => $stasis['STASI001'],
                'code' => 'LING001',
                'name' => 'Lingkungan Kebayoran',
                'chairperson_name' => 'Pak Hendra',
                'phone' => '087654321098',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'stasi_id' => $stasis['STASI001'],
                'code' => 'LING002',
                'name' => 'Lingkungan Pondok Indah',
                'chairperson_name' => 'Bu Ratna',
                'phone' => '087654321099',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'stasi_id' => $stasis['STASI002'],
                'code' => 'LING003',
                'name' => 'Lingkungan Mampang',
                'chairperson_name' => 'Pak Dimas',
                'phone' => '087654321100',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'stasi_id' => $stasis['STASI003'],
                'code' => 'LING004',
                'name' => 'Lingkungan Darmo',
                'chairperson_name' => 'Bu Sinta',
                'phone' => '087654321101',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['stasi_id', 'code']);

        // ===== SAW CRITERIA =====
        DB::table('saw_criteria')->upsert([
            [
                'code' => 'monthly_income',
                'name' => 'Penghasilan Bulanan',
                'description' => 'Jumlah penghasilan bulanan keluarga (semakin rendah semakin prioritas)',
                'type' => 'cost',
                'attribute_key' => 'monthly_income',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'dependents_count',
                'name' => 'Jumlah Tanggungan',
                'description' => 'Jumlah anggota keluarga yang ditanggung (semakin banyak semakin prioritas)',
                'type' => 'benefit',
                'attribute_key' => 'dependents_count',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'housing_status',
                'name' => 'Status Tempat Tinggal',
                'description' => 'Kepemilikan dan kondisi tempat tinggal',
                'type' => 'benefit',
                'attribute_key' => 'housing_status_score',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'disability',
                'name' => 'Disabilitas',
                'description' => 'Ada tidaknya anggota keluarga yang menyandang disabilitas',
                'type' => 'benefit',
                'attribute_key' => 'disability_score',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code']);

        // ===== SAW WEIGHT VERSION (Default weights) =====
        $criteria = DB::table('saw_criteria')->pluck('id', 'code');

        DB::table('saw_weight_versions')->updateOrInsert(
            ['code' => 'V1_2026'],
            [
                'name' => 'Versi Default 2026',
                'description' => 'Bobot default untuk periode 2026',
                'total_weight' => 100,
                'is_active' => true,
                'locked_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $weightVersionId = DB::table('saw_weight_versions')->where('code', 'V1_2026')->value('id');

        DB::table('saw_weight_items')->upsert([
            [
                'saw_weight_version_id' => $weightVersionId,
                'saw_criterion_id' => $criteria['monthly_income'],
                'weight' => 35,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_weight_version_id' => $weightVersionId,
                'saw_criterion_id' => $criteria['dependents_count'],
                'weight' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_weight_version_id' => $weightVersionId,
                'saw_criterion_id' => $criteria['housing_status'],
                'weight' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_weight_version_id' => $weightVersionId,
                'saw_criterion_id' => $criteria['disability'],
                'weight' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['saw_weight_version_id', 'saw_criterion_id'], ['weight', 'updated_at']);

        // ===== SAW CRITERION OPTIONS (Scoring for categorical criteria) =====
        DB::table('saw_criterion_options')->upsert([
            // Housing status options
            [
                'saw_criterion_id' => $criteria['housing_status'],
                'value' => 'milik_sendiri',
                'label' => 'Milik Sendiri',
                'score' => 1,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_criterion_id' => $criteria['housing_status'],
                'value' => 'kontrak',
                'label' => 'Kontrak',
                'score' => 2,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_criterion_id' => $criteria['housing_status'],
                'value' => 'menumpang',
                'label' => 'Menumpang',
                'score' => 3,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_criterion_id' => $criteria['housing_status'],
                'value' => 'tidak_tetap',
                'label' => 'Tidak Tetap',
                'score' => 4,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Disability options
            [
                'saw_criterion_id' => $criteria['disability'],
                'value' => 'tidak',
                'label' => 'Tidak Ada',
                'score' => 1,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'saw_criterion_id' => $criteria['disability'],
                'value' => 'ya',
                'label' => 'Ada',
                'score' => 2,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['saw_criterion_id', 'value'], ['label', 'score', 'sort_order', 'updated_at']);

        // ===== DOCUMENT TEMPLATES =====
        DB::table('document_templates')->upsert([
            [
                'code' => 'surat_permohonan_stasi',
                'name' => 'Surat Permohonan dari Stasi',
                'type' => 'surat_permohonan_stasi',
                'body' => <<<'BODY'
Yth. Bapak Kepala Paroki,

Berikut ini kami sampaikan daftar calon penerima bantuan sosial untuk periode {periode}.

[DATA CALON PENERIMA]

Demikian surat permohonan ini kami sampaikan untuk menjadi bahan pertimbangan.

Hormat kami,
Koordinator Stasi
BODY,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'laporan_penerima',
                'name' => 'Laporan Penerima Bantuan',
                'type' => 'laporan_penerima',
                'body' => <<<'BODY'
LAPORAN PENERIMA BANTUAN SOSIAL
Periode: {periode}
Paroki: {paroki}

Total Penerima: {total_penerima}
Total Jumlah Bantuan: Rp. {total_bantuan}

[DATA PENERIMA]

Dibuat oleh: {pembuat_laporan}
Tanggal: {tanggal}
BODY,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code']);

        $this->command->info('Master data seeded successfully!');
    }
}
