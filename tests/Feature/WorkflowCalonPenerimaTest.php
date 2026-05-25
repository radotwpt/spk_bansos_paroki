<?php

namespace Tests\Feature;

use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkflowCalonPenerimaTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_can_be_submitted_to_stasi()
    {
        [$stasi, $lingkungan, $period] = $this->createHierarchy();

        $user = User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkungan->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/lingkungan-stasi/calon-penerima', [
            'bansos_period_id' => $period->id,
            'nik' => '9999999999999999',
            'nama_lengkap' => 'Test Submit',
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 100000,
            'jumlah_tanggungan' => 1,
            'status_tempat_tinggal' => 'numpang',
            'status_hubungan' => 'lajang',
        ]);

        $response->assertCreated();

        $id = $response->json('data.id');

        $this->postJson("/api/v1/lingkungan-stasi/calon-penerima/{$id}/ajukan")->assertOk();

        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $id,
            'status_alur' => 'diajukan_ke_stasi',
        ]);
    }

    public function test_stasi_can_approve_submitted_candidate()
    {
        [$stasi, $lingkungan, $period] = $this->createHierarchy();

        $ketua = User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkungan->id,
        ]);

        Sanctum::actingAs($ketua);

        $res = $this->postJson('/api/v1/lingkungan-stasi/calon-penerima', [
            'bansos_period_id' => $period->id,
            'nik' => '8888888888888888',
            'nama_lengkap' => 'To Approve',
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 200000,
            'jumlah_tanggungan' => 2,
            'status_tempat_tinggal' => 'sewa',
            'status_hubungan' => 'menikah',
        ])->assertCreated();

        $id = $res->json('data.id');

        $this->postJson("/api/v1/lingkungan-stasi/calon-penerima/{$id}/ajukan")->assertOk();

        $stasiUser = User::factory()->create([
            'role' => 'stasi',
            'stasi_id' => $stasi->id,
        ]);

        Sanctum::actingAs($stasiUser);

        $this->postJson("/api/v1/stasi/calon-penerima/{$id}/approve")->assertOk();

        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $id,
            'status_alur' => 'disetujui_stasi',
        ]);
    }

    public function test_stasi_can_reject_with_reason_and_log_saved()
    {
        [$stasi, $lingkungan, $period] = $this->createHierarchy();

        $ketua = User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkungan->id,
        ]);

        Sanctum::actingAs($ketua);

        $res = $this->postJson('/api/v1/lingkungan-stasi/calon-penerima', [
            'bansos_period_id' => $period->id,
            'nik' => '7777777777777777',
            'nama_lengkap' => 'To Reject',
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 300000,
            'jumlah_tanggungan' => 3,
            'status_tempat_tinggal' => 'numpang',
            'status_hubungan' => 'cerai',
        ])->assertCreated();

        $id = $res->json('data.id');

        $this->postJson("/api/v1/lingkungan-stasi/calon-penerima/{$id}/ajukan")->assertOk();

        $stasiUser = User::factory()->create([
            'role' => 'stasi',
            'stasi_id' => $stasi->id,
        ]);

        Sanctum::actingAs($stasiUser);

        $this->postJson("/api/v1/stasi/calon-penerima/{$id}/reject", [
            'reason' => 'Data tidak lengkap',
        ])->assertOk();

        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $id,
            'status_alur' => 'ditolak',
        ]);

        $log = ActivityLog::where('action', 'reject_data')->where('model_id', $id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('ditolak', $log->meta['to']);
        $this->assertEquals('Data tidak lengkap', $log->meta['reason']);
    }

    public function test_edit_forbidden_after_submit()
    {
        [$stasi, $lingkungan, $period] = $this->createHierarchy();

        $ketua = User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkungan->id,
        ]);

        Sanctum::actingAs($ketua);

        $res = $this->postJson('/api/v1/lingkungan-stasi/calon-penerima', [
            'bansos_period_id' => $period->id,
            'nik' => '6666666666666666',
            'nama_lengkap' => 'Cannot Edit',
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 400000,
            'jumlah_tanggungan' => 4,
            'status_tempat_tinggal' => 'sewa',
            'status_hubungan' => 'menikah',
        ])->assertCreated();

        $id = $res->json('data.id');

        $this->postJson("/api/v1/lingkungan-stasi/calon-penerima/{$id}/ajukan")->assertOk();

        $this->putJson("/api/v1/lingkungan-stasi/calon-penerima/{$id}", [
            'nama_lengkap' => 'Edited Name'
        ])->assertForbidden();
    }

    private function createHierarchy(string $suffix = ''): array
    {
        $suffix = $suffix === '' ? 'D' : $suffix;

        $stasi = Stasi::create([
            'nama_stasi' => "Stasi Santo Paulus {$suffix}",
            'kode_stasi' => "STP-{$suffix}",
        ]);

        $lingkungan = LingkunganStasi::create([
            'stasi_id' => $stasi->id,
            'nama_lingkungan_stasi' => "Lingkungan {$suffix}",
            'kode_lingkungan' => "L-{$suffix}",
        ]);

        $period = BansosPeriod::firstOrCreate([
            'nama_periode' => 'Bansos 2026',
            'tahun' => 2026,
        ]);

        return [$stasi, $lingkungan, $period];
    }
}
