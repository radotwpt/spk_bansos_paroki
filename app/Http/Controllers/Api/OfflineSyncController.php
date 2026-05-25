<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Services\BansosWorkflowService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfflineSyncController extends Controller
{
    use RespondsWithApi;

    protected BansosWorkflowService $workflow;

    protected DocumentService $docs;

    public function __construct(BansosWorkflowService $workflow, DocumentService $docs)
    {
        $this->workflow = $workflow;
        $this->docs = $docs;
    }

    public function sync(Request $request)
    {
        $data = $request->all();
        // expected: { action: 'submit_candidate', payload: {...} }
        $action = $data['action'] ?? null;
        $payload = $data['payload'] ?? [];

        switch ($action) {
            case 'submit_candidate':
                $payload = $this->normalizeCandidatePayload($payload, $request);

                $v = Validator::make($payload, [
                    'bansos_period_id' => 'required|exists:bansos_periods,id',
                    'nik' => 'required|string|max:16',
                    'nama_lengkap' => 'required|string|max:150',
                    'alamat_kristen' => 'nullable|string',
                    'pendapatan_keluarga' => 'required|numeric|min:0',
                    'jumlah_tanggungan' => 'required|integer|min:0',
                    'status_tempat_tinggal' => 'required|in:milik_sendiri,sewa,numpang',
                    'status_hubungan' => 'required|in:lajang,menikah,cerai',
                    'urgensi_tambahan_tekstual' => 'nullable|string',
                    'lingkungan_stasi_id' => 'required|exists:lingkungan_stasis,id',
                    'stasi_id' => 'required|exists:stasis,id',
                ]);

                if ($v->fails()) {
                    return $this->error('Payload sync tidak valid.', 422, $v->errors());
                }

                try {
                    $calon = CalonPenerima::create(array_merge($v->validated(), [
                        'status_alur' => 'draft',
                    ]));

                    return $this->success([
                        'ok' => true,
                        'id' => $calon->id,
                    ], 'Data offline berhasil disinkronkan.', 201);
                } catch (\Throwable $e) {
                    return $this->error('Data offline gagal disinkronkan.', 500, [
                        'sync' => [$e->getMessage()],
                    ]);
                }

            default:
                return $this->error('Action sync tidak dikenal.', 400, [
                    'action' => ['Action tidak didukung.'],
                ]);
        }
    }

    private function normalizeCandidatePayload(array $payload, Request $request): array
    {
        $user = $request->user();

        $payload['nama_lengkap'] = $payload['nama_lengkap'] ?? $payload['nama'] ?? null;
        $payload['alamat_kristen'] = $payload['alamat_kristen'] ?? $payload['alamat'] ?? null;
        $payload['jumlah_tanggungan'] = $payload['jumlah_tanggungan'] ?? 0;
        $payload['status_tempat_tinggal'] = $payload['status_tempat_tinggal'] ?? 'milik_sendiri';
        $payload['status_hubungan'] = $payload['status_hubungan'] ?? 'lajang';

        if ($user?->role !== 'super_admin') {
            $payload['lingkungan_stasi_id'] = $user?->lingkungan_stasi_id;
            $payload['stasi_id'] = $user?->stasi_id;
        } else {
            $payload['lingkungan_stasi_id'] = $payload['lingkungan_stasi_id'] ?? null;
            $payload['stasi_id'] = $payload['stasi_id'] ?? null;
        }

        if (empty($payload['bansos_period_id'])) {
            $payload['bansos_period_id'] = BansosPeriod::where('status_periode', 'aktif')->value('id');
        }

        return $payload;
    }
}
