@extends('layouts.app')

@section('title', 'Detail Surat Pengantar')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   DETAIL SURAT PENGANTAR — Premium Modern UI
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

.page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--gray-500); font-weight: 600; font-size: 0.9rem; text-decoration: none; margin-bottom: 0.75rem; transition: var(--transition); }
.back-link:hover { color: var(--primary); transform: translateX(-2px); }

.page-title { font-size: 1.8rem; font-weight: 800; color: var(--dark); letter-spacing: -0.02em; margin-bottom: 0.25rem; }
.page-subtitle { font-size: 0.95rem; color: var(--gray-500); }

.card { background: white; border: 1px solid var(--gray-100); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 2rem; }
.card-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--gray-100); display: flex; align-items: center; justify-content: space-between; background: var(--gray-50); }
.card-title { font-size: 1.15rem; font-weight: 700; color: var(--dark); margin: 0; display:flex; align-items:center; gap:0.5rem; }
.card-body { padding: 2rem; }

.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-label { font-size: 0.8rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.05em; }
.info-value { font-size: 0.95rem; font-weight: 600; color: var(--dark); }

.custom-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
.custom-table th { padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200); white-space: nowrap; }
.custom-table td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-100); vertical-align: middle; transition: background 0.15s; }
.custom-table tbody tr:hover td { background: var(--gray-50); }
.custom-table tbody tr:last-child td { border-bottom: none; }

.badge-status { display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:20px; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; }
.status-generated { background: #FEF3C7; color: #D97706; }
.status-sent { background: #ECFDF5; color: #059669; }
.status-cancelled { background: #FEE2E2; color: #DC2626; }

.btn-primary { background: var(--primary); color: white; border: none; padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06); }
.btn-primary:hover { background: #4338CA; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2), 0 4px 6px -2px rgba(79, 70, 229, 0.1); }

.btn-danger { background: white; border: 1px solid #FECACA; color: #DC2626; font-weight: 500; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: var(--radius-md); display: inline-flex; align-items: center; gap: 0.4rem; transition: var(--transition); text-decoration: none; box-shadow: var(--shadow-sm); cursor: pointer; }
.btn-danger:hover { background: #FEF2F2; border-color: #F87171; transform: translateY(-1px); }

</style>
@endpush

@section('content')

<a href="{{ route('stasi.surat-permohonan.index') }}" class="back-link anim-fade-up">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Kembali ke Daftar Surat
</a>

<div class="page-header anim-fade-up delay-1">
    <div>
        <h1 class="page-title">Detail Surat Pengantar</h1>
        <p class="page-subtitle">{{ $suratPermohonan->letter_number }}</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        @if($suratPermohonan->status === 'generated')
        <form action="{{ route('stasi.surat-permohonan.destroy', $suratPermohonan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan surat ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batalkan Surat
            </button>
        </form>
        @endif
        
        <a href="{{ route('stasi.surat-permohonan.print', $suratPermohonan->id) }}" target="_blank" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Surat / PDF
        </a>
    </div>
</div>

<div class="card anim-fade-up delay-2">
    <div class="card-header">
        <h2 class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Informasi Dokumen
        </h2>
        <span class="badge-status status-{{ $suratPermohonan->status }}">
            @if($suratPermohonan->status === 'generated')
                Dibuat
            @elseif($suratPermohonan->status === 'sent')
                Terkirim
            @elseif($suratPermohonan->status === 'cancelled')
                Dibatalkan
            @else
                {{ ucfirst($suratPermohonan->status) }}
            @endif
        </span>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nomor Surat</span>
                <span class="info-value">{{ $suratPermohonan->letter_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Periode Bantuan</span>
                <span class="info-value">{{ $suratPermohonan->periodeBantuan->name ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Dibuat</span>
                <span class="info-value">{{ $suratPermohonan->created_at->translatedFormat('d F Y, H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Jumlah Terlampir</span>
                <span class="info-value">{{ $suratPermohonan->total_candidates }} Keluarga (KK)</span>
            </div>
        </div>
    </div>
</div>

<div class="card anim-fade-up delay-3">
    <div class="card-header" style="background: white;">
        <h2 class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Daftar Calon Penerima Terlampir
        </h2>
    </div>
    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Calon (KK)</th>
                    <th>NIK / No. KK</th>
                    <th>Lingkungan</th>
                    <th>Status Calon</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratPermohonan->calonPenerimas as $index => $calon)
                <tr>
                    <td style="color: var(--gray-500); font-weight: 500;">{{ $index + 1 }}.</td>
                    <td>
                        <span style="font-weight: 600; color: var(--dark);">{{ $calon->name }}</span>
                    </td>
                    <td>
                        <span style="display:block; font-family: var(--font-mono); font-size: 0.85rem; color: var(--gray-700);">NIK: {{ $calon->nik }}</span>
                        <span style="display:block; font-family: var(--font-mono); font-size: 0.85rem; color: var(--gray-500);">KK: {{ $calon->nomor_kk }}</span>
                    </td>
                    <td style="color: var(--gray-700); font-weight: 500;">
                        {{ $calon->lingkungan->name ?? '-' }}
                    </td>
                    <td>
                        @if($calon->status === 'sent_to_paroki')
                            <span style="display:inline-flex;align-items:center;padding:0.25rem 0.6rem;background:#ECFDF5;color:#059669;border-radius:4px;font-size:0.75rem;font-weight:700;">Dikirim ke Paroki</span>
                        @elseif($calon->status === 'approved_by_stasi')
                            <span style="display:inline-flex;align-items:center;padding:0.25rem 0.6rem;background:#EEF2FF;color:#4F46E5;border-radius:4px;font-size:0.75rem;font-weight:700;">Disetujui Stasi</span>
                        @else
                            <span style="display:inline-flex;align-items:center;padding:0.25rem 0.6rem;background:var(--gray-100);color:var(--gray-700);border-radius:4px;font-size:0.75rem;font-weight:700;">{{ str_replace('_', ' ', strtoupper($calon->status)) }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                        Tidak ada calon yang terlampir.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
