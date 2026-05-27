<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreUserRequest;
use App\Http\Requests\Master\UpdateUserRequest;
use App\Models\LingkunganStasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 15);

        $query = User::query();

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        $items = $query->orderBy('name')->paginate($perPage);

        return $this->success($items, 'Daftar user diambil.');
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

        $user = User::create($data);

        return $this->success($user, 'User berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return $this->success($user);
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

        return $this->success($user->fresh(), 'User berhasil diperbarui.');
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
