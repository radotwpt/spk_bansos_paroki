<?php

namespace Tests\Feature;

use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\LingkunganParoki;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use App\Models\User;
use Database\Seeders\SawCriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SawCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_three_ranking_flow_with_preview_execute_results_and_lock()
    {
        $this->seed(SawCriteriaSeeder::class);

        $period = BansosPeriod::create([
            'nama_periode' => 'Bansos 2026',
            'tahun' => 2026,
            'status_periode' => 'aktif',
        ]);

        $lingkunganParoki = LingkunganParoki::create([
            'nama_lingkungan_paroki' => 'Paroki Test',
            'kode_wilayah' => 'PRK-1',
        ]);

        $stasiA = Stasi::factory()->create(['nama_stasi' => 'Stasi A']);
        $stasiB = Stasi::factory()->create(['nama_stasi' => 'Stasi B']);
        $lingkunganA = LingkunganStasi::factory()->create(['stasi_id' => $stasiA->id]);
        $lingkunganB = LingkunganStasi::factory()->create(['stasi_id' => $stasiB->id]);

        $ketuaParoki = User::factory()->create([
            'role' => 'ketua_lingkungan_paroki',
            'lingkungan_paroki_id' => $lingkunganParoki->id,
            'password' => 'password',
        ]);

        Sanctum::actingAs($ketuaParoki);

        CalonPenerima::create([
            'bansos_period_id' => $period->id,
            'stasi_id' => $stasiA->id,
            'lingkungan_stasi_id' => $lingkunganA->id,
            'nik' => '1111111111111111',
            'nama_lengkap' => 'Calon A1',
            'alamat_kristen' => 'Alamat A1',
            'pendapatan_keluarga' => 100000,
            'jumlah_tanggungan' => 1,
            'status_tempat_tinggal' => 'numpang',
            'status_hubungan' => 'lajang',
            'status_alur' => 'disetujui_stasi',
        ]);

        CalonPenerima::create([
            'bansos_period_id' => $period->id,
            'stasi_id' => $stasiA->id,
            'lingkungan_stasi_id' => $lingkunganA->id,
            'nik' => '1111111111111112',
            'nama_lengkap' => 'Calon A2',
            'alamat_kristen' => 'Alamat A2',
            'pendapatan_keluarga' => 180000,
            'jumlah_tanggungan' => 4,
            'status_tempat_tinggal' => 'sewa',
            'status_hubungan' => 'menikah',
            'status_alur' => 'disetujui_stasi',
        ]);

        CalonPenerima::create([
            'bansos_period_id' => $period->id,
            'stasi_id' => $stasiB->id,
            'lingkungan_stasi_id' => $lingkunganB->id,
            'nik' => '2222222222222222',
            'nama_lengkap' => 'Calon B1',
            'alamat_kristen' => 'Alamat B1',
            'pendapatan_keluarga' => 260000,
            'jumlah_tanggungan' => 3,
            'status_tempat_tinggal' => 'milik_sendiri',
            'status_hubungan' => 'cerai',
            'status_alur' => 'disetujui_stasi',
        ]);

        $weights = [
            'c1_pendapatan' => 0.40,
            'c2_tanggungan' => 0.30,
            'c3_tempat_tinggal' => 0.15,
            'c4_status_hubungan' => 0.15,
        ];

        $this->postJson('/api/v1/ranking/weights', [
            'period_id' => $period->id,
            'weights' => $weights,
            'use_global' => false,
        ])->assertOk();

        $weightsResponse = $this->getJson("/api/v1/ranking/weights?period_id={$period->id}")
            ->assertOk();
        $this->assertEquals(4, count($weightsResponse->json('data.criteria')));

        $preview = $this->getJson("/api/v1/ranking/preview?period_id={$period->id}")
            ->assertOk();
        $this->assertEquals(3, $preview->json('data.preview.summary.total_candidates'));
        $this->assertEquals(3, count($preview->json('data.preview.decision_matrix')));
        $this->assertEquals(3, count($preview->json('data.preview.normalization_matrix')));
        $this->assertEquals(3, count($preview->json('data.preview.scoring')));

        $this->postJson('/api/v1/ranking/execute', ['period_id' => $period->id])
            ->assertOk()
            ->assertJsonPath('data.ranked_count', 3);

        $this->assertDatabaseHas('saw_results', [
            'bansos_period_id' => $period->id,
        ]);
        $this->assertDatabaseHas('calon_penerimas', [
            'bansos_period_id' => $period->id,
            'status_alur' => 'diranking_lingkungan_paroki',
        ]);

        $results = $this->getJson("/api/v1/ranking/results?period_id={$period->id}&stasi_id={$stasiA->id}&sort=rank")
            ->assertOk();

        $this->assertEquals($period->id, $results->json('data.period.id'));
        $this->assertEquals($stasiA->id, $results->json('data.filters.stasi_id'));
        $this->assertEquals(2, $results->json('data.stats.total_ranked'));

        $topRow = $results->json('data.rows.0');
        $this->assertNotNull($topRow['rank_internal_stasi']);
        $this->assertNotNull($topRow['rank_global']);

        $this->postJson("/api/v1/ranking/send-to-paroki/{$period->id}")
            ->assertOk();

        $this->assertDatabaseHas('bansos_periods', [
            'id' => $period->id,
            'is_locked' => 1,
            'status_periode' => 'selesai',
        ]);

        $this->postJson('/api/v1/ranking/weights', [
            'period_id' => $period->id,
            'weights' => $weights,
        ])->assertStatus(409);
    }

    public function test_weights_validation_rejects_invalid_total()
    {
        $this->seed(SawCriteriaSeeder::class);

        $period = BansosPeriod::create([
            'nama_periode' => 'Bansos 2027',
            'tahun' => 2027,
            'status_periode' => 'aktif',
        ]);

        $lingkunganParoki = LingkunganParoki::create([
            'nama_lingkungan_paroki' => 'Paroki Val',
            'kode_wilayah' => 'PRK-VAL',
        ]);

        $ketuaParoki = User::factory()->create([
            'role' => 'ketua_lingkungan_paroki',
            'lingkungan_paroki_id' => $lingkunganParoki->id,
        ]);

        Sanctum::actingAs($ketuaParoki);

        $this->postJson('/api/v1/ranking/weights', [
            'period_id' => $period->id,
            'weights' => [
                'c1_pendapatan' => 0.5,
                'c2_tanggungan' => 0.3,
                'c3_tempat_tinggal' => 0.15,
                'c4_status_hubungan' => 0.2,
            ],
        ])->assertStatus(422);
    }
}

