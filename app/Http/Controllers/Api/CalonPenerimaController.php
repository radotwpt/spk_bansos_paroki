<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CalonPenerima;
use App\Models\SawCriterionOption;
use App\Models\ValidasiLog;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CalonPenerimaController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = CalonPenerima::query()
            ->with(['periodeBantuan', 'paroki', 'stasi', 'lingkungan', 'sawResult', 'penerimaBantuan'])
            ->visibleTo($request->user());

        foreach (['periode_bantuan_id', 'paroki_id', 'stasi_id', 'lingkungan_id', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('registration_number', 'like', "%{$search}%"));
        }

        $page = $query->latest('id')->paginate((int) $request->query('per_page', 15));
        $page->setCollection($page->getCollection()->map(fn ($candidate) => $this->serializeCandidate($candidate, $request->user())));

        return $this->success($page);
    }

    public function store(Request $request, AuditService $audit)
    {
        $payload = $request->validate($this->rules());
        $payload = $this->enrichScores($payload);
        $payload['created_by'] = $request->user()->id;
        $payload['status'] = $payload['status'] ?? 'draft';

        $candidate = CalonPenerima::query()->create($payload);
        $audit->record('calon_penerimas.created', $candidate, newValues: $candidate->toArray(), request: $request);

        return $this->success($this->serializeCandidate($candidate, $request->user()), 'Calon penerima berhasil dibuat.', 201);
    }

    public function show(Request $request, CalonPenerima $calonPenerima)
    {
        $this->authorizeVisibility($request, $calonPenerima);

        return $this->success($this->serializeCandidate($calonPenerima, $request->user()));
    }

    public function update(Request $request, CalonPenerima $calonPenerima, AuditService $audit)
    {
        $this->authorizeVisibility($request, $calonPenerima);

        if (! in_array($calonPenerima->status, ['draft', 'revision_requested'], true) && ! $request->user()->hasRole('super_admin')) {
            return $this->error('Data hanya dapat diedit saat draft atau revisi.', 422);
        }

        $old = $calonPenerima->toArray();
        $payload = $request->validate($this->rules($calonPenerima));
        $calonPenerima->update($this->enrichScores($payload));
        $audit->record('calon_penerimas.updated', $calonPenerima, $old, $calonPenerima->fresh()->toArray(), request: $request);

        return $this->success($this->serializeCandidate($calonPenerima->fresh(), $request->user()), 'Calon penerima berhasil diperbarui.');
    }

    public function destroy(Request $request, CalonPenerima $calonPenerima, AuditService $audit)
    {
        $this->authorizeVisibility($request, $calonPenerima);
        $audit->record('calon_penerimas.deleted', $calonPenerima, oldValues: $calonPenerima->toArray(), request: $request);
        $calonPenerima->delete();

        return $this->success(message: 'Calon penerima berhasil dihapus.');
    }

    public function transition(Request $request, CalonPenerima $calonPenerima, string $action, AuditService $audit)
    {
        $this->authorizeVisibility($request, $calonPenerima);

        $request->validate(['notes' => ['nullable', 'string']]);
        $oldStatus = $calonPenerima->status;
        $payload = $this->transitionPayload($request, $calonPenerima, $action);

        if ($payload === null) {
            return $this->error('Aksi workflow tidak valid untuk status/role saat ini.', 422);
        }

        return DB::transaction(function () use ($request, $audit, $calonPenerima, $oldStatus, $action, $payload) {
            $calonPenerima->update($payload);

            ValidasiLog::query()->create([
                'calon_penerima_id' => $calonPenerima->id,
                'actor_id' => $request->user()->id,
                'action' => $action,
                'from_status' => $oldStatus,
                'to_status' => $payload['status'],
                'notes' => $request->input('notes'),
            ]);

            $audit->record("calon_penerimas.workflow.{$action}", $calonPenerima, ['status' => $oldStatus], $payload, request: $request);

            return $this->success($this->serializeCandidate($calonPenerima->fresh(), $request->user()), 'Status pengajuan berhasil diperbarui.');
        });
    }

    public function batchSubmit(Request $request, AuditService $audit)
    {
        $payload = $request->validate([
            'candidate_ids' => ['required', 'array', 'min:1', 'max:100'],
            'candidate_ids.*' => ['integer', 'distinct', 'exists:calon_penerimas,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $ids = collect($payload['candidate_ids'])->map(fn ($id) => (int) $id)->values();
        $visibleCount = CalonPenerima::query()
            ->visibleTo($request->user())
            ->whereIn('id', $ids)
            ->count();

        if ($visibleCount !== $ids->count()) {
            return $this->error('Sebagian data tidak tersedia untuk user ini.', 403);
        }

        return DB::transaction(function () use ($request, $audit, $ids) {
            $candidates = CalonPenerima::query()
                ->visibleTo($request->user())
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->sortBy(fn ($candidate) => $ids->search($candidate->id))
                ->values();

            $submitted = [];
            $skipped = [];

            foreach ($candidates as $candidate) {
                $oldStatus = $candidate->status;
                $transition = $this->transitionPayload($request, $candidate, 'submit-to-stasi');

                if ($transition === null) {
                    $skipped[] = [
                        'id' => $candidate->id,
                        'name' => $candidate->name,
                        'status' => $candidate->status,
                        'reason' => 'Status atau role tidak valid untuk diajukan.',
                    ];

                    continue;
                }

                $candidate->update($transition);

                ValidasiLog::query()->create([
                    'calon_penerima_id' => $candidate->id,
                    'actor_id' => $request->user()->id,
                    'action' => 'batch-submit-to-stasi',
                    'from_status' => $oldStatus,
                    'to_status' => $transition['status'],
                    'notes' => $request->input('notes'),
                    'metadata' => ['batch' => true],
                ]);

                $audit->record(
                    'calon_penerimas.workflow.batch-submit-to-stasi',
                    $candidate,
                    ['status' => $oldStatus],
                    $transition,
                    request: $request
                );

                $submitted[] = $this->serializeCandidate($candidate->fresh(), $request->user());
            }

            if (count($submitted) === 0) {
                return $this->error('Tidak ada calon penerima yang dapat diajukan.', 422, [
                    'skipped' => $skipped,
                ]);
            }

            $audit->record('calon_penerimas.workflow.batch-submitted', metadata: [
                'candidate_ids' => $ids->all(),
                'submitted_count' => count($submitted),
                'skipped_count' => count($skipped),
            ], request: $request);

            return $this->success([
                'submitted_count' => count($submitted),
                'skipped_count' => count($skipped),
                'submitted' => $submitted,
                'skipped' => $skipped,
            ], count($skipped) > 0
                ? 'Sebagian calon penerima berhasil diajukan.'
                : 'Semua calon penerima terpilih berhasil diajukan.');
        });
    }

    private function rules(?CalonPenerima $candidate = null): array
    {
        $periodeId = request('periode_bantuan_id', $candidate?->periode_bantuan_id);

        return [
            'periode_bantuan_id' => ['required', 'exists:periode_bantuans,id'],
            'paroki_id' => ['required', 'exists:parokis,id'],
            'stasi_id' => ['required', 'exists:stasis,id'],
            'lingkungan_id' => ['required', 'exists:lingkungans,id'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:32', Rule::unique('calon_penerimas', 'nik')->where('periode_bantuan_id', $periodeId)->ignore($candidate?->id)],
            'nomor_kk' => ['required', 'string', 'max:32', Rule::unique('calon_penerimas', 'nomor_kk')->where('periode_bantuan_id', $periodeId)->ignore($candidate?->id)],
            'family_head_name' => ['nullable', 'string', 'max:255'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:laki_laki,perempuan'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['required', 'numeric', 'min:0'],
            'dependents_count' => ['required', 'integer', 'min:0', 'max:255'],
            'housing_status' => ['required', 'in:milik_sendiri,kontrak,menumpang,tidak_tetap'],
            'has_disability' => ['boolean'],
            'disability_note' => ['nullable', 'string'],
            'urgency_note' => ['nullable', 'string'],
            'economic_condition_note' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,submitted_to_stasi,revision_requested,approved_by_stasi,sent_to_paroki,ranked,under_discussion,approved_final,rejected'],
        ];
    }

    private function enrichScores(array $payload): array
    {
        if (isset($payload['housing_status'])) {
            $payload['housing_status_score'] = (int) SawCriterionOption::query()
                ->whereHas('criterion', fn ($q) => $q->where('code', 'housing_status'))
                ->where('value', $payload['housing_status'])
                ->value('score');
        }

        $payload['has_disability'] = (bool) ($payload['has_disability'] ?? false);
        $payload['disability_score'] = $payload['has_disability'] ? 2 : 1;

        return $payload;
    }

    private function transitionPayload(Request $request, CalonPenerima $candidate, string $action): ?array
    {
        $user = $request->user();

        return match ($action) {
            'submit-to-stasi' => $user->hasRole('ketua_lingkungan_stasi', 'super_admin') && in_array($candidate->status, ['draft', 'revision_requested'], true)
                ? ['status' => 'submitted_to_stasi', 'submitted_by' => $user->id, 'submitted_at' => now()]
                : null,
            'request-revision' => $user->hasRole('stasi', 'super_admin') && $candidate->status === 'submitted_to_stasi'
                ? ['status' => 'revision_requested', 'validated_by' => $user->id, 'validated_at' => now(), 'stasi_validation_note' => $request->input('notes')]
                : null,
            'approve-by-stasi' => $user->hasRole('stasi', 'super_admin') && $candidate->status === 'submitted_to_stasi'
                ? ['status' => 'approved_by_stasi', 'validated_by' => $user->id, 'validated_at' => now(), 'stasi_validation_note' => $request->input('notes')]
                : null,
            'send-to-paroki' => $user->hasRole('stasi', 'super_admin') && $candidate->status === 'approved_by_stasi'
                ? ['status' => 'sent_to_paroki', 'sent_by' => $user->id, 'sent_to_paroki_at' => now()]
                : null,
            'mark-under-discussion' => $user->hasRole('paroki', 'super_admin') && in_array($candidate->status, ['ranked', 'sent_to_paroki'], true)
                ? ['status' => 'under_discussion']
                : null,
            'reject' => $user->hasRole('stasi', 'paroki', 'super_admin')
                ? ['status' => 'rejected', 'decided_by' => $user->id, 'decided_at' => now(), 'paroki_decision_note' => $request->input('notes')]
                : null,
            default => null,
        };
    }

    private function authorizeVisibility(Request $request, CalonPenerima $candidate): void
    {
        abort_unless(CalonPenerima::query()->visibleTo($request->user())->whereKey($candidate->id)->exists(), 403, 'Data tidak tersedia untuk user ini.');
    }
}
