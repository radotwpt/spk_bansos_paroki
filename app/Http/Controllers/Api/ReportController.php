<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CalonPenerima;
use App\Models\PenerimaBantuan;
use App\Models\PeriodeBantuan;
use App\Models\ReportExport;
use App\Models\SawResult;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuditService $auditService) {}

    /**
     * Generate candidate list report
     */
    public function candidateList(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $request->validate([
            'format' => ['nullable', 'in:json,csv'],
        ]);

        $candidates = CalonPenerima::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->with(['paroki', 'stasi', 'lingkungan', 'sawResult'])
            ->orderBy('created_at')
            ->get();

        $data = $candidates->map(fn ($c) => [
            'id' => $c->id,
            'registration_number' => $c->registration_number,
            'name' => $c->name,
            'nik' => $c->nik,
            'nomor_kk' => $c->nomor_kk,
            'birth_date' => $c->date_of_birth,
            'address' => $c->address,
            'paroki' => $c->paroki?->name,
            'stasi' => $c->stasi?->name,
            'lingkungan' => $c->lingkungan?->name,
            'monthly_income' => $c->monthly_income,
            'dependents' => $c->dependents_count,
            'housing' => $c->housing_status,
            'disability' => $c->has_disability ? 'Ya' : 'Tidak',
            'status' => $c->status,
            'ranking' => $c->sawResult?->rank,
            'score' => $c->sawResult?->final_score,
        ]);

        if ($request->input('format') === 'csv') {
            return $this->exportCsv($data, "candidates_{$periodeBantuan->code}.csv");
        }

        $this->recordReport('rekap_calon_per_stasi', $periodeBantuan, $request);

        return $this->success($data);
    }

    /**
     * Generate ranking results report
     */
    public function rankingResults(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $request->validate([
            'format' => ['nullable', 'in:json,csv'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $limit = $request->input('limit');

        $query = SawResult::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->with(['calonPenerima', 'sawWeightVersion'])
            ->orderBy('rank');

        if ($limit) {
            $query->limit($limit);
        }

        $results = $query->get();

        $data = $results->map(fn ($r, $idx) => [
            'rank' => $r->rank,
            'name' => $r->calonPenerima->name,
            'nik' => $r->calonPenerima->nik,
            'address' => $r->calonPenerima->address,
            'monthly_income' => $r->monthly_income_value,
            'dependents' => $r->dependents_count_value,
            'housing_status' => $r->calonPenerima->housing_status,
            'has_disability' => $r->calonPenerima->has_disability ? 'Ya' : 'Tidak',
            'final_score' => $r->final_score,
            'normalized_income' => $r->normalized_income,
            'normalized_dependents' => $r->normalized_dependents,
            'normalized_housing' => $r->normalized_housing,
            'normalized_disability' => $r->normalized_disability,
            'calculated_at' => $r->calculated_at,
        ]);

        if ($request->input('format') === 'csv') {
            return $this->exportCsv($data, "ranking_{$periodeBantuan->code}.csv");
        }

        $this->recordReport('hasil_ranking_saw', $periodeBantuan, $request);

        return $this->success([
            'total_candidates' => $results->count(),
            'period' => [
                'id' => $periodeBantuan->id,
                'code' => $periodeBantuan->code,
                'name' => $periodeBantuan->name,
            ],
            'results' => $data,
        ]);
    }

    /**
     * Generate beneficiary list report
     */
    public function beneficiaryList(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $request->validate([
            'format' => ['nullable', 'in:json,csv'],
            'final_status' => ['nullable', 'in:selected,waiting_list,not_selected'],
            'disbursement_status' => ['nullable', 'in:pending,scheduled,disbursed,cancelled'],
        ]);

        $query = PenerimaBantuan::query()
            ->where('periode_bantuan_id', $periodeBantuan->id)
            ->with('calonPenerima');

        if ($request->filled('final_status')) {
            $query->where('final_status', $request->input('final_status'));
        }

        if ($request->filled('disbursement_status')) {
            $query->where('disbursement_status', $request->input('disbursement_status'));
        }

        $beneficiaries = $query->orderBy('id')->get();

        $data = $beneficiaries->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->calonPenerima->name,
            'nik' => $b->calonPenerima->nik,
            'nomor_kk' => $b->calonPenerima->nomor_kk,
            'address' => $b->calonPenerima->address,
            'phone' => $b->calonPenerima->phone,
            'aid_amount' => $b->aid_amount,
            'payment_method' => $b->payment_method,
            'bank_account' => $b->bank_account_number,
            'final_status' => $b->final_status,
            'disbursement_status' => $b->disbursement_status,
            'scheduled_disbursement_at' => $b->scheduled_disbursement_at,
            'disbursed_at' => $b->disbursed_at,
        ]);

        if ($request->input('format') === 'csv') {
            return $this->exportCsv($data, "beneficiaries_{$periodeBantuan->code}.csv");
        }

        $this->recordReport('surat_permohonan_pdf', $periodeBantuan, $request);

        return $this->success([
            'total_beneficiaries' => $beneficiaries->count(),
            'period' => [
                'id' => $periodeBantuan->id,
                'code' => $periodeBantuan->code,
                'name' => $periodeBantuan->name,
            ],
            'beneficiaries' => $data,
        ]);
    }

    /**
     * Generate Surat Permohonan (formal request letter)
     */
    public function generateSuratPermohonan(Request $request, PeriodeBantuan $periodeBantuan)
    {
        $request->validate([
            'stasi_id' => ['required', 'exists:stasis,id'],
            'title' => ['nullable', 'string'],
        ]);

        try {
            // Get candidates from specific stasi for this period
            $candidates = CalonPenerima::query()
                ->where('periode_bantuan_id', $periodeBantuan->id)
                ->where('stasi_id', $request->input('stasi_id'))
                ->whereIn('status', ['sent_to_paroki', 'ranked', 'under_discussion', 'approved_final'])
                ->with(['stasi', 'paroki'])
                ->get();

            if ($candidates->isEmpty()) {
                return $this->error('Tidak ada kandidat untuk surat permohonan di stasi ini.', 422);
            }

            // Generate letter data
            $letterData = [
                'period' => $periodeBantuan,
                'stasi' => $candidates->first()->stasi,
                'paroki' => $candidates->first()->paroki,
                'total_applicants' => $candidates->count(),
                'total_amount_requested' => $candidates->sum(fn ($c) => $periodeBantuan->default_aid_amount),
                'candidates' => $candidates->map(fn ($c) => [
                    'name' => $c->name,
                    'nik' => $c->nik,
                    'address' => $c->address,
                    'amount' => $periodeBantuan->default_aid_amount,
                ])->values(),
                'generated_at' => now(),
            ];

            $this->recordReport('surat_permohonan_pdf', $periodeBantuan, $request);

            return $this->success([
                'letter_type' => 'surat_permohonan',
                'letter_data' => $letterData,
            ], 'Surat permohonan berhasil digenerate.');
        } catch (\Exception $e) {
            return $this->error('Gagal membuat surat permohonan: '.$e->getMessage(), 422);
        }
    }

    /**
     * Export report to file
     */
    public function export(Request $request, string $reportType, PeriodeBantuan $periodeBantuan)
    {
        $request->validate([
            'format' => ['nullable', 'in:csv,xlsx,pdf'],
        ]);

        $format = $request->input('format', 'csv');

        try {
            $data = match ($reportType) {
                'candidate-list' => CalonPenerima::query()
                    ->where('periode_bantuan_id', $periodeBantuan->id)
                    ->get(),
                'ranking-results' => SawResult::query()
                    ->where('periode_bantuan_id', $periodeBantuan->id)
                    ->orderBy('rank')
                    ->get(),
                'beneficiaries' => PenerimaBantuan::query()
                    ->where('periode_bantuan_id', $periodeBantuan->id)
                    ->get(),
                default => abort(404, 'Jenis laporan tidak ditemukan.'),
            };

            if ($format === 'csv') {
                return $this->exportCsv($data, "{$reportType}_{$periodeBantuan->code}.csv");
            }

            // For XLSX and PDF, would require additional libraries
            return $this->error('Format '.$format.' belum didukung. Gunakan CSV.', 422);
        } catch (\Exception $e) {
            return $this->error('Gagal membuat export: '.$e->getMessage(), 422);
        }
    }

    /**
     * Helper: Export to CSV
     */
    private function exportCsv($data, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Write headers
            if ($data->isNotEmpty()) {
                $first = $data->first();
                $headers = is_array($first) ? array_keys($first) : $first->getAttributes();
                fputcsv($file, $headers);
            }

            // Write data
            foreach ($data as $row) {
                fputcsv($file, is_array($row) ? $row : $row->toArray());
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Helper: Record report export
     */
    private function recordReport(string $type, PeriodeBantuan $period, Request $request): void
    {
        ReportExport::query()->create([
            'periode_bantuan_id' => $period->id,
            'paroki_id' => $period->paroki_id,
            'stasi_id' => $request->input('stasi_id'),
            'generated_by' => $request->user()->id,
            'type' => $type,
            'title' => $request->input('title', "Laporan {$type} - {$period->code}"),
            'filters' => $request->query(),
            'status' => 'completed',
            'generated_at' => now(),
        ]);

        $this->auditService->record(
            'reports.exported',
            $period,
            newValues: ['report_type' => $type],
            request: $request
        );
    }
}
