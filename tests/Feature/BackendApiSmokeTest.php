<?php

namespace Tests\Feature;

use App\Models\CalonPenerima;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\PeriodeBantuan;
use App\Models\Stasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BackendApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_user_can_login_and_access_protected_master_data(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@spk-bansos.local',
            'password' => 'admin12345',
            'device_name' => 'phpunit',
        ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token_type', 'access_token', 'user']]);

        $token = $login->json('data.access_token');

        $this->withToken($token)
            ->getJson('/api/v1/master-data/roles')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(4, 'data');
    }

    public function test_paroki_can_calculate_and_finalize_saw_ranking(): void
    {
        $paroki = Paroki::query()->where('code', 'PAROKI001')->firstOrFail();
        $stasi = Stasi::query()->where('code', 'STASI001')->firstOrFail();
        $lingkungan = Lingkungan::query()->where('code', 'LING001')->firstOrFail();
        $parokiUser = User::query()->where('email', 'paroki1@spk-bansos.local')->firstOrFail();
        $creator = User::query()->where('email', 'ling1@spk-bansos.local')->firstOrFail();

        $period = PeriodeBantuan::query()->create([
            'paroki_id' => $paroki->id,
            'code' => 'TEST-2026-06',
            'name' => 'Periode Test Juni 2026',
            'aid_type' => 'tunai',
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
            'quota' => 1,
            'default_aid_amount' => 250000,
            'total_budget' => 250000,
            'status' => 'ranking',
        ]);

        $highPriority = $this->createCandidate($period, $paroki, $stasi, $lingkungan, $creator, [
            'name' => 'Maria Prioritas',
            'nik' => '3173000000000001',
            'nomor_kk' => '3173000000001001',
            'monthly_income' => 500000,
            'dependents_count' => 5,
            'housing_status' => 'tidak_tetap',
            'housing_status_score' => 4,
            'has_disability' => true,
            'disability_score' => 2,
        ]);

        $this->createCandidate($period, $paroki, $stasi, $lingkungan, $creator, [
            'name' => 'Yusuf Pembanding',
            'nik' => '3173000000000002',
            'nomor_kk' => '3173000000001002',
            'monthly_income' => 2500000,
            'dependents_count' => 1,
            'housing_status' => 'milik_sendiri',
            'housing_status_score' => 1,
            'has_disability' => false,
            'disability_score' => 1,
        ]);

        Sanctum::actingAs($parokiUser);

        $this->postJson('/api/v1/ranking/calculate', [
            'periode_bantuan_id' => $period->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_candidates_ranked', 2);

        $this->assertDatabaseHas('saw_results', [
            'periode_bantuan_id' => $period->id,
            'calon_penerima_id' => $highPriority->id,
            'rank' => 1,
        ]);

        $this->postJson("/api/v1/ranking/finalize/{$period->id}", [
            'approved_count' => 1,
            'notes' => 'Disetujui pada rapat paroki.',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.approved_count', 1);

        $this->assertDatabaseHas('penerima_bantuans', [
            'periode_bantuan_id' => $period->id,
            'calon_penerima_id' => $highPriority->id,
            'final_status' => 'selected',
            'disbursement_status' => 'pending',
        ]);

        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $highPriority->id,
            'status' => 'approved_final',
        ]);
    }

    public function test_ketua_lingkungan_can_batch_submit_eligible_candidates(): void
    {
        $paroki = Paroki::query()->where('code', 'PAROKI001')->firstOrFail();
        $stasi = Stasi::query()->where('code', 'STASI001')->firstOrFail();
        $lingkungan = Lingkungan::query()->where('code', 'LING001')->firstOrFail();
        $creator = User::query()->where('email', 'ling1@spk-bansos.local')->firstOrFail();

        $period = PeriodeBantuan::query()->create([
            'paroki_id' => $paroki->id,
            'code' => 'TEST-BATCH-2026',
            'name' => 'Periode Test Batch 2026',
            'aid_type' => 'tunai',
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-12-31',
            'quota' => 10,
            'default_aid_amount' => 250000,
            'total_budget' => 2500000,
            'status' => 'open',
        ]);

        $firstDraft = $this->createCandidate($period, $paroki, $stasi, $lingkungan, $creator, [
            'name' => 'Batch Draft Satu',
            'nik' => '3173000000000101',
            'nomor_kk' => '3173000000001101',
            'monthly_income' => 800000,
            'dependents_count' => 4,
            'housing_status' => 'menumpang',
            'housing_status_score' => 3,
            'has_disability' => false,
            'disability_score' => 1,
            'status' => 'draft',
        ]);
        $secondDraft = $this->createCandidate($period, $paroki, $stasi, $lingkungan, $creator, [
            'name' => 'Batch Draft Dua',
            'nik' => '3173000000000102',
            'nomor_kk' => '3173000000001102',
            'monthly_income' => 900000,
            'dependents_count' => 3,
            'housing_status' => 'kontrak',
            'housing_status_score' => 2,
            'has_disability' => true,
            'disability_score' => 2,
            'status' => 'revision_requested',
        ]);
        $alreadySubmitted = $this->createCandidate($period, $paroki, $stasi, $lingkungan, $creator, [
            'name' => 'Batch Sudah Terkirim',
            'nik' => '3173000000000103',
            'nomor_kk' => '3173000000001103',
            'monthly_income' => 1200000,
            'dependents_count' => 2,
            'housing_status' => 'milik_sendiri',
            'housing_status_score' => 1,
            'has_disability' => false,
            'disability_score' => 1,
            'status' => 'submitted_to_stasi',
        ]);

        Sanctum::actingAs($creator);

        $this->postJson('/api/v1/calon-penerimas/batch-submit', [
            'candidate_ids' => [$firstDraft->id, $secondDraft->id, $alreadySubmitted->id],
            'notes' => 'Batch test.',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.submitted_count', 2)
            ->assertJsonPath('data.skipped_count', 1);

        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $firstDraft->id,
            'status' => 'submitted_to_stasi',
            'submitted_by' => $creator->id,
        ]);
        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $secondDraft->id,
            'status' => 'submitted_to_stasi',
            'submitted_by' => $creator->id,
        ]);
        $this->assertDatabaseHas('calon_penerimas', [
            'id' => $alreadySubmitted->id,
            'status' => 'submitted_to_stasi',
            'submitted_by' => null,
        ]);
        $this->assertDatabaseCount('validasi_logs', 2);
    }

    private function createCandidate(
        PeriodeBantuan $period,
        Paroki $paroki,
        Stasi $stasi,
        Lingkungan $lingkungan,
        User $creator,
        array $overrides
    ): CalonPenerima {
        return CalonPenerima::query()->create(array_merge([
            'periode_bantuan_id' => $period->id,
            'paroki_id' => $paroki->id,
            'stasi_id' => $stasi->id,
            'lingkungan_id' => $lingkungan->id,
            'created_by' => $creator->id,
            'registration_number' => null,
            'family_head_name' => null,
            'place_of_birth' => null,
            'date_of_birth' => null,
            'gender' => null,
            'address' => 'Alamat test',
            'phone' => null,
            'occupation' => null,
            'status' => 'sent_to_paroki',
        ], $overrides));
    }
}
