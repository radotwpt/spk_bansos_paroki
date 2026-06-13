<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::query()->with(['role', 'paroki', 'stasi', 'lingkungan']);

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->query('role_id'));
        }

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->query('search').'%')->orWhere('email', 'like', '%'.$request->query('search').'%'));
        }

        return $this->success($query->latest('id')->paginate((int) $request->query('per_page', 15)));
    }

    public function store(Request $request, AuditService $audit)
    {
        $payload = $request->validate($this->rules());
        $user = User::query()->create($payload);
        $audit->record('users.created', $user, newValues: $user->toArray(), request: $request);

        return $this->success($user->load(['role', 'paroki', 'stasi', 'lingkungan']), 'User berhasil dibuat.', 201);
    }

    public function show(User $user)
    {
        return $this->success($user->load(['role', 'paroki', 'stasi', 'lingkungan']));
    }

    public function update(Request $request, User $user, AuditService $audit)
    {
        $old = $user->toArray();
        $payload = $request->validate($this->rules($user));
        if (empty($payload['password'])) {
            unset($payload['password']);
        }
        $user->update($payload);
        $audit->record('users.updated', $user, $old, $user->fresh()->toArray(), request: $request);

        return $this->success($user->fresh()->load(['role', 'paroki', 'stasi', 'lingkungan']), 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user, AuditService $audit)
    {
        $audit->record('users.deleted', $user, oldValues: $user->toArray(), request: $request);
        $user->delete();

        return $this->success(message: 'User berhasil dihapus.');
    }

    private function rules(?User $user = null): array
    {
        return [
            'role_id' => ['required', 'exists:roles,id'],
            'paroki_id' => ['nullable', 'exists:parokis,id'],
            'stasi_id' => ['nullable', 'exists:stasis,id'],
            'lingkungan_id' => ['nullable', 'exists:lingkungans,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }
}
