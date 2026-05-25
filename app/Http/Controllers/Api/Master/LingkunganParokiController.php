<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreLingkunganParokiRequest;
use App\Http\Requests\Master\UpdateLingkunganParokiRequest;
use App\Models\LingkunganParoki;
use Illuminate\Http\Request;

class LingkunganParokiController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 15);

        $query = LingkunganParoki::query();

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_lingkungan_paroki', 'like', "%{$q}%")
                    ->orWhere('kode_wilayah', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('nama_lingkungan_paroki')->paginate($perPage);

        return $this->success($items, 'Daftar lingkungan paroki diambil.');
    }

    public function store(StoreLingkunganParokiRequest $request)
    {
        $item = LingkunganParoki::create($request->validated());

        return $this->success($item, 'Lingkungan paroki berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $item = LingkunganParoki::findOrFail($id);

        return $this->success($item);
    }

    public function update(UpdateLingkunganParokiRequest $request, $id)
    {
        $item = LingkunganParoki::findOrFail($id);
        $item->update($request->validated());

        return $this->success($item->fresh(), 'Lingkungan paroki berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
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
