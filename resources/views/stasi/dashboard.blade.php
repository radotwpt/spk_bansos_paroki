@extends('layouts.app')

@section('title', 'Dashboard Stasi')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   DASHBOARD STASI — Premium Modern UI
   ═══════════════════════════════════════════════════════════ */

:root {
    --primary: #4F46E5; /* Indigo 600 */
    --primary-light: #EEF2FF; /* Indigo 50 */
    --secondary: #10B981; /* Emerald 500 */
    --warning: #F59E0B; /* Amber 500 */
    --danger: #EF4444; /* Red 500 */
    --dark: #1E293B; /* Slate 800 */
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --bg-color: #F8FAFC;
    --radius-lg: 20px;
    --radius-md: 14px;
    --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.08);
    --shadow-hover: 0 20px 40px -10px rgba(79,70,229,0.15);
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: 1px solid rgba(255, 255, 255, 0.5);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background-color: var(--bg-color);
}

/* ── Hero Glassmorphism ──────────────────────────────────── */
.hero-stasi {
    background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
    box-shadow: 0 15px 30px -10px rgba(79, 70, 229, 0.3);
    color: white;
}

.hero-stasi::before {
    content: ''; 
    position: absolute; 
    top: -50%; right: -20%;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.hero-stasi::after {
    content: '';
    position: absolute;
    bottom: -30%; left: -10%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.hero-stasi-content { position: relative; z-index: 1; }

.hero-stasi h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem; }
.hero-stasi p { font-size: 1rem; color: rgba(255,255,255,0.9); font-weight: 400; line-height: 1.6; max-width: 600px; }

/* ── Metrics Grid ────────────────────────────────────────── */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-soft);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    transition: var(--transition);
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.metric-icon-wrap {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1rem;
}

.mc-blue .metric-icon-wrap { background: var(--primary-light); color: var(--primary); }
.mc-yellow .metric-icon-wrap { background: #FEF3C7; color: var(--warning); }
.mc-lime .metric-icon-wrap { background: #D1FAE5; color: var(--secondary); }
.mc-orange .metric-icon-wrap { background: #FFEDD5; color: #F97316; }

.metric-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-500);
    margin-bottom: 0.25rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
    line-height: 1.2;
}

/* ── Dashboard Cards ─────────────────────────────────────── */
.dash-card {
    background: white;
    border: 1px solid var(--gray-100);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    box-shadow: var(--shadow-soft);
    margin-bottom: 2rem;
    transition: var(--transition);
}

.dash-card:hover {
    box-shadow: 0 15px 35px -5px rgba(0,0,0,0.05);
}

.dash-card-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-100);
}

.dash-card-header .title-group {
    display: flex; align-items: center; gap: 0.75rem;
}

.dash-card-header .icon-bg {
    width: 36px; height: 36px;
    background: var(--gray-50);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: var(--gray-600);
}

.dash-card-header h3 { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin: 0; }

/* ── Pipeline Chart ──────────────────────────────────────── */
.pipeline-bar {
    height: 14px;
    background: var(--gray-100);
    border-radius: 20px;
    display: flex;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.p-segment { height: 100%; transition: width 1s ease-in-out; }
.p-sub { background: var(--warning); }
.p-app { background: var(--secondary); }
.p-rej { background: var(--danger); }

.pipeline-legend {
    display: flex; gap: 1.5rem; flex-wrap: wrap; 
}

.leg-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; color: var(--gray-600); }
.leg-dot { width: 10px; height: 10px; border-radius: 50%; }

/* ── Recent Table ────────────────────────────────────────── */
.recent-table-wrap {
    overflow-x: auto;
    margin: 0 -1.75rem;
    padding: 0 1.75rem;
}

.recent-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.recent-table th { 
    background: var(--gray-50); 
    padding: 0.85rem 1rem; 
    text-align: left; 
    font-size: 0.75rem; 
    font-weight: 600; 
    text-transform: uppercase; 
    letter-spacing: 0.05em; 
    color: var(--gray-500); 
    border-y: 1px solid var(--gray-200); 
}
.recent-table th:first-child { border-left: 1px solid var(--gray-200); border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
.recent-table th:last-child { border-right: 1px solid var(--gray-200); border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

.recent-table td { 
    padding: 1rem; 
    border-bottom: 1px solid var(--gray-100); 
    vertical-align: middle; 
}
.recent-table tr:hover td { background: var(--gray-50); }
.recent-table tr:last-child td { border-bottom: none; }

.rt-name { font-weight: 600; color: var(--dark); font-size: 0.95rem; margin-bottom: 0.2rem; }
.rt-ling { font-size: 0.8rem; color: var(--gray-500); display: flex; align-items: center; gap: 4px; }

.btn-modern {
    background: white;
    border: 1px solid var(--gray-200);
    color: var(--gray-600);
    font-weight: 500;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: inline-flex; align-items: center; gap: 0.4rem;
    transition: var(--transition);
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-modern:hover {
    background: var(--gray-50);
    color: var(--primary);
    border-color: var(--gray-300);
    transform: translateY(-1px);
}

/* ── Bar Chart ───────────────────────────────────────────── */
.bc-item { margin-bottom: 1.25rem; }
.bc-item:last-child { margin-bottom: 0; }
.bc-info { display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark); }
.bc-info span:last-child { color: var(--gray-500); font-size: 0.8rem; }
.bc-track { 
    height: 8px; 
    background: var(--gray-100); 
    border-radius: 4px; 
    overflow: hidden; 
}
.bc-fill { 
    height: 100%; 
    border-radius: 4px;
    transition: width 1s ease-out;
}

</style>
@endpush

@section('content')

{{-- ══ HERO ════════════════════════════════════════════════ --}}
<div class="hero-stasi anim-fade-up">
    <div class="hero-stasi-content">
        <h1>Dashboard Validasi Stasi</h1>
        <p>Wilayah <strong>{{ Auth::user()->stasi?->name ?? '—' }}</strong>. Tinjau, validasi, dan kelola usulan calon penerima bantuan sosial dari seluruh lingkungan Anda dengan mudah dan cepat.</p>
    </div>
</div>

{{-- ══ METRICS ═════════════════════════════════════════════ --}}
<div class="metrics-grid anim-fade-up delay-1">
    <div class="metric-card mc-blue">
        <div class="metric-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div class="metric-title">Total Masuk</div>
        <div class="metric-value">{{ $metrics['total_masuk'] }}</div>
    </div>
    
    <div class="metric-card mc-yellow">
        <div class="metric-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="metric-title">Perlu Validasi</div>
        <div class="metric-value">{{ $metrics['perlu_validasi'] }}</div>
    </div>

    <div class="metric-card mc-lime">
        <div class="metric-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="metric-title">Telah Divalidasi</div>
        <div class="metric-value">{{ $metrics['telah_divalidasi'] }}</div>
    </div>

    <div class="metric-card mc-orange">
        <div class="metric-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.13 15.57a10 10 0 1 0 14.77-9.52M2.5 22v-6h6M21.87 8.43A10 10 0 1 0 7.1 17.95"/></svg>
        </div>
        <div class="metric-title">Menunggu Revisi</div>
        <div class="metric-value">{{ $metrics['sedang_revisi'] }}</div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        {{-- ══ PIPELINE ════════════════════════════════════════════ --}}
        @if(!empty($pipelineData))
        <div class="dash-card anim-fade-up delay-2">
            <div class="dash-card-header">
                <div class="title-group">
                    <div class="icon-bg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <h3>Rasio Keputusan Validasi</h3>
                </div>
            </div>
            
            <div class="pipeline-bar">
                @if($pipelineData['submitted']['pct'] > 0)
                <div class="p-segment p-sub" style="width: {{ $pipelineData['submitted']['pct'] }}%;" title="Menunggu Validasi ({{ $pipelineData['submitted']['pct'] }}%)"></div>
                @endif
                @if($pipelineData['approved']['pct'] > 0)
                <div class="p-segment p-app" style="width: {{ $pipelineData['approved']['pct'] }}%;" title="Disetujui ({{ $pipelineData['approved']['pct'] }}%)"></div>
                @endif
                @if($pipelineData['rejected']['pct'] > 0)
                <div class="p-segment p-rej" style="width: {{ $pipelineData['rejected']['pct'] }}%;" title="Ditolak ({{ $pipelineData['rejected']['pct'] }}%)"></div>
                @endif
            </div>
            
            <div class="pipeline-legend">
                <div class="leg-item"><div class="leg-dot" style="background:var(--warning);"></div> Menunggu ({{ $pipelineData['submitted']['count'] }})</div>
                <div class="leg-item"><div class="leg-dot" style="background:var(--secondary);"></div> Disetujui ({{ $pipelineData['approved']['count'] }})</div>
                <div class="leg-item"><div class="leg-dot" style="background:var(--danger);"></div> Ditolak ({{ $pipelineData['rejected']['count'] }})</div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-6">
        {{-- ══ BAR CHART ═══════════════════════════════════════════ --}}
        @if(isset($distribusiLingkungan) && $distribusiLingkungan->isNotEmpty())
        <div class="dash-card anim-fade-up delay-2">
            <div class="dash-card-header">
                <div class="title-group">
                    <div class="icon-bg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <h3>Distribusi per Lingkungan</h3>
                </div>
            </div>
            
            @php
                $maxCount = $distribusiLingkungan->max('calon_penerimas_count');
                $colors = ['#4F46E5', '#10B981', '#F59E0B', '#3B82F6', '#8B5CF6'];
            @endphp

            @foreach($distribusiLingkungan->take(4) as $index => $lingkungan)
                @php
                    $percentage = $maxCount > 0 ? ($lingkungan->calon_penerimas_count / $maxCount) * 100 : 0;
                    $color = $colors[$index % count($colors)];
                @endphp
                <div class="bc-item">
                    <div class="bc-info">
                        <span>{{ $lingkungan->name }}</span>
                        <span>{{ $lingkungan->calon_penerimas_count }} Usulan</span>
                    </div>
                    <div class="bc-track">
                        <div class="bc-fill" style="width: {{ $percentage }}%; background: {{ $color }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ══ RECENT SUBMISSIONS ══════════════════════════════════ --}}
<div class="dash-card anim-fade-up delay-3">
    <div class="dash-card-header" style="border-bottom:none; margin-bottom: 0;">
        <div class="title-group">
            <div class="icon-bg">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3>Pengajuan Terbaru Masuk</h3>
        </div>
        <a href="{{ route('stasi.calons.index') }}" class="btn-modern">
            Semua Data
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
    
    @if($recentSubmissions->isEmpty())
    <div style="padding: 4rem 2rem; text-align: center;">
        <div style="width: 64px; height: 64px; background: var(--gray-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: 0.25rem;">Belum Ada Pengajuan Baru</h4>
        <p style="font-size: 0.9rem; color: var(--gray-500);">Seluruh pengajuan dari lingkungan saat ini telah selesai divalidasi.</p>
    </div>
    @else
    <div class="recent-table-wrap mt-3">
        <table class="recent-table">
            <thead>
                <tr>
                    <th>Calon Penerima</th>
                    <th>Lingkungan</th>
                    <th>Periode</th>
                    <th>Waktu Masuk</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentSubmissions as $calon)
                <tr>
                    <td>
                        <div class="rt-name">{{ $calon->name }}</div>
                        <div class="rt-ling">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            NIK: {{ $calon->nik }}
                        </div>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;padding:0.25rem 0.6rem;background:var(--gray-100);border-radius:20px;font-size:0.8rem;font-weight:500;color:var(--gray-600);">
                            {{ $calon->lingkungan?->name ?? '—' }}
                        </span>
                    </td>
                    <td style="font-size:0.85rem; color:var(--dark); font-weight:500;">{{ $calon->periodeBantuan?->name ?? '—' }}</td>
                    <td style="font-size:0.85rem; color:var(--gray-500);">
                        {{ $calon->submitted_at ? $calon->submitted_at->format('d M Y, H:i') : '—' }}
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('stasi.calons.show', $calon) }}" class="btn-modern" style="padding: 0.4rem 0.8rem;">
                            Validasi
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
