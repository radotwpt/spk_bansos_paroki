@extends('layouts.app')

@section('title', 'Riwayat Surat Pengantar')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   REPORTS STASI — Premium Modern UI
   ═══════════════════════════════════════════════════════════ */

:root {
    --primary: #4F46E5;
    --primary-light: #EEF2FF;
    --secondary: #10B981;
    --dark: #0F172A;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --gray-700: #334155;
    --radius-lg: 16px;
    --radius-md: 10px;
    --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05);
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --transition: all 0.2s ease;
}

.page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; }
.page-title { font-size: 1.8rem; font-weight: 800; color: var(--dark); letter-spacing: -0.02em; margin-bottom: 0.5rem; }
.page-subtitle { font-size: 0.95rem; color: var(--gray-500); }

.btn-primary { background: var(--primary); color: white; border: none; padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06); }
.btn-primary:hover { background: #4338CA; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2), 0 4px 6px -2px rgba(79, 70, 229, 0.1); }

.table-card { background: white; border: 1px solid var(--gray-100); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 2rem; }

.custom-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
.custom-table th { padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200); white-space: nowrap; }
.custom-table td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-100); vertical-align: middle; transition: background 0.15s; }
.custom-table tbody tr:hover td { background: var(--gray-50); }
.custom-table tbody tr:last-child td { border-bottom: none; }

.btn-modern { background: white; border: 1px solid var(--gray-200); color: var(--gray-600); font-weight: 500; font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.4rem; transition: var(--transition); text-decoration: none; box-shadow: var(--shadow-sm); }
.btn-modern:hover { background: var(--gray-50); color: var(--primary); border-color: var(--gray-300); transform: translateY(-1px); }

.empty-state { padding: 4rem 2rem; text-align: center; }
.empty-icon { width: 64px; height: 64px; background: var(--gray-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--gray-400); }
.empty-title { font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem; }
.empty-desc { font-size: 0.95rem; color: var(--gray-500); }

/* Custom Pagination wrapper */
.custom-pagination { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-100); }
</style>
@endpush

@section('content')

<div class="page-header anim-fade-up">
    <div>
        <h1 class="page-title">Riwayat Surat Pengantar</h1>
        <p class="page-subtitle">Daftar surat pengantar ke Paroki yang pernah Anda buat (di-generate).</p>
    </div>
    <form action="{{ route('stasi.reports.store') }}" method="POST" target="_blank">
        @csrf
        <button type="submit" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Buat Surat Pengantar Baru
        </button>
    </form>
</div>

<div class="table-card anim-fade-up delay-1">
    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Judul Dokumen</th>
                    <th>Jumlah Calon Tercakup</th>
                    <th>Waktu Dibuat</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <span style="font-weight: 600; color: var(--dark);">{{ $report->title }}</span>
                        </div>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;padding:0.25rem 0.75rem;background:var(--gray-100);border-radius:20px;font-size:0.85rem;font-weight:600;color:var(--gray-600);">
                            {{ $report->filters['calon_count'] ?? 0 }} KK Terlampir
                        </span>
                    </td>
                    <td style="color: var(--gray-600); font-size: 0.9rem;">
                        {{ $report->created_at->translatedFormat('d F Y, H:i') }}
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('stasi.reports.show', $report->id) }}" target="_blank" class="btn-modern">
                            Lihat / Cetak
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </div>
                            <h3 class="empty-title">Belum Ada Riwayat Surat</h3>
                            <p class="empty-desc">Anda belum pernah membuat (mencetak) surat pengantar ke Paroki.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
    <div class="custom-pagination">
        {{ $reports->links('vendor.pagination.tailwind') }}
    </div>
    @endif
</div>

@if(session('open_report'))
<script>
    // Buka otomatis tab baru setelah form submit create surat
    window.open("{{ session('open_report') }}", "_blank");
</script>
@endif
@endsection
