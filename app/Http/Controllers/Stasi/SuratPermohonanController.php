<?php

namespace App\Http\Controllers\Stasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SuratPermohonan;
use App\Models\CalonPenerima;
use App\Models\PeriodeBantuan;
use App\Models\ValidasiLog;

class SuratPermohonanController extends Controller
{
    public function index(): View
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();
        
        $suratPermohonans = SuratPermohonan::where('stasi_id', $user->stasi_id)
            ->with('periodeBantuan')
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('stasi.surat-permohonan.index', compact('suratPermohonans'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();

        $periodeAktif = PeriodeBantuan::where('status', 'open')->first();
        if (!$periodeAktif) {
            return back()->with('error', 'Tidak ada Periode Bantuan yang aktif. Tidak dapat membuat surat pengantar.');
        }

        $calonDisetujui = CalonPenerima::where('stasi_id', $user->stasi_id)
            ->where('periode_bantuan_id', $periodeAktif->id)
            ->where('status', 'approved_by_stasi')
            ->get();

        if ($calonDisetujui->isEmpty()) {
            return back()->with('error', 'Tidak ada calon penerima dengan status "Disetujui" yang bisa dikirim ke Paroki.');
        }

        DB::beginTransaction();
        try {
            $letterNumber = sprintf('SP-STASI%d-%s-%04d', $user->stasi_id, date('Ym'), rand(1000, 9999));

            $surat = SuratPermohonan::create([
                'periode_bantuan_id' => $periodeAktif->id,
                'paroki_id' => $user->stasi->paroki_id ?? ($user->paroki_id ?? 1),
                'stasi_id' => $user->stasi_id,
                'generated_by' => $user->id,
                'letter_number' => $letterNumber,
                'subject' => 'Permohonan Bantuan Sosial Periode ' . $periodeAktif->name,
                'total_candidates' => $calonDisetujui->count(),
                'status' => 'generated',
                'generated_at' => now(),
            ]);

            $calonIds = $calonDisetujui->pluck('id')->toArray();
            
            $surat->calonPenerimas()->attach($calonIds);

            foreach ($calonDisetujui as $calon) {
                $oldStatus = $calon->status;
                $calon->status = 'sent_to_paroki';
                $calon->sent_to_paroki_at = now();
                $calon->save();

                ValidasiLog::create([
                    'calon_penerima_id' => $calon->id,
                    'actor_id' => $user->id,
                    'action' => 'Dikirim ke Paroki via Surat Pengantar',
                    'from_status' => $oldStatus,
                    'to_status' => 'sent_to_paroki',
                    'notes' => 'Dikirim menggunakan surat pengantar No. ' . $letterNumber,
                ]);
            }

            DB::commit();

            return redirect()->route('stasi.surat-permohonan.show', $surat->id)->with('success', 'Surat Pengantar berhasil dibuat dan calon terkait telah diubah statusnya menjadi Dikirim ke Paroki.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat surat pengantar: ' . $e->getMessage());
        }
    }

    public function show(SuratPermohonan $suratPermohonan): View
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();

        if ($suratPermohonan->stasi_id !== $user->stasi_id) {
            abort(403);
        }

        $suratPermohonan->load('calonPenerimas.lingkungan');

        return view('stasi.surat-permohonan.show', compact('suratPermohonan'));
    }

    public function destroy(SuratPermohonan $suratPermohonan)
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();

        if ($suratPermohonan->stasi_id !== $user->stasi_id) {
            abort(403);
        }

        if ($suratPermohonan->status !== 'generated') {
            return back()->with('error', 'Hanya surat pengantar yang berstatus baru (generated) yang bisa dibatalkan.');
        }

        DB::beginTransaction();
        try {
            $calons = $suratPermohonan->calonPenerimas;

            foreach ($calons as $calon) {
                if ($calon->status === 'sent_to_paroki') {
                    $oldStatus = $calon->status;
                    $calon->status = 'approved_by_stasi';
                    $calon->sent_to_paroki_at = null;
                    $calon->save();

                    ValidasiLog::create([
                        'calon_penerima_id' => $calon->id,
                        'actor_id' => $user->id,
                        'action' => 'Pembatalan Surat Pengantar',
                        'from_status' => $oldStatus,
                        'to_status' => 'approved_by_stasi',
                        'notes' => 'Surat Pengantar No. ' . $suratPermohonan->letter_number . ' dibatalkan. Status dikembalikan ke Disetujui Stasi.',
                    ]);
                }
            }

            $suratPermohonan->delete();

            DB::commit();
            return redirect()->route('stasi.surat-permohonan.index')->with('success', 'Surat pengantar berhasil dibatalkan dan status calon terkait telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membatalkan surat: ' . $e->getMessage());
        }
    }

    public function print(SuratPermohonan $suratPermohonan): View
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();

        if ($suratPermohonan->stasi_id !== $user->stasi_id) {
            abort(403);
        }

        $stasi = $user->stasi;
        $paroki = $user->paroki ?? ($stasi ? $stasi->paroki : null);
        $calonCount = $suratPermohonan->total_candidates;
        $tanggalSurat = $suratPermohonan->generated_at ?? $suratPermohonan->created_at;
        $report = $suratPermohonan;

        return view('stasi.surat.cetak', compact('stasi', 'paroki', 'calonCount', 'tanggalSurat', 'report'));
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
