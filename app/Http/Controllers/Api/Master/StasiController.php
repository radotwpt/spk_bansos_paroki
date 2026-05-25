<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreStasiRequest;
use App\Http\Requests\Master\UpdateStasiRequest;
use App\Models\Stasi;
use Illuminate\Http\Request;

class StasiController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 15);

        $query = Stasi::query();

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_stasi', 'like', "%{$q}%")
                    ->orWhere('kode_stasi', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('nama_stasi')->paginate($perPage);

        return $this->success($items, 'Daftar stasi diambil.');
    }

    public function store(StoreStasiRequest $request)
    {
        $stasi = Stasi::create($request->validated());

        return $this->success($stasi, 'Stasi berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $stasi = Stasi::findOrFail($id);

        return $this->success($stasi);
    }

    public function update(UpdateStasiRequest $request, $id)
    {
        $stasi = Stasi::findOrFail($id);
        $stasi->update($request->validated());

        return $this->success($stasi->fresh(), 'Stasi berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
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
