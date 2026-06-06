<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreLingkunganParokiRequest;
use App\Http\Requests\Master\UpdateLingkunganParokiRequest;
use App\Http\Resources\LingkunganParokiResource;
use App\Models\LingkunganParoki;
use Illuminate\Http\Request;

class LingkunganParokiController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $this->authorize('manage-master-data');

        $q = trim((string) $request->query('q', ''));
        $perPage = max(5, min(100, (int) $request->query('per_page', 15)));
        $sort = (string) $request->query('sort', 'nama_lingkungan_paroki');
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nama_lingkungan_paroki', 'kode_wilayah', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'nama_lingkungan_paroki';

        $query = LingkunganParoki::query();

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_lingkungan_paroki', 'like', "%{$q}%")
                    ->orWhere('kode_wilayah', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return $this->paginated($items, LingkunganParokiResource::collection($items->items()), 'Daftar lingkungan paroki diambil.');
    }

    public function store(StoreLingkunganParokiRequest $request)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganParoki::create($request->validated());

        return $this->success(new LingkunganParokiResource($item), 'Lingkungan paroki berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganParoki::findOrFail($id);

        return $this->success(new LingkunganParokiResource($item), 'Detail lingkungan paroki diambil.');
    }

    public function update(UpdateLingkunganParokiRequest $request, $id)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganParoki::findOrFail($id);
        $item->update($request->validated());

        return $this->success(new LingkunganParokiResource($item->fresh()), 'Lingkungan paroki berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage-master-data');

        $item = LingkunganParoki::findOrFail($id);

        if ($item->users()->exists()) {
            return $this->error('Lingkungan paroki masih direferensi oleh data lain.', 409, [
                'related' => ['Tidak dapat menghapus lingkungan paroki yang masih dipakai.'],
            ]);
        }

        $item->delete();

        return $this->success(null, 'Lingkungan paroki berhasil dihapus.');
    }
}
