<?php

namespace App\Http\Controllers\KetuaLingkunganStasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\CalonPenerima;
use App\Models\PeriodeBantuan;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class CalonPenerimaController extends Controller
{
    /**
     * Show a listing of the resource for ketua lingkungan stasi.
     */
    public function index(Request $request): View
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        $lingkunganId = Auth::user()->lingkungan_id;

        // ── Filter & Sort params ──────────────────────────────
        $search    = $request->input('search');
        $status    = $request->input('status');
        $sort      = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $perPage   = (int) $request->input('per_page', 10);

        $allowedSorts = ['name', 'nik', 'monthly_income', 'dependents_count', 'status', 'created_at'];
        if (!in_array($sort, $allowedSorts)) $sort = 'created_at';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';
        if (!in_array($perPage, [10, 25, 50])) $perPage = 10;

        // ── Build query ───────────────────────────────────────
        $query = CalonPenerima::where('lingkungan_id', $lingkunganId)
            ->with(['periodeBantuan:id,name,starts_at']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik',  'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $calons = $query->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        // ── Status counts for tab badges ─────────────────────
        $allStatuses   = ['draft', 'submitted_to_stasi', 'revision_requested',
                          'approved_by_stasi', 'sent_to_paroki', 'rejected'];

        $statusCounts  = CalonPenerima::where('lingkungan_id', $lingkunganId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAll = $statusCounts->sum();

        return view('ketua-lingkungan-stasi.calons.index', compact(
            'calons', 'allStatuses', 'statusCounts', 'totalAll',
            'search', 'status', 'sort', 'direction', 'perPage'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        $user = Auth::user();

        // Ambil periode bantuan aktif (status open/draft) untuk paroki user
        $periodeBantuans = PeriodeBantuan::where('paroki_id', $user->paroki_id)
            ->whereIn('status', ['open', 'draft'])
            ->orderByDesc('starts_at')
            ->get();

        return view('ketua-lingkungan-stasi.calons.create', compact('periodeBantuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        $user = Auth::user();

        $validated = $request->validate([
            'periode_bantuan_id'      => 'required|exists:periode_bantuans,id',
            'name'                    => 'required|string|max:255',
            'nik'                     => [
                'required', 'string', 'max:32',
                Rule::unique('calon_penerimas')->where(fn ($query) => $query->where('periode_bantuan_id', $request->periode_bantuan_id))
            ],
            'nomor_kk'                => [
                'required', 'string', 'max:32',
                Rule::unique('calon_penerimas')->where(fn ($query) => $query->where('periode_bantuan_id', $request->periode_bantuan_id))
            ],
            'family_head_name'        => 'nullable|string|max:255',
            'place_of_birth'          => 'nullable|string|max:255',
            'date_of_birth'           => 'nullable|date',
            'gender'                  => 'nullable|in:laki_laki,perempuan',
            'address'                 => 'required|string',
            'phone'                   => 'nullable|string|max:20',
            'occupation'              => 'nullable|string|max:255',
            'monthly_income'          => 'required|numeric|min:0',
            'dependents_count'        => 'required|integer|min:0|max:20',
            'housing_status'          => 'required|in:milik_sendiri,kontrak,menumpang,tidak_tetap',
            'housing_status_score'    => 'required|integer|in:1,2,3,4',
            'has_disability'          => 'sometimes|boolean',
            'disability_score'        => 'required|integer|in:1,2',
            'disability_note'         => 'nullable|string|max:500',
            'urgency_note'            => 'nullable|string|max:1000',
            'economic_condition_note' => 'nullable|string|max:1000',
        ]);

        // Auto-fill relasi dari user yang sedang login
        $validated['paroki_id']      = $user->paroki_id;
        $validated['stasi_id']       = $user->stasi_id;
        $validated['lingkungan_id']  = $user->lingkungan_id;
        $validated['created_by']     = $user->id;
        $validated['status']         = 'draft';
        $validated['has_disability'] = $request->boolean('has_disability');

        CalonPenerima::create($validated);

        return redirect()->route('ketua-lingkungan-stasi.calons.index')
            ->with('success', 'Calon penerima berhasil ditambahkan.');
    }


    /**
     * Display the specified resource.
     */
    public function show(CalonPenerima $calonPenerima): View
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        // Check if the calon belongs to the user's lingkungan
        if ($calonPenerima->lingkungan_id !== Auth::user()->lingkungan_id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load semua relasi untuk halaman detail
        $calonPenerima->load([
            'periodeBantuan',
            'paroki',
            'stasi',
            'lingkungan',
            'creator',
            'sawResult',
            'validasiLogs' => fn ($q) => $q->latest()->with('actor:id,name'),
        ]);

        return view('ketua-lingkungan-stasi.calons.show', compact('calonPenerima'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalonPenerima $calonPenerima): View
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        // Check if the calon belongs to the user's lingkungan
        if ($calonPenerima->lingkungan_id !== Auth::user()->lingkungan_id) {
            abort(403, 'Unauthorized action.');
        }

        // Hanya bisa diedit jika status draft atau revision_requested
        if (!in_array($calonPenerima->status, ['draft', 'revision_requested'])) {
            return redirect()->route('ketua-lingkungan-stasi.calons.show', $calonPenerima)
                ->with('error', 'Data tidak dapat diedit pada status saat ini.');
        }

        $user = Auth::user();

        // Periode bantuan aktif untuk dropdown
        $periodeBantuans = PeriodeBantuan::where('paroki_id', $user->paroki_id)
            ->whereIn('status', ['open', 'draft'])
            ->orderByDesc('starts_at')
            ->get();

        $calonPenerima->load(['periodeBantuan']);

        return view('ketua-lingkungan-stasi.calons.edit', compact('calonPenerima', 'periodeBantuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CalonPenerima $calonPenerima): RedirectResponse
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        // Check ownership
        if ($calonPenerima->lingkungan_id !== Auth::user()->lingkungan_id) {
            abort(403, 'Unauthorized action.');
        }

        // Hanya bisa diupdate jika draft atau revision_requested
        if (!in_array($calonPenerima->status, ['draft', 'revision_requested'])) {
            abort(403, 'Data tidak dapat diubah pada status saat ini.');
        }

        $validated = $request->validate([
            'periode_bantuan_id'      => 'required|exists:periode_bantuans,id',
            'name'                    => 'required|string|max:255',
            'nik'                     => [
                'required', 'string', 'max:32',
                Rule::unique('calon_penerimas')
                    ->where(fn ($query) => $query->where('periode_bantuan_id', $request->periode_bantuan_id))
                    ->ignore($calonPenerima->id)
            ],
            'nomor_kk'                => [
                'required', 'string', 'max:32',
                Rule::unique('calon_penerimas')
                    ->where(fn ($query) => $query->where('periode_bantuan_id', $request->periode_bantuan_id))
                    ->ignore($calonPenerima->id)
            ],
            'family_head_name'        => 'nullable|string|max:255',
            'place_of_birth'          => 'nullable|string|max:255',
            'date_of_birth'           => 'nullable|date',
            'gender'                  => 'nullable|in:laki_laki,perempuan',
            'address'                 => 'required|string',
            'phone'                   => 'nullable|string|max:20',
            'occupation'              => 'nullable|string|max:255',
            'monthly_income'          => 'required|numeric|min:0',
            'dependents_count'        => 'required|integer|min:0|max:20',
            'housing_status'          => 'required|in:milik_sendiri,kontrak,menumpang,tidak_tetap',
            'housing_status_score'    => 'required|integer|in:1,2,3,4',
            'has_disability'          => 'sometimes|boolean',
            'disability_score'        => 'required|integer|in:1,2',
            'disability_note'         => 'nullable|string|max:500',
            'urgency_note'            => 'nullable|string|max:1000',
            'economic_condition_note' => 'nullable|string|max:1000',
        ]);

        $validated['has_disability'] = $request->boolean('has_disability');

        $calonPenerima->update($validated);

        return redirect()->route('ketua-lingkungan-stasi.calons.show', $calonPenerima)
            ->with('success', 'Data calon penerima berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalonPenerima $calonPenerima): RedirectResponse
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        if ($calonPenerima->lingkungan_id !== Auth::user()->lingkungan_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($calonPenerima->status !== 'draft') {
            return back()->with('error', 'Hanya data berstatus Draft yang dapat dihapus.');
        }

        $name = $calonPenerima->name;
        $calonPenerima->delete();

        return redirect()->route('ketua-lingkungan-stasi.calons.index')
            ->with('success', "Data {$name} berhasil dihapus.");
    }

    /**
     * Submit the calon to stasi.
     */
    public function submitToStasi(CalonPenerima $calonPenerima): RedirectResponse
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        // Check if the calon belongs to the user's lingkungan
        if ($calonPenerima->lingkungan_id !== Auth::user()->lingkungan_id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($calonPenerima->status, ['draft', 'revision_requested'])) {
            return back()->with('error', 'Hanya data Draft atau Revisi yang dapat diajukan.');
        }

        $calonPenerima->update([
            'status' => 'submitted_to_stasi',
            'submitted_at' => now(),
        ]);

        return redirect()->route('ketua-lingkungan-stasi.calons.index')
            ->with('status', 'Calon penerima berhasil diajukan ke stasi.');
    }

    /**
     * Submit multiple calons to stasi.
     */
    public function submitBulk(Request $request): RedirectResponse
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:calon_penerimas,id',
        ]);

        $lingkunganId = Auth::user()->lingkungan_id;

        $calons = CalonPenerima::whereIn('id', $validated['ids'])
            ->where('lingkungan_id', $lingkunganId)
            ->whereIn('status', ['draft', 'revision_requested'])
            ->get();

        if ($calons->isEmpty()) {
            return back()->with('error', 'Tidak ada data valid yang dapat diajukan.');
        }

        foreach ($calons as $calon) {
            $calon->update([
                'status' => 'submitted_to_stasi',
                'submitted_at' => now(),
            ]);
        }

        $count = $calons->count();
        return redirect()->route('ketua-lingkungan-stasi.calons.index')
            ->with('success', "Berhasil mengajukan {$count} calon ke Stasi.");
    }

    /**
     * Authorize the user to have the given role.
     *
     * @param string $role
     * @return void
     */
    protected function authorizeRole(string $role)
    {
        if (!Auth::check()) {
            // If not authenticated, redirect to login
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!isset($user->role) || $user->role->name !== $role) {
            // If not authorized, redirect to home or show error
            abort(403, 'Unauthorized action.');
        }
    }
}
