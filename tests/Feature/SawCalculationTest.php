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

    public function test_preview_and_execute_and_lock_flow()
    {
        // seed criteria and global weights
        $this->seed(SawCriteriaSeeder::class);

        $period = BansosPeriod::create([ 'nama_periode' => 'Bansos 2026', 'tahun' => 2026, 'status_periode' => 'aktif' ]);

        $lp = LingkunganParoki::create([ 'nama_lingkungan_paroki' => 'LP Test', 'kode_wilayah' => 'LP-T' ]);

        $stasi = Stasi::factory()->create();
        $lingkunganStasi = LingkunganStasi::factory()->create(['stasi_id' => $stasi->id]);

        $ketua = User::factory()->create([
            'role' => 'ketua_lingkungan_paroki',
            'lingkungan_paroki_id' => $lp->id,
            'password' => 'password',
        ]);

        // create candidates
        $c1 = CalonPenerima::create([
            'bansos_period_id' => $period->id,
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkunganStasi->id,
            'nik' => '1111111111111111',
            'nama_lengkap' => 'A',
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 100000,
            'jumlah_tanggungan' => 1,
            'status_tempat_tinggal' => 'numpang',
            'status_hubungan' => 'lajang',
            'status_alur' => 'disetujui_stasi'
        ]);

        $c2 = CalonPenerima::create([
            'bansos_period_id' => $period->id,
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkunganStasi->id,
            'nik' => '2222222222222222',
            'nama_lengkap' => 'B',
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 300000,
            'jumlah_tanggungan' => 3,
            'status_tempat_tinggal' => 'sewa',
            'status_hubungan' => 'menikah',
            'status_alur' => 'disetujui_stasi'
        ]);

        Sanctum::actingAs($ketua);

        // save custom weights for period
        $weights = [
            'c1_pendapatan' => 0.4,
            'c2_tanggungan' => 0.3,
            'c3_tempat_tinggal' => 0.15,
            'c4_status_hubungan' => 0.15,
        ];

        $this->postJson("/api/v1/lingkungan-paroki/saw/weights/{$period->id}", ['weights' => $weights])->assertOk();

        // preview (do not persist)
        $preview = $this->getJson("/api/v1/lingkungan-paroki/saw/preview/{$period->id}")->assertOk();
        $this->assertCount(2, $preview->json('preview'));

        // execute SAW (persist)
        $this->postJson("/api/v1/lingkungan-paroki/proses-saw/{$period->id}")->assertOk();

        $this->assertDatabaseHas('saw_results', [ 'bansos_period_id' => $period->id, 'calon_penerima_id' => $c1->id ]);
        $this->assertDatabaseHas('calon_penerimas', [ 'id' => $c1->id, 'status_alur' => 'diranking_lingkungan_paroki' ]);

        // send to paroki (locks period)
        $this->postJson("/api/v1/lingkungan-paroki/kirim-ke-paroki/{$period->id}")->assertOk();

        $this->assertDatabaseHas('bansos_periods', [ 'id' => $period->id, 'is_locked' => 1 ]);

        // attempts to change weights should fail
        $this->postJson("/api/v1/lingkungan-paroki/saw/weights/{$period->id}", ['weights' => $weights])->assertStatus(409);
    }
}
