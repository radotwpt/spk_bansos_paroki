<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreLingkunganStasiRequest;
use App\Http\Requests\Master\UpdateLingkunganStasiRequest;
use App\Models\LingkunganStasi;
use Illuminate\Http\Request;

class LingkunganStasiController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 15);

        $query = LingkunganStasi::query();

        if ($request->filled('stasi_id')) {
            $query->where('stasi_id', $request->integer('stasi_id'));
        }

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_lingkungan_stasi', 'like', "%{$q}%")
                    ->orWhere('kode_lingkungan', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('nama_lingkungan_stasi')->paginate($perPage);

        return $this->success($items, 'Daftar lingkungan stasi diambil.');
    }

    public function store(StoreLingkunganStasiRequest $request)
    {
        $item = LingkunganStasi::create($request->validated());

        return $this->success($item, 'Lingkungan stasi berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $item = LingkunganStasi::findOrFail($id);

        return $this->success($item);
    }

    public function update(UpdateLingkunganStasiRequest $request, $id)
    {
        $item = LingkunganStasi::findOrFail($id);
        $item->update($request->validated());

        return $this->success($item->fresh(), 'Lingkungan stasi berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
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
