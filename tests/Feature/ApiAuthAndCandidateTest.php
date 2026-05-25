<?php

namespace Tests\Feature;

use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthAndCandidateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        User::factory()->create([
            'email' => 'ketua@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ketua@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'data' => ['token', 'user' => ['id', 'email', 'role']]]);
    }

    public function test_authenticated_user_can_fetch_me(): void
    {
        $user = User::factory()->create([
            'email' => 'ketua@example.com',
            'role' => 'ketua_lingkungan_stasi',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'ketua@example.com')
            ->assertJsonPath('data.role', 'ketua_lingkungan_stasi');
    }

    public function test_guest_cannot_access_protected_api(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_user_with_wrong_role_gets_forbidden_response(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => 'paroki',
        ]));

        $this->getJson('/api/v1/stasi/calon-penerima-rekap')
            ->assertForbidden()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_database_seeder_creates_demo_users_for_all_roles(): void
    {
        $this->seed();

        foreach ([
            'super_admin',
            'paroki',
            'ketua_lingkungan_paroki',
            'stasi',
            'ketua_lingkungan_stasi',
        ] as $role) {
            $this->assertDatabaseHas('users', [
                'role' => $role,
            ]);
        }
    }

    public function test_ketua_lingkungan_stasi_can_create_candidate(): void
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
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Maria Lestari',
            'alamat_kristen' => 'Jalan Kasih 1',
            'pendapatan_keluarga' => 750000,
            'jumlah_tanggungan' => 4,
            'status_tempat_tinggal' => 'sewa',
            'status_hubungan' => 'cerai',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.nama_lengkap', 'Maria Lestari')
            ->assertJsonPath('data.lingkungan_stasi_id', $lingkungan->id)
            ->assertJsonPath('data.stasi_id', $stasi->id);

        $this->assertDatabaseHas('calon_penerimas', [
            'nik' => '1234567890123456',
            'status_alur' => 'draft',
        ]);
    }

    public function test_stasi_user_only_sees_candidates_from_their_stasi(): void
    {
        [$stasiA, $lingkunganA, $period] = $this->createHierarchy('A');
        [$stasiB, $lingkunganB] = $this->createHierarchy('B');

        CalonPenerima::create($this->candidatePayload($period, $stasiA, $lingkunganA, [
            'nik' => '1111111111111111',
            'nama_lengkap' => 'Kandidat Stasi A',
        ]));

        CalonPenerima::create($this->candidatePayload($period, $stasiB, $lingkunganB, [
            'nik' => '2222222222222222',
            'nama_lengkap' => 'Kandidat Stasi B',
        ]));

        Sanctum::actingAs(User::factory()->create([
            'role' => 'stasi',
            'stasi_id' => $stasiA->id,
        ]));

        $this->getJson('/api/v1/stasi/calon-penerima-rekap')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_lengkap', 'Kandidat Stasi A');
    }

    public function test_offline_sync_uses_authenticated_users_tenant(): void
    {
        [$stasiA, $lingkunganA, $period] = $this->createHierarchy('A');
        [$stasiB, $lingkunganB] = $this->createHierarchy('B');

        Sanctum::actingAs(User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasiA->id,
            'lingkungan_stasi_id' => $lingkunganA->id,
        ]));

        $this->postJson('/api/v1/offline/sync', [
            'action' => 'submit_candidate',
            'payload' => [
                'bansos_period_id' => $period->id,
                'nik' => '3333333333333333',
                'nama_lengkap' => 'Kandidat Offline',
                'pendapatan_keluarga' => 500000,
                'jumlah_tanggungan' => 3,
                'status_tempat_tinggal' => 'numpang',
                'status_hubungan' => 'cerai',
                'stasi_id' => $stasiB->id,
                'lingkungan_stasi_id' => $lingkunganB->id,
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('calon_penerimas', [
            'nik' => '3333333333333333',
            'stasi_id' => $stasiA->id,
            'lingkungan_stasi_id' => $lingkunganA->id,
        ]);
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

    private function candidatePayload(BansosPeriod $period, Stasi $stasi, LingkunganStasi $lingkungan, array $overrides = []): array
    {
        return array_merge([
            'bansos_period_id' => $period->id,
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkungan->id,
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Maria Lestari',
            'alamat_kristen' => 'Jalan Kasih 1',
            'pendapatan_keluarga' => 750000,
            'jumlah_tanggungan' => 4,
            'status_tempat_tinggal' => 'sewa',
            'status_hubungan' => 'cerai',
        ], $overrides);
    }
}
