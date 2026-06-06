<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreLingkunganStasiRequest;
use App\Http\Requests\Master\UpdateLingkunganStasiRequest;
use App\Http\Resources\LingkunganStasiResource;
use App\Models\LingkunganStasi;
use Illuminate\Http\Request;

class LingkunganStasiController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $this->authorize('manage-master-data');

        $q = trim((string) $request->query('q', ''));
        $perPage = max(5, min(100, (int) $request->query('per_page', 15)));
        $sort = (string) $request->query('sort', 'nama_lingkungan_stasi');
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nama_lingkungan_stasi', 'kode_lingkungan', 'stasi_id', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nama_lingkungan_stasi';

        $query = LingkunganStasi::query()->with('stasi:id,nama_stasi,kode_stasi');

        if ($request->filled('stasi_id')) {
            $query->where('stasi_id', $request->integer('stasi_id'));
        }

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_lingkungan_stasi', 'like', "%{$q}%")
                    ->orWhere('kode_lingkungan', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return $this->paginated($items, LingkunganStasiResource::collection($items->items()), 'Daftar lingkungan stasi diambil.');
    }

    public function store(StoreLingkunganStasiRequest $request)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganStasi::create($request->validated());

        return $this->success(new LingkunganStasiResource($item->load('stasi:id,nama_stasi,kode_stasi')), 'Lingkungan stasi berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganStasi::findOrFail($id);

        return $this->success(new LingkunganStasiResource($item->load('stasi:id,nama_stasi,kode_stasi')), 'Detail lingkungan stasi diambil.');
    }

    public function update(UpdateLingkunganStasiRequest $request, $id)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganStasi::findOrFail($id);
        $item->update($request->validated());

        return $this->success(new LingkunganStasiResource($item->fresh()->load('stasi:id,nama_stasi,kode_stasi')), 'Lingkungan stasi berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganStasi::findOrFail($id);

        if ($item->calonPenerimas()->exists() || $item->users()->exists()) {
            return $this->error('Lingkungan stasi masih direferensi oleh data lain.', 409, [
                'related' => ['Tidak dapat menghapus lingkungan stasi yang masih dipakai.'],
            ]);
        }

        $item->delete();

        return $this->success(null, 'Lingkungan stasi berhasil dihapus.');
    }
}
