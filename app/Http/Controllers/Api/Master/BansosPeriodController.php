<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBansosPeriodRequest;
use App\Http\Requests\Master\UpdateBansosPeriodRequest;
use App\Models\BansosPeriod;
use Illuminate\Http\Request;

class BansosPeriodController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 15);

        $query = BansosPeriod::query();

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_periode', 'like', "%{$q}%")
                    ->orWhere('tahun', 'like', "%{$q}%");
            });
        }

        $items = $query->orderByDesc('tahun')->paginate($perPage);

        return $this->success($items, 'Daftar periode bansos diambil.');
    }

    public function store(StoreBansosPeriodRequest $request)
    {
        $item = BansosPeriod::create($request->validated());

        return $this->success($item, 'Periode bansos berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $item = BansosPeriod::findOrFail($id);

        return $this->success($item);
    }

    public function update(UpdateBansosPeriodRequest $request, $id)
    {
        $item = BansosPeriod::findOrFail($id);
        $item->update($request->validated());

        return $this->success($item->fresh(), 'Periode bansos berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $item = BansosPeriod::findOrFail($id);

        if ($item->calonPenerimas()->exists()) {
            return $this->error('Periode bansos masih direferensi oleh calon penerima.', 409, [
                'related' => ['Tidak dapat menghapus periode yang masih dipakai.'],
            ]);
        }

        $item->delete();

        return $this->success(null, 'Periode bansos berhasil dihapus.');
    }
}
