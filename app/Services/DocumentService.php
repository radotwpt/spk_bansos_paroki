<?php

namespace App\Services;

use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\DocumentTemplate;
use App\Models\GeneratedLetter;
use App\Models\Stasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public const LETTER_TYPE_PERMOHONAN_STASI = 'permohonan_stasi';
    public const LETTER_TYPE_EDARAN_PAROKI = 'edaran_paroki';

    public function getOfficialPlaceholders(): array
    {
        return [
            'nama_periode',
            'tahun',
            'nama_stasi',
            'nama_lingkungan',
            'tanggal',
            'nomor_surat',
            'daftar_penerima',
            'total_penerima',
            'total_nominal',
        ];
    }

    public function renderTemplate(DocumentTemplate $template, ?CalonPenerima $calon = null, array $extra = []): string
    {
        $content = $template->content ?? '';
        $replacements = [];

        if ($calon) {
            $replacements = array_merge($replacements, [
                '{{nama}}' => $calon->nama_lengkap ?? '',
                '{{nama_lengkap}}' => $calon->nama_lengkap ?? '',
                '{{nik}}' => $calon->nik ?? '',
                '{{alamat}}' => $calon->alamat_kristen ?? '',
                '{{alamat_kristen}}' => $calon->alamat_kristen ?? '',
                '{{pendapatan_keluarga}}' => (string) ($calon->pendapatan_keluarga ?? ''),
                '{{jumlah_tanggungan}}' => (string) ($calon->jumlah_tanggungan ?? ''),
                '{{status_tempat_tinggal}}' => $calon->status_tempat_tinggal ?? '',
                '{{status_hubungan}}' => $calon->status_hubungan ?? '',
            ]);
        }

        foreach ($extra as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) $value;
        }

        return strtr($content, $replacements);
    }

    public function saveGenerated(array $payload): GeneratedLetter
    {
        return GeneratedLetter::create([
            'document_template_id' => $payload['document_template_id'],
            'calon_penerima_id' => $payload['calon_penerima_id'] ?? null,
            'bansos_period_id' => $payload['bansos_period_id'] ?? null,
            'title' => $payload['title'] ?? null,
            'jenis_surat' => $payload['jenis_surat'] ?? null,
            'nomor_surat' => $payload['nomor_surat'] ?? null,
            'content' => $payload['content'] ?? null,
            'final_html_content' => $payload['final_html_content'] ?? $payload['content'] ?? null,
            'metadata_json' => $payload['metadata_json'] ?? null,
            'file_path' => $payload['file_path'] ?? null,
            'created_by' => $payload['created_by'] ?? null,
        ]);
    }

    public function getNextLetterNumber(string $type, int $year): string
    {
        $prefix = $this->letterPrefix($type);
        $letters = GeneratedLetter::query()
            ->where('jenis_surat', $type)
            ->where('nomor_surat', 'like', $prefix.'/'.$year.'/%')
            ->pluck('nomor_surat');

        $maxSequence = 0;
        foreach ($letters as $number) {
            $parts = explode('/', (string) $number);
            $sequence = (int) ($parts[2] ?? 0);
            if ($sequence > $maxSequence) {
                $maxSequence = $sequence;
            }
        }

        $next = $maxSequence + 1;
        return sprintf('%s/%d/%03d', $prefix, $year, $next);
    }

    public function generatePermohonanStasi(
        DocumentTemplate $template,
        BansosPeriod $period,
        Stasi $stasi,
        User $actor,
        ?string $nomorSurat = null,
        ?string $customTitle = null
    ): GeneratedLetter {
        $candidates = CalonPenerima::query()
            ->withoutGlobalScopes()
            ->where('bansos_period_id', $period->id)
            ->where('stasi_id', $stasi->id)
            ->whereIn('status_alur', ['disetujui_stasi', 'diranking_lingkungan_paroki', 'disetujui_paroki'])
            ->get();

        $nomorSurat = $nomorSurat ?: $this->getNextLetterNumber(self::LETTER_TYPE_PERMOHONAN_STASI, (int) $period->tahun);

        $bindings = $this->buildCommonBindings($period, $nomorSurat);
        $bindings['nama_stasi'] = $stasi->nama_stasi;
        $bindings['nama_lingkungan'] = '-';
        $bindings['daftar_penerima'] = $this->buildRecipientListHtml($candidates);
        $bindings['total_penerima'] = (string) $candidates->count();
        $bindings['total_nominal'] = (string) number_format((float) $candidates->sum('nominal_bansos_disetujui'), 0, ',', '.');

        $html = $this->renderTemplate($template, null, $bindings);

        $letter = $this->saveGenerated([
            'document_template_id' => $template->id,
            'bansos_period_id' => $period->id,
            'title' => $customTitle ?: $template->name,
            'jenis_surat' => self::LETTER_TYPE_PERMOHONAN_STASI,
            'nomor_surat' => $nomorSurat,
            'content' => $html,
            'final_html_content' => $html,
            'metadata_json' => [
                'stasi_id' => $stasi->id,
                'stasi_nama' => $stasi->nama_stasi,
                'total_penerima' => $candidates->count(),
            ],
            'created_by' => $actor->id,
        ]);

        $this->logGenerated($template, $letter, $actor->id);

        return $letter;
    }

    public function generateEdaranParoki(
        DocumentTemplate $template,
        BansosPeriod $period,
        User $actor,
        array $stasiIds = [],
        ?string $nomorSurat = null,
        ?string $customTitle = null
    ): GeneratedLetter {
        $query = CalonPenerima::query()
            ->withoutGlobalScopes()
            ->where('bansos_period_id', $period->id)
            ->whereIn('status_alur', ['diranking_lingkungan_paroki', 'disetujui_paroki'])
            ->with('stasi:id,nama_stasi');

        if (! empty($stasiIds)) {
            $query->whereIn('stasi_id', $stasiIds);
        }

        $candidates = $query->get();
        $stasiNames = $candidates->pluck('stasi.nama_stasi')->filter()->unique()->values();

        $nomorSurat = $nomorSurat ?: $this->getNextLetterNumber(self::LETTER_TYPE_EDARAN_PAROKI, (int) $period->tahun);
        $bindings = $this->buildCommonBindings($period, $nomorSurat);
        $bindings['nama_stasi'] = $stasiNames->implode(', ');
        $bindings['nama_lingkungan'] = $actor->lingkunganParoki?->nama_lingkungan_paroki ?? '-';
        $bindings['daftar_penerima'] = $this->buildRecipientListHtml($candidates);
        $bindings['total_penerima'] = (string) $candidates->count();
        $bindings['total_nominal'] = (string) number_format((float) $candidates->sum('nominal_bansos_disetujui'), 0, ',', '.');

        $html = $this->renderTemplate($template, null, $bindings);
        $letter = $this->saveGenerated([
            'document_template_id' => $template->id,
            'bansos_period_id' => $period->id,
            'title' => $customTitle ?: $template->name,
            'jenis_surat' => self::LETTER_TYPE_EDARAN_PAROKI,
            'nomor_surat' => $nomorSurat,
            'content' => $html,
            'final_html_content' => $html,
            'metadata_json' => [
                'stasi_ids' => $stasiNames->isEmpty() ? $stasiIds : $candidates->pluck('stasi_id')->unique()->values()->all(),
                'stasi_names' => $stasiNames->all(),
                'total_penerima' => $candidates->count(),
            ],
            'created_by' => $actor->id,
        ]);

        $this->logGenerated($template, $letter, $actor->id);

        return $letter;
    }

    public function generatePdf(GeneratedLetter $letter): GeneratedLetter
    {
        $html = $letter->final_html_content ?: $letter->content ?: '<p>Konten surat kosong</p>';
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        $safeNo = $letter->nomor_surat
            ? str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', $letter->nomor_surat)
            : 'surat-'.$letter->id;
        $fileName = $safeNo.'-'.$letter->id.'.pdf';
        $relativePath = 'letters/'.$fileName;

        Storage::disk('public')->put($relativePath, $pdf->output());

        $letter->file_path = $relativePath;
        $letter->save();

        return $letter->fresh();
    }

    public function generateLetter(DocumentTemplate $template, ?CalonPenerima $calon, array $opts = []): GeneratedLetter
    {
        $content = $this->renderTemplate($template, $calon, $opts['extra'] ?? []);

        $letter = $this->saveGenerated([
            'document_template_id' => $template->id,
            'calon_penerima_id' => $calon?->id,
            'bansos_period_id' => $opts['bansos_period_id'] ?? null,
            'title' => $opts['title'] ?? $template->name,
            'content' => $content,
            'final_html_content' => $content,
            'created_by' => $opts['created_by'] ?? null,
        ]);

        $this->logGenerated($template, $letter, $opts['created_by'] ?? null);

        return $letter;
    }

    private function buildCommonBindings(BansosPeriod $period, string $nomorSurat): array
    {
        return [
            'nama_periode' => $period->nama_periode,
            'tahun' => (string) $period->tahun,
            'tanggal' => now()->translatedFormat('d F Y'),
            'nomor_surat' => $nomorSurat,
        ];
    }

    private function buildRecipientListHtml(Collection $candidates): string
    {
        if ($candidates->isEmpty()) {
            return '<p>Tidak ada data penerima.</p>';
        }

        $rows = $candidates->values()->map(function (CalonPenerima $candidate, int $index) {
            return sprintf(
                '<tr><td style="padding:6px 8px;border:1px solid #d1d5db;">%d</td><td style="padding:6px 8px;border:1px solid #d1d5db;">%s</td><td style="padding:6px 8px;border:1px solid #d1d5db;">%s</td></tr>',
                $index + 1,
                e($candidate->nama_lengkap),
                e($candidate->nik)
            );
        })->implode('');

        return '<table style="width:100%;border-collapse:collapse;border:1px solid #d1d5db;"><thead><tr><th style="text-align:left;padding:6px 8px;border:1px solid #d1d5db;">No</th><th style="text-align:left;padding:6px 8px;border:1px solid #d1d5db;">Nama</th><th style="text-align:left;padding:6px 8px;border:1px solid #d1d5db;">NIK</th></tr></thead><tbody>'.$rows.'</tbody></table>';
    }

    private function letterPrefix(string $type): string
    {
        return match ($type) {
            self::LETTER_TYPE_PERMOHONAN_STASI => 'PERM',
            self::LETTER_TYPE_EDARAN_PAROKI => 'EDAR',
            default => 'SURAT',
        };
    }

    private function logGenerated(DocumentTemplate $template, GeneratedLetter $letter, ?int $userId): void
    {
        try {
            $logger = app()->make(ActivityLogService::class);
            $logger->log('generated_letter', GeneratedLetter::class, $letter->id, $userId, [
                'template' => $template->slug ?? $template->id,
                'jenis_surat' => $letter->jenis_surat,
                'nomor_surat' => $letter->nomor_surat,
            ]);
        } catch (\Throwable) {
            // no-op
        }
    }
}

