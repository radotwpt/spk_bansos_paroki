<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DocumentTemplate::updateOrCreate(
            ['slug' => 'surat-permohonan-stasi'],
            [
                'name' => 'Surat Permohonan Stasi',
                'type' => 'permohonan_stasi',
                'content' => <<<'HTML'
<h2 style="margin:0 0 12px;">Surat Permohonan Bantuan Sosial</h2>
<p>Nomor: {{nomor_surat}}</p>
<p>Tanggal: {{tanggal}}</p>
<p>Kepada Yth. Tim Paroki</p>
<p>Dengan hormat, kami dari <strong>{{nama_stasi}}</strong> mengajukan data calon penerima bansos untuk periode <strong>{{nama_periode}} ({{tahun}})</strong>.</p>
<div>{{daftar_penerima}}</div>
<p>Total penerima: <strong>{{total_penerima}}</strong></p>
<p>Hormat kami,</p>
<p><strong>{{nama_stasi}}</strong></p>
HTML,
            ]
        );

        DocumentTemplate::updateOrCreate(
            ['slug' => 'surat-edaran-paroki'],
            [
                'name' => 'Surat Edaran Paroki',
                'type' => 'edaran_paroki',
                'content' => <<<'HTML'
<h2 style="margin:0 0 12px;">Surat Edaran Keputusan Bansos</h2>
<p>Nomor: {{nomor_surat}}</p>
<p>Tanggal: {{tanggal}}</p>
<p>Perihal: Keputusan Penerima Bansos Periode <strong>{{nama_periode}} ({{tahun}})</strong></p>
<p>Berikut daftar penerima dari stasi: <strong>{{nama_stasi}}</strong></p>
<div>{{daftar_penerima}}</div>
<p>Total penerima: <strong>{{total_penerima}}</strong></p>
<p>Total nominal: <strong>Rp {{total_nominal}}</strong></p>
<p>Atas perhatian semua pihak, kami ucapkan terima kasih.</p>
HTML,
            ]
        );
    }
}

