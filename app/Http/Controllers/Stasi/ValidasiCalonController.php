<?php

namespace App\Http\Controllers\Stasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\CalonPenerima;

class ValidasiCalonController extends Controller
{
    /**
     * Tampilkan daftar calon yang perlu divalidasi oleh stasi.
     */
    public function index(Request $request): View
    {
        $this->authorizeRole('stasi');

        $stasiId = Auth::user()->stasi_id;

        // ── Filter & Sort params ──────────────────────────────
        $search    = $request->input('search');
        $status    = $request->input('status', 'submitted_to_stasi'); // default lihat yang belum divalidasi
        $sort      = $request->input('sort', 'submitted_at');
        $direction = $request->input('direction', 'desc');
        $perPage   = (int) $request->input('per_page', 25);
        $lingkungan_id = $request->input('lingkungan_id');

        $allowedSorts = ['name', 'nik', 'monthly_income', 'status', 'submitted_at', 'created_at'];
        if (!in_array($sort, $allowedSorts)) $sort = 'submitted_at';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';
        if (!in_array($perPage, [10, 25, 50, 100])) $perPage = 25;

        // ── Build query ───────────────────────────────────────
        $query = CalonPenerima::where('stasi_id', $stasiId)
            ->with(['lingkungan:id,name', 'periodeBantuan:id,name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik',  'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($lingkungan_id) {
            $query->where('lingkungan_id', $lingkungan_id);
        }

        $calons = $query->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        // ── Status counts for tab badges ─────────────────────
        $allStatuses   = ['submitted_to_stasi', 'revision_requested', 'approved_by_stasi', 'rejected', 'sent_to_paroki'];

        $statusCountsQuery = CalonPenerima::where('stasi_id', $stasiId);

        if ($lingkungan_id) {
            $statusCountsQuery->where('lingkungan_id', $lingkungan_id);
        }

        $statusCounts = $statusCountsQuery->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAll = $statusCounts->sum();

        $lingkungans = \App\Models\Lingkungan::where('stasi_id', $stasiId)->orderBy('name')->get();

        return view('stasi.calons.index', compact(
            'calons', 'allStatuses', 'statusCounts', 'totalAll',
            'search', 'status', 'sort', 'direction', 'perPage', 'lingkungan_id', 'lingkungans'
        ));
    }

    /**
     * Tampilkan halaman detail untuk proses verifikasi.
     */
    public function show(CalonPenerima $calonPenerima): View
    {
        $this->authorizeRole('stasi');

        if ($calonPenerima->stasi_id !== Auth::user()->stasi_id) {
            abort(403, 'Unauthorized action.');
        }

        $calonPenerima->load([
            'periodeBantuan', 'paroki', 'stasi', 'lingkungan', 'creator',
            'validasiLogs' => fn ($q) => $q->latest()->with('actor:id,name'),
        ]);

        return view('stasi.calons.show', compact('calonPenerima'));
    }

    /**
     * Proses validasi batch (Setuju / Minta Revisi / Tolak).
     */
    public function processBatch(Request $request): RedirectResponse
    {
        $this->authorizeRole('stasi');

        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:calon_penerimas,id',
            'action' => 'required|in:approve,revision,reject,send_to_paroki',
            'note'   => 'nullable|string|max:1000',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');
        $note = $request->input('note');
        
        $user = Auth::user();

        // Ambil data yang valid (milik stasi ini)
        $calons = CalonPenerima::where('stasi_id', $user->stasi_id)
            ->whereIn('id', $ids)
            ->get();

        if ($calons->isEmpty()) {
            return back()->with('error', 'Data tidak valid.');
        }

        $now = now();
        $count = 0;

        foreach ($calons as $calon) {
            // Cek logic status
            if ($action === 'approve') {
                if ($calon->status !== 'submitted_to_stasi') continue;
                $statusUpdate = 'approved_by_stasi';
                $logAction    = 'approved';
            } 
            elseif ($action === 'revision') {
                if ($calon->status !== 'submitted_to_stasi') continue;
                $statusUpdate = 'revision_requested';
                $logAction    = 'revision_requested';
            } 
            elseif ($action === 'reject') {
                if (!in_array($calon->status, ['submitted_to_stasi', 'approved_by_stasi'])) continue;
                $statusUpdate = 'rejected';
                $logAction    = 'rejected';
            }
            elseif ($action === 'send_to_paroki') {
                // Hanya yang sudah diapprove stasi yang bisa dikirim ke paroki
                if ($calon->status !== 'approved_by_stasi') continue;
                $statusUpdate = 'sent_to_paroki';
                $logAction    = 'sent_to_paroki';
            }
            else {
                continue;
            }

            // Update record
            $updateData = [
                'status' => $statusUpdate,
                'stasi_validation_note' => $note,
                'validated_by' => $user->id,
            ];

            if ($action === 'approve' || $action === 'reject') {
                $updateData['validated_at'] = $now;
            } elseif ($action === 'send_to_paroki') {
                $updateData['sent_to_paroki_at'] = $now;
                $updateData['sent_by'] = $user->id;
            }

            $calon->update($updateData);

            // Log activity
            $calon->validasiLogs()->create([
                'action'   => $logAction,
                'actor_id' => $user->id,
                'notes'    => $note,
            ]);

            $count++;
        }

        if ($count === 0) {
            return back()->with('error', 'Tidak ada data yang berhasil diproses. Pastikan status calon sesuai dengan aksi yang dipilih.');
        }

        $actionMap = [
            'approve' => 'disetujui',
            'revision' => 'dikembalikan untuk revisi',
            'reject' => 'ditolak',
            'send_to_paroki' => 'dikirim ke paroki',
        ];

        return back()->with('success', "Berhasil memproses {$count} data (Status: {$actionMap[$action]}).");
    }

    /**
     * Cetak Surat Permohonan ke Paroki
     */
    public function cetakSuratPermohonan(Request $request): View
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();
        
        $stasi = $user->stasi;
        $paroki = $user->paroki ?? ($stasi ? $stasi->paroki : null);
        
        // Ambil periode berjalan dari pengajuan terawal yang sent_to_paroki
        // Atau default pakai string kosong. Dalam aplikasi sebenarnya bisa via Active Period
        $periode = null;

        $calonCount = CalonPenerima::where('stasi_id', $user->stasi_id)
            ->where('status', 'sent_to_paroki')
            ->count();

        return view('stasi.surat.cetak', compact('stasi', 'paroki', 'calonCount'));
    }

    protected function authorizeRole(string $role)
    {
        if (!Auth::check()) {
            abort(redirect()->route('login'));
        }

        $user = Auth::user();
        if (!isset($user->role) || $user->role->name !== $role) {
            abort(403, 'Unauthorized action.');
        }
    }
}
