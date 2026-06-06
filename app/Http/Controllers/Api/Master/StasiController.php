<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreStasiRequest;
use App\Http\Requests\Master\UpdateStasiRequest;
use App\Http\Resources\StasiResource;
use App\Models\Stasi;
use Illuminate\Http\Request;

class StasiController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $this->authorize('manage-master-data');

        $q = trim((string) $request->query('q', ''));
        $perPage = max(5, min(100, (int) $request->query('per_page', 15)));
        $sort = (string) $request->query('sort', 'nama_stasi');
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nama_stasi', 'kode_stasi', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nama_stasi';

        $query = Stasi::query();

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_stasi', 'like', "%{$q}%")
                    ->orWhere('kode_stasi', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return $this->paginated($items, StasiResource::collection($items->items()), 'Daftar stasi diambil.');
    }

    public function store(StoreStasiRequest $request)
    {
        $this->authorize('manage-master-data');

        $stasi = Stasi::create($request->validated());

        return $this->success(new StasiResource($stasi), 'Stasi berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $this->authorize('manage-master-data');

        $stasi = Stasi::findOrFail($id);

        return $this->success(new StasiResource($stasi), 'Detail stasi diambil.');
    }

    public function update(UpdateStasiRequest $request, $id)
    {
        $this->authorize('manage-master-data');

        $stasi = Stasi::findOrFail($id);
        $stasi->update($request->validated());

        return $this->success(new StasiResource($stasi->fresh()), 'Stasi berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage-master-data');

        $stasi = Stasi::findOrFail($id);

        if ($stasi->lingkunganStasis()->exists() || $stasi->users()->exists() || $stasi->calonPenerimas()->exists()) {
            return $this->error('Stasi masih direferensi oleh data lain.', 409, [
                'related' => ['Tidak dapat menghapus stasi yang masih dipakai.'],
            ]);
        }

        $stasi->delete();

        return $this->success(null, 'Stasi berhasil dihapus.');
    }
}
