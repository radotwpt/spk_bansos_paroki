<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PeriodeBantuan;
use App\Models\PenerimaBantuan;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaBantuanController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuditService $auditService) {}

    /**
     * List beneficiaries for a period
     */
    public function index(Request $request)
    {
        $request->validate([
            'periode_bantuan_id' => ['required', 'exists:periode_bantuans,id'],
            'final_status' => ['nullable', 'in:selected,waiting_list,not_selected'],
            'disbursement_status' => ['nullable', 'in:pending,scheduled,disbursed,cancelled'],
        ]);

        $query = PenerimaBantuan::query()
            ->where('periode_bantuan_id', $request->input('periode_bantuan_id'))
            ->with(['calonPenerima', 'periodeBantuan']);

        if ($request->filled('final_status')) {
            $query->where('final_status', $request->input('final_status'));
        }

        if ($request->filled('disbursement_status')) {
            $query->where('disbursement_status', $request->input('disbursement_status'));
        }

        if ($request->filled('search')) {
            $query->whereHas('calonPenerima', fn ($q) => $q
                ->where('name', 'like', '%'.$request->input('search').'%')
                ->orWhere('nik', 'like', '%'.$request->input('search').'%')
            );
        }

        $beneficiaries = $query->orderBy('id')->paginate((int) $request->query('per_page', 15));

        return $this->success(
            $beneficiaries->through(fn ($b) => [
                'id' => $b->id,
                'candidate' => [
                    'id' => $b->calonPenerima->id,
                    'name' => $b->calonPenerima->name,
                    'nik' => $b->calonPenerima->nik,
                    'nomor_kk' => $b->calonPenerima->nomor_kk,
                    'address' => $b->calonPenerima->address,
                    'phone' => $b->calonPenerima->phone,
                ],
                'aid_info' => [
                    'amount' => $b->aid_amount,
                    'final_status' => $b->final_status,
                    'disbursement_status' => $b->disbursement_status,
                    'payment_method' => $b->payment_method,
                    'scheduled_disbursement_at' => $b->scheduled_disbursement_at,
                    'bank_account' => $b->bank_account_number,
                ],
                'timestamps' => [
                    'created_at' => $b->created_at,
                    'updated_at' => $b->updated_at,
                    'disbursed_at' => $b->disbursed_at,
                ],
            ])
        );
    }

    /**
     * Get beneficiary details
     */
    public function show(PenerimaBantuan $penerimaBantuan)
    {
        return $this->success([
            'id' => $penerimaBantuan->id,
            'period' => [
                'id' => $penerimaBantuan->periodeBantuan->id,
                'code' => $penerimaBantuan->periodeBantuan->code,
                'name' => $penerimaBantuan->periodeBantuan->name,
            ],
            'candidate' => [
                'id' => $penerimaBantuan->calonPenerima->id,
                'name' => $penerimaBantuan->calonPenerima->name,
                'nik' => $penerimaBantuan->calonPenerima->nik,
                'nomor_kk' => $penerimaBantuan->calonPenerima->nomor_kk,
                'family_head' => $penerimaBantuan->calonPenerima->family_head_name,
                'birth_date' => $penerimaBantuan->calonPenerima->date_of_birth,
                'address' => $penerimaBantuan->calonPenerima->address,
                'phone' => $penerimaBantuan->calonPenerima->phone,
                'occupation' => $penerimaBantuan->calonPenerima->occupation,
            ],
            'economic_data' => [
                'monthly_income' => $penerimaBantuan->calonPenerima->monthly_income,
                'dependents_count' => $penerimaBantuan->calonPenerima->dependents_count,
                'housing_status' => $penerimaBantuan->calonPenerima->housing_status,
                'has_disability' => $penerimaBantuan->calonPenerima->has_disability,
            ],
            'aid_info' => [
                'amount' => $penerimaBantuan->aid_amount,
                'final_status' => $penerimaBantuan->final_status,
                'disbursement_status' => $penerimaBantuan->disbursement_status,
                'payment_method' => $penerimaBantuan->payment_method,
                'bank_account' => $penerimaBantuan->bank_account_number,
                'scheduled_disbursement_at' => $penerimaBantuan->scheduled_disbursement_at,
                'disbursed_at' => $penerimaBantuan->disbursed_at,
                'notes' => $penerimaBantuan->decision_note,
            ],
            'audit' => [
                'created_at' => $penerimaBantuan->created_at,
                'updated_at' => $penerimaBantuan->updated_at,
                'disbursed_at' => $penerimaBantuan->disbursed_at,
            ],
        ]);
    }

    /**
     * Update beneficiary information
     */
    public function update(Request $request, PenerimaBantuan $penerimaBantuan)
    {
        $request->validate([
            'aid_amount' => ['nullable', 'numeric', 'min:0'],
            'aid_description' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'in:cash,bank_transfer,other'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'scheduled_disbursement_at' => ['nullable', 'date'],
            'decision_note' => ['nullable', 'string'],
        ]);

        $old = $penerimaBantuan->toArray();
        $penerimaBantuan->update($request->only([
            'aid_amount',
            'aid_description',
            'payment_method',
            'bank_name',
            'bank_account_number',
            'bank_account_holder',
            'scheduled_disbursement_at',
            'decision_note',
        ]));

        $this->auditService->record(
            'penerima_bantuans.updated',
            $penerimaBantuan,
            $old,
            $penerimaBantuan->fresh()->toArray(),
            request: $request
        );

        return $this->success($penerimaBantuan->fresh(), 'Data penerima bantuan berhasil diperbarui.');
    }

    /**
     * Mark aid as disbursed
     */
    public function markDisbursed(Request $request, PenerimaBantuan $penerimaBantuan)
    {
        $request->validate([
            'disbursed_at' => ['nullable', 'date'],
            'disbursement_notes' => ['nullable', 'string'],
        ]);

        if ($penerimaBantuan->final_status !== 'selected') {
            return $this->error('Hanya penerima yang status-nya "selected" yang dapat dicatat pencairannya.', 422);
        }

        try {
            $old = $penerimaBantuan->toArray();

            $penerimaBantuan->update([
                'disbursement_status' => 'disbursed',
                'disbursed_at' => $request->date('disbursed_at') ?? now(),
                'decision_note' => trim(($penerimaBantuan->decision_note ?? '').' [DIBAYAR: '.$request->input('disbursement_notes', 'Pencairan bantuan').']'),
            ]);

            $this->auditService->record(
                'penerima_bantuans.disbursed',
                $penerimaBantuan,
                $old,
                $penerimaBantuan->fresh()->toArray(),
                request: $request
            );

            return $this->success($penerimaBantuan->fresh(), 'Bantuan berhasil dicatat sebagai sudah dibayarkan.');
        } catch (\Exception $e) {
            return $this->error('Gagal mencatat pencairan: '.$e->getMessage(), 422);
        }
    }
}
