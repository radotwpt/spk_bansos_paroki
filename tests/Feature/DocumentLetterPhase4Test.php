<?php

namespace Tests\Feature;

use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\LingkunganParoki;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use App\Models\User;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentLetterPhase4Test extends TestCase
{
    use RefreshDatabase;

    public function test_template_crud_and_delete_protection()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Sanctum::actingAs($admin);

        $create = $this->postJson('/api/v1/templates', [
            'name' => 'Template Uji',
            'slug' => 'template-uji',
            'type' => 'permohonan_stasi',
            'content' => '<p>{{nama_periode}}</p>',
        ])->assertCreated();

        $id = $create->json('data.id');

        $this->putJson("/api/v1/templates/{$id}", [
            'name' => 'Template Uji Update',
            'content' => '<p>{{nomor_surat}}</p>',
        ])->assertOk();

        $this->getJson('/api/v1/templates?type=permohonan_stasi')->assertOk();
        $this->getJson("/api/v1/templates/{$id}")->assertOk();

        // create letter using this template -> template should not be deletable
        [$stasi, $lingkunganStasi, $period] = $this->createHierarchy();
        $stasiUser = User::factory()->create([
            'role' => 'stasi',
            'stasi_id' => $stasi->id,
        ]);
        Sanctum::actingAs($stasiUser);

        $this->postJson('/api/v1/letters/generate-permohonan-stasi', [
            'template_id' => $id,
            'period_id' => $period->id,
        ])->assertCreated();

        Sanctum::actingAs($admin);
        $this->deleteJson("/api/v1/templates/{$id}")->assertStatus(422);
    }

    public function test_generate_permohonan_and_next_number_and_pdf()
    {
        Storage::fake('public');
        $this->seed(DocumentTemplateSeeder::class);

        [$stasi, $lingkunganStasi, $period] = $this->createHierarchy();
        $this->createCandidate($period->id, $stasi->id, $lingkunganStasi->id, '1234567890123456');

        $stasiUser = User::factory()->create([
            'role' => 'stasi',
            'stasi_id' => $stasi->id,
        ]);
        Sanctum::actingAs($stasiUser);

        $templateId = \App\Models\DocumentTemplate::where('slug', 'surat-permohonan-stasi')->value('id');

        $gen = $this->postJson('/api/v1/letters/generate-permohonan-stasi', [
            'template_id' => $templateId,
            'period_id' => $period->id,
        ])->assertCreated();

        $this->assertStringStartsWith('PERM/'.$period->tahun.'/', $gen->json('data.nomor_surat'));
        $this->assertEquals('permohonan_stasi', $gen->json('data.jenis_surat'));
        $this->assertNotNull($gen->json('data.file_path'));

        $next = $this->getJson('/api/v1/letters/next-number?type=permohonan_stasi&year='.$period->tahun)
            ->assertOk();
        $this->assertEquals('PERM/'.$period->tahun.'/002', $next->json('data.next_number'));

        $letterId = $gen->json('data.id');
        $this->get('/api/v1/letters/'.$letterId.'/pdf')->assertOk();
    }

    public function test_generate_edaran_with_stasi_filters()
    {
        Storage::fake('public');
        $this->seed(DocumentTemplateSeeder::class);

        [$stasiA, $lingkunganA, $period] = $this->createHierarchy('A');
        [$stasiB, $lingkunganB] = $this->createHierarchy('B');

        $this->createCandidate($period->id, $stasiA->id, $lingkunganA->id, '1111111111111111');
        $this->createCandidate($period->id, $stasiB->id, $lingkunganB->id, '2222222222222222');

        $lingkunganParoki = LingkunganParoki::create([
            'nama_lingkungan_paroki' => 'LP Test',
            'kode_wilayah' => 'LP-T',
        ]);

        $parokiUser = User::factory()->create([
            'role' => 'paroki',
            'lingkungan_paroki_id' => $lingkunganParoki->id,
        ]);
        Sanctum::actingAs($parokiUser);

        $templateId = \App\Models\DocumentTemplate::where('slug', 'surat-edaran-paroki')->value('id');

        $response = $this->postJson('/api/v1/letters/generate-edaran-paroki', [
            'template_id' => $templateId,
            'period_id' => $period->id,
            'stasi_ids' => [$stasiA->id],
        ])->assertCreated();

        $this->assertEquals('edaran_paroki', $response->json('data.jenis_surat'));
        $this->assertStringStartsWith('EDAR/'.$period->tahun.'/', $response->json('data.nomor_surat'));
    }

    private function createHierarchy(string $suffix = ''): array
    {
        $suffix = $suffix === '' ? 'D' : $suffix;

        $stasi = Stasi::create([
            'nama_stasi' => "Stasi {$suffix}",
            'kode_stasi' => "ST-{$suffix}",
        ]);

        $lingkungan = LingkunganStasi::create([
            'stasi_id' => $stasi->id,
            'nama_lingkungan_stasi' => "Lingkungan {$suffix}",
            'kode_lingkungan' => "LS-{$suffix}",
        ]);

        $period = BansosPeriod::firstOrCreate([
            'nama_periode' => 'Bansos 2026',
            'tahun' => 2026,
        ], [
            'status_periode' => 'aktif',
        ]);

        return [$stasi, $lingkungan, $period];
    }

    private function createCandidate(int $periodId, int $stasiId, int $lingkunganStasiId, string $nik): void
    {
        CalonPenerima::create([
            'bansos_period_id' => $periodId,
            'stasi_id' => $stasiId,
            'lingkungan_stasi_id' => $lingkunganStasiId,
            'nik' => $nik,
            'nama_lengkap' => 'Calon '.$nik,
            'alamat_kristen' => 'Alamat',
            'pendapatan_keluarga' => 125000,
            'jumlah_tanggungan' => 3,
            'status_tempat_tinggal' => 'numpang',
            'status_hubungan' => 'menikah',
            'status_alur' => 'disetujui_stasi',
        ]);
    }
}

