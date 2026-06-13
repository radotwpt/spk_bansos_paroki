<?php

namespace App\Http\Controllers\Stasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportExport;
use App\Models\CalonPenerima;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorizeRole('stasi');
        $reports = ReportExport::where('stasi_id', Auth::user()->stasi_id)
            ->where('type', 'surat_permohonan_pdf')
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('stasi.reports.index', compact('reports'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();

        $calonCount = CalonPenerima::where('stasi_id', $user->stasi_id)
            ->where('status', 'sent_to_paroki')
            ->count();

        $report = ReportExport::create([
            'stasi_id' => $user->stasi_id,
            'paroki_id' => $user->paroki_id ?? ($user->stasi ? $user->stasi->paroki_id : null),
            'generated_by' => $user->id,
            'type' => 'surat_permohonan_pdf',
            'title' => 'Surat Permohonan Bansos - ' . date('d M Y'),
            'filters' => ['calon_count' => $calonCount],
            'status' => 'completed',
            'generated_at' => now(),
        ]);

        // Using session flash data to let frontend open the new tab
        return back()->with('open_report', route('stasi.reports.show', $report->id));
    }

    public function show(ReportExport $report): View
    {
        $this->authorizeRole('stasi');
        $user = Auth::user();

        if ($report->stasi_id !== $user->stasi_id) {
            abort(403);
        }

        $stasi = $user->stasi;
        $paroki = $user->paroki ?? ($stasi ? $stasi->paroki : null);
        $calonCount = $report->filters['calon_count'] ?? 0;
        $tanggalSurat = $report->generated_at ?? $report->created_at;

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
