<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreUserRequest;
use App\Http\Requests\Master\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\LingkunganStasi;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = max(5, min(100, (int) $request->query('per_page', 15)));
        $sort = (string) $request->query('sort', 'name');
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'email', 'role', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'name';

        $query = User::query()->with([
            'stasi:id,nama_stasi,kode_stasi',
            'lingkunganStasi:id,nama_lingkungan_stasi',
            'lingkunganParoki:id,nama_lingkungan_paroki',
        ]);

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        $items = $query->orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return $this->paginated($items, UserResource::collection($items->items()), 'Daftar user diambil.');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // role-specific validation
        $role = $data['role'] ?? null;

        if ($role === 'stasi' && empty($data['stasi_id'])) {
            return $this->error('Role stasi wajib memiliki stasi_id.', 422, [
                'stasi_id' => ['Stasi id wajib diisi untuk role stasi.'],
            ]);
        }

        if ($role === 'ketua_lingkungan_stasi' && (empty($data['stasi_id']) || empty($data['lingkungan_stasi_id']))) {
            return $this->error('Role ketua_lingkungan_stasi wajib memiliki stasi_id dan lingkungan_stasi_id.', 422, [
                'stasi_id' => ['Stasi id wajib diisi.'],
                'lingkungan_stasi_id' => ['Lingkungan stasi id wajib diisi.'],
            ]);
        }

        if ($role === 'ketua_lingkungan_paroki' && empty($data['lingkungan_paroki_id'])) {
            return $this->error('Role ketua_lingkungan_paroki wajib memiliki lingkungan_paroki_id.', 422, [
                'lingkungan_paroki_id' => ['Lingkungan paroki id wajib diisi.'],
            ]);
        }

        // ensure lingkungan_stasi belongs to stasi if both provided
        if (! empty($data['lingkungan_stasi_id']) && ! empty($data['stasi_id'])) {
            $ok = LingkunganStasi::whereKey($data['lingkungan_stasi_id'])->where('stasi_id', $data['stasi_id'])->exists();
            if (! $ok) {
                return $this->error('Lingkungan stasi tidak sesuai dengan stasi yang dipilih.', 422, [
                    'lingkungan_stasi_id' => ['Lingkungan stasi harus berada pada stasi yang sama.'],
                ]);
            }
        }

        $user = User::create($data)->load([
            'stasi:id,nama_stasi,kode_stasi',
            'lingkunganStasi:id,nama_lingkungan_stasi',
            'lingkunganParoki:id,nama_lingkungan_paroki',
        ]);

        return $this->success(new UserResource($user), 'User berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $user = User::with([
            'stasi:id,nama_stasi,kode_stasi',
            'lingkunganStasi:id,nama_lingkungan_stasi',
            'lingkunganParoki:id,nama_lingkungan_paroki',
        ])->findOrFail($id);

        return $this->success(new UserResource($user), 'Detail user diambil.');
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        // role-specific validation same as store
        $role = $data['role'] ?? $user->role;

        if ($role === 'stasi' && empty($data['stasi_id']) && empty($user->stasi_id)) {
            return $this->error('Role stasi wajib memiliki stasi_id.', 422, [
                'stasi_id' => ['Stasi id wajib diisi untuk role stasi.'],
            ]);
        }

        if ($role === 'ketua_lingkungan_stasi' && (empty($data['stasi_id']) && empty($user->stasi_id) || (empty($data['lingkungan_stasi_id']) && empty($user->lingkungan_stasi_id)))) {
            return $this->error('Role ketua_lingkungan_stasi wajib memiliki stasi_id dan lingkungan_stasi_id.', 422, [
                'stasi_id' => ['Stasi id wajib diisi.'],
                'lingkungan_stasi_id' => ['Lingkungan stasi id wajib diisi.'],
            ]);
        }

        if ($role === 'ketua_lingkungan_paroki' && empty($data['lingkungan_paroki_id']) && empty($user->lingkungan_paroki_id)) {
            return $this->error('Role ketua_lingkungan_paroki wajib memiliki lingkungan_paroki_id.', 422, [
                'lingkungan_paroki_id' => ['Lingkungan paroki id wajib diisi.'],
            ]);
        }

        // if password not provided, remove it from data so it won't be updated
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->load([
            'stasi:id,nama_stasi,kode_stasi',
            'lingkunganStasi:id,nama_lingkungan_stasi',
            'lingkunganParoki:id,nama_lingkungan_paroki',
        ]);

        return $this->success(new UserResource($user->fresh()), 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // prevent deletion of super_admin
        if ($user->role === 'super_admin') {
            return $this->error('Tidak boleh menghapus user super_admin.', 409);
        }

        $user->delete();

        return $this->success(null, 'User berhasil dihapus.');
    }
}
