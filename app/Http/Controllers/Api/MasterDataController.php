<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\PeriodeBantuan;
use App\Models\Role;
use App\Models\Stasi;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    use ApiResponse;

    public function roles()
    {
        return $this->success(Role::query()->orderBy('label')->get());
    }

    public function index(Request $request, string $resource)
    {
        $query = $this->model($resource)::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        foreach (['paroki_id', 'stasi_id', 'status', 'type', 'is_active'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return $this->success($query->latest('id')->paginate((int) $request->query('per_page', 15)));
    }

    public function store(Request $request, string $resource, AuditService $audit)
    {
        $model = $this->model($resource);
        $payload = $request->validate($this->rules($resource));
        $record = $model::query()->create($payload);
        $audit->record("{$resource}.created", $record, newValues: $record->toArray(), request: $request);

        return $this->success($record, 'Data berhasil dibuat.', 201);
    }

    public function show(string $resource, int $id)
    {
        return $this->success($this->model($resource)::query()->findOrFail($id));
    }

    public function update(Request $request, string $resource, int $id, AuditService $audit)
    {
        $record = $this->model($resource)::query()->findOrFail($id);
        $old = $record->toArray();
        $payload = $request->validate($this->rules($resource, $record));
        $record->update($payload);
        $audit->record("{$resource}.updated", $record, $old, $record->fresh()->toArray(), request: $request);

        return $this->success($record->fresh(), 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, string $resource, int $id, AuditService $audit)
    {
        $record = $this->model($resource)::query()->findOrFail($id);
        $audit->record("{$resource}.deleted", $record, oldValues: $record->toArray(), request: $request);
        $record->delete();

        return $this->success(message: 'Data berhasil dihapus.');
    }

    /**
     * @return class-string<Model>
     */
    private function model(string $resource): string
    {
        return match ($resource) {
            'parokis' => Paroki::class,
            'stasis' => Stasi::class,
            'lingkungans' => Lingkungan::class,
            'periode-bantuans' => PeriodeBantuan::class,
            'document-templates' => DocumentTemplate::class,
            default => abort(404, 'Resource tidak ditemukan.'),
        };
    }

    private function rules(string $resource, ?Model $record = null): array
    {
        return match ($resource) {
            'parokis' => [
                'code' => ['required', 'string', 'max:255', Rule::unique('parokis', 'code')->ignore($record?->id)],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
                'phone' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'leader_name' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ],
            'stasis' => [
                'paroki_id' => ['required', 'exists:parokis,id'],
                'code' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
                'phone' => ['nullable', 'string', 'max:255'],
                'leader_name' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ],
            'lingkungans' => [
                'stasi_id' => ['required', 'exists:stasis,id'],
                'code' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'chairperson_name' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
                'phone' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ],
            'periode-bantuans' => [
                'paroki_id' => ['required', 'exists:parokis,id'],
                'code' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'aid_type' => ['nullable', 'in:tunai'],
                'starts_at' => ['required', 'date'],
                'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
                'quota' => ['nullable', 'integer', 'min:1'],
                'ranking_scope_size' => ['nullable', 'integer', 'min:1'],
                'default_aid_amount' => ['nullable', 'numeric', 'min:0'],
                'total_budget' => ['nullable', 'numeric', 'min:0'],
                'planned_disbursement_date' => ['nullable', 'date'],
                'status' => ['nullable', 'in:draft,open,closed,ranking,finalized,archived'],
            ],
            'document-templates' => [
                'code' => ['required', 'string', 'max:255', Rule::unique('document_templates', 'code')->ignore($record?->id)],
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:surat_permohonan_stasi,laporan_penerima,lainnya'],
                'subject' => ['nullable', 'string', 'max:255'],
                'body' => ['required', 'string'],
                'is_active' => ['boolean'],
            ],
            default => [],
        };
    }
}
