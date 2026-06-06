<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBansosPeriodRequest;
use App\Http\Requests\Master\UpdateBansosPeriodRequest;
use App\Http\Resources\BansosPeriodResource;
use App\Models\BansosPeriod;
use Illuminate\Http\Request;

class BansosPeriodController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $this->authorize('manage-master-data');

        $q = trim((string) $request->query('q', ''));
        $perPage = max(5, min(100, (int) $request->query('per_page', 15)));
        $sort = (string) $request->query('sort', 'tahun');
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['nama_periode', 'tahun', 'status_periode', 'created_at', 'updated_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'tahun';

        $query = BansosPeriod::query();

        if ($request->filled('status_periode')) {
            $query->where('status_periode', $request->query('status_periode'));
        }

        if (! empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_periode', 'like', "%{$q}%")
                    ->orWhere('tahun', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return $this->paginated($items, BansosPeriodResource::collection($items->items()), 'Daftar periode bansos diambil.');
    }

    public function store(StoreBansosPeriodRequest $request)
    {
        $this->authorize('manage-master-data');

        $item = BansosPeriod::create($request->validated());

        return $this->success(new BansosPeriodResource($item), 'Periode bansos berhasil dibuat.', 201);
    }

    public function show($id)
    {
        $this->authorize('manage-master-data');

        $item = BansosPeriod::findOrFail($id);

        return $this->success(new BansosPeriodResource($item), 'Detail periode bansos diambil.');
    }

    public function update(UpdateBansosPeriodRequest $request, $id)
    {
        $this->authorize('manage-master-data');

        $item = BansosPeriod::findOrFail($id);
        $item->update($request->validated());

        return $this->success(new BansosPeriodResource($item->fresh()), 'Periode bansos berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage-master-data');

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
