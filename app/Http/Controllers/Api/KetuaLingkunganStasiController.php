<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalonPenerimaRequest;
use App\Http\Requests\UpdateCalonPenerimaRequest;
use App\Models\CalonPenerima;
use App\Models\LingkunganStasi;
use App\Services\BansosWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class KetuaLingkunganStasiController extends Controller
{
    use AuthorizesRequests;
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;

    public function __construct(BansosWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && ! $user->lingkungan_stasi_id) {
            return $this->error('User belum terhubung ke lingkungan stasi.', 422, [
                'lingkungan_stasi_id' => ['Lingkungan stasi wajib tersedia untuk role ini.'],
            ]);
        }

        $items = CalonPenerima::query()
            ->when($user->role !== 'super_admin', fn ($query) => $query->where('lingkungan_stasi_id', $user->lingkungan_stasi_id))
            ->latest()
            ->get();

        return $this->success($items, 'Daftar calon penerima berhasil diambil.');
    }

    public function store(StoreCalonPenerimaRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        if ($user->role === 'super_admin') {
            if (! $request->filled('stasi_id') || ! $request->filled('lingkungan_stasi_id')) {
                return $this->error('Super admin wajib memilih stasi dan lingkungan stasi.', 422, [
                    'stasi_id' => ['Stasi wajib diisi.'],
                    'lingkungan_stasi_id' => ['Lingkungan stasi wajib diisi.'],
                ]);
            }

            $data['stasi_id'] = $request->integer('stasi_id');
            $data['lingkungan_stasi_id'] = $request->integer('lingkungan_stasi_id');
        } else {
            if (! $user->stasi_id || ! $user->lingkungan_stasi_id) {
                return $this->error('User belum terhubung ke stasi dan lingkungan stasi.', 422, [
                    'stasi_id' => ['Stasi wajib tersedia untuk role ini.'],
                    'lingkungan_stasi_id' => ['Lingkungan stasi wajib tersedia untuk role ini.'],
                ]);
            }

            $data['lingkungan_stasi_id'] = $user->lingkungan_stasi_id;
            $data['stasi_id'] = $user->stasi_id;
        }

        $lingkunganSesuaiStasi = LingkunganStasi::whereKey($data['lingkungan_stasi_id'])
            ->where('stasi_id', $data['stasi_id'])
            ->exists();

        if (! $lingkunganSesuaiStasi) {
            return $this->error('Lingkungan stasi tidak sesuai dengan stasi yang dipilih.', 422, [
                'lingkungan_stasi_id' => ['Lingkungan stasi harus berada pada stasi yang sama.'],
            ]);
        }

        $calon = CalonPenerima::create($data);

        return $this->success($calon, 'Calon penerima berhasil dibuat.', 201);
    }

    public function update(UpdateCalonPenerimaRequest $request, $id)
    {
        $calon = CalonPenerima::findOrFail($id);
        $this->authorize('update', $calon);
        $data = $request->validated();

        $calon->update($data);

        return $this->success($calon->fresh(), 'Calon penerima berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $calon = CalonPenerima::findOrFail($id);
        $this->authorize('delete', $calon);
        $calon->delete();

        return $this->success(null, 'Calon penerima berhasil dihapus.');
    }

    public function submitToStasi(Request $request, $id)
    {
        $calon = CalonPenerima::findOrFail($id);
        $this->authorize('update', $calon);

        $user = $request->user();
        $ok = $this->workflow->submitToStasi((int) $id, $user->id);

        if (! $ok) {
            return $this->error('Calon penerima tidak dapat diajukan dari status saat ini.', 409);
        }

        return $this->success(['ok' => true], 'Calon penerima berhasil diajukan ke stasi.');
    }
}
