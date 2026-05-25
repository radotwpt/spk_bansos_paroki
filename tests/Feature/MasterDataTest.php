<?php

namespace Tests\Feature;

use App\Models\BansosPeriod;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_stasi_crud_and_prevent_delete_when_used()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/master/stasis', [
                'nama_stasi' => 'Stasi Test',
                'kode_stasi' => 'ST-TST',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.nama_stasi', 'Stasi Test');

        $stasiId = Stasi::where('kode_stasi', 'ST-TST')->value('id');

        // create lingkungan stasi referencing stasi
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/master/lingkungan-stasis', [
                'stasi_id' => $stasiId,
                'nama_lingkungan_stasi' => 'Lingkungan A',
                'kode_lingkungan' => 'LS-TST',
            ])
            ->assertStatus(201);

        // attempt to delete stasi should fail
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/master/stasis/{$stasiId}")
            ->assertStatus(409);
    }

    public function test_bansos_period_crud()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/master/bansos-periods', [
                'nama_periode' => 'Bansos 2027',
                'tahun' => 2027,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.nama_periode', 'Bansos 2027');
    }

    public function test_user_role_validation_requires_stasi_for_stasi_role()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/master/users', [
                'name' => 'User Stasi',
                'email' => 'userstasi@example.com',
                'password' => 'password',
                'role' => 'stasi',
            ])
            ->assertStatus(422);
    }
}
