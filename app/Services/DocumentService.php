<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\CalonPenerima;
use App\Models\GeneratedLetter;
use App\Services\ActivityLogService;

class DocumentService
{
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

        foreach ($extra as $k => $v) {
            $replacements['{{'.$k.'}}'] = (string)$v;
        }

        return strtr($content, $replacements);
    }

    public function generateLetter(DocumentTemplate $template, ?CalonPenerima $calon, array $opts = []): GeneratedLetter
    {
        $content = $this->renderTemplate($template, $calon, $opts['extra'] ?? []);

        $letter = GeneratedLetter::create([
            'document_template_id' => $template->id,
            'calon_penerima_id' => $calon?->id,
            'bansos_period_id' => $opts['bansos_period_id'] ?? null,
            'title' => $opts['title'] ?? $template->name,
            'content' => $content,
            'file_path' => null,
            'created_by' => $opts['created_by'] ?? null,
        ]);

        try {
            $logger = app()->make(ActivityLogService::class);
            $logger->log('generated_letter', GeneratedLetter::class, $letter->id, $opts['created_by'] ?? null, [
                'template' => $template->slug ?? $template->id,
                'calon_id' => $calon?->id,
            ]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return $letter;
    }
}
