@extends('layouts.app')

@section('title', 'Detail Calon — ' . $calonPenerima->name)
@section('meta_description', 'Detail lengkap calon penerima bantuan sosial: ' . $calonPenerima->name)

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   DETAIL VALIDASI STASI — Premium Modern UI
   ═══════════════════════════════════════════════════════════ */

:root {
    --primary: #4F46E5;
    --primary-light: #EEF2FF;
    --secondary: #10B981;
    --warning: #F59E0B;
    --danger: #EF4444;
    --dark: #0F172A;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-300: #CBD5E1;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --gray-700: #334155;
    --radius-lg: 16px;
    --radius-md: 10px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05);
    --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.1);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

body { background-color: #F8FAFC; color: var(--gray-700); font-family: 'Inter', system-ui, -apple-system, sans-serif; }

.detail-wrap { max-width: 1000px; margin: 0 auto; }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; color: var(--gray-500); margin-bottom: 1.5rem; }
.breadcrumb a { color: var(--primary); text-decoration: none; transition: var(--transition); }
.breadcrumb a:hover { color: #4338CA; text-decoration: underline; }
.breadcrumb-sep { color: var(--gray-300); }

/* Hero Card */
.hero-card { background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); margin-bottom: 2rem; overflow: hidden; position: relative; }
.hero-card::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
.hero-body { padding: 2.5rem; display: flex; align-items: flex-start; gap: 2rem; position: relative; z-index: 1; flex-wrap: wrap; }
.hero-avatar { width: 80px; height: 80px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white; flex-shrink: 0; text-transform: uppercase; box-shadow: 0 10px 20px -5px rgba(245,158,11,0.4); }
.hero-info { flex: 1; min-width: 250px; }
.hero-name { font-size: 1.75rem; font-weight: 800; color: white; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
.hero-meta { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; margin-bottom: 1rem; }
.hero-tag { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; font-size: 0.8rem; font-weight: 600; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; color: rgba(255,255,255,0.9); }
.hero-tag svg { color: rgba(255,255,255,0.6); }

.status-badge-big { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 700; border-radius: 20px; }

/* Detail Grid */
.detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
@media(max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }

.detail-card { background: white; border: 1px solid var(--gray-100); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 1.5rem; overflow: hidden; }
.detail-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-100); display: flex; align-items: center; gap: 0.75rem; background: var(--gray-50); }
.detail-card-header h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin: 0; }
.detail-card-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.detail-card-body { padding: 1.5rem; }

/* Fields */
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
@media(max-width: 576px) { .field-grid { grid-template-columns: 1fr; } }
.field-group { display: flex; flex-direction: column; gap: 0.35rem; }
.field-group.full { grid-column: 1 / -1; }
.field-label { font-size: 0.8rem; font-weight: 600; color: var(--gray-500); }
.field-value { font-size: 0.95rem; font-weight: 600; color: var(--dark); }
.field-value.highlight { color: var(--primary); font-size: 1.1rem; }

/* Actions */
.action-radio-group { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem; }
.ar-card { position: relative; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 1rem; cursor: pointer; transition: var(--transition); background: white; display: flex; align-items: center; gap: 1rem; }
.ar-card input[type="radio"] { opacity: 0; position: absolute; }
.ar-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--gray-50); color: var(--gray-500); transition: var(--transition); flex-shrink: 0; }
.ar-text { flex: 1; }
.ar-title { display: block; font-size: 0.95rem; font-weight: 600; color: var(--dark); margin-bottom: 0.1rem; }

.ar-card:hover { border-color: var(--gray-300); background: var(--gray-50); }
input[type="radio"]:checked + .ar-card { border-width: 2px; }
input#act_approve:checked + .ar-card { border-color: var(--secondary); background: #F0FDF4; }
input#act_approve:checked + .ar-card .ar-icon { background: var(--secondary); color: white; }
input#act_revision:checked + .ar-card { border-color: var(--warning); background: #FFFBEB; }
input#act_revision:checked + .ar-card .ar-icon { background: var(--warning); color: white; }
input#act_reject:checked + .ar-card { border-color: var(--danger); background: #FEF2F2; }
input#act_reject:checked + .ar-card .ar-icon { background: var(--danger); color: white; }
input#act_send:checked + .ar-card { border-color: var(--primary); background: #EEF2FF; }
input#act_send:checked + .ar-card .ar-icon { background: var(--primary); color: white; }

.note-group { display: none; margin-top: 1.5rem; animation: slideDown 0.3s ease; }
.note-group.show { display: block; }
@keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.custom-textarea { width: 100%; padding: 0.75rem 1rem; font-family: inherit; font-size: 0.9rem; border: 1px solid var(--gray-200); border-radius: var(--radius-md); outline: none; transition: var(--transition); resize: vertical; }
.custom-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }

/* Timeline */
.timeline { display: flex; flex-direction: column; padding-left: 0.5rem; }
.timeline-item { position: relative; padding-bottom: 1.5rem; padding-left: 2rem; border-left: 2px solid var(--gray-200); }
.timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }
.tl-dot { position: absolute; left: -11px; top: 0; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: var(--shadow-sm); }
.tl-dot.approve { background: var(--secondary); }
.tl-dot.reject { background: var(--danger); }
.tl-dot.submit { background: var(--primary); }
.tl-dot.revision { background: var(--warning); }
.tl-dot.send { background: #0EA5E9; }

.tl-content { background: var(--gray-50); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--gray-100); }
.tl-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 0.25rem; flex-wrap: wrap; }
.tl-action { font-size: 0.9rem; font-weight: 700; color: var(--dark); }
.tl-time { font-size: 0.8rem; font-weight: 500; color: var(--gray-500); }
.tl-actor { font-size: 0.8rem; font-weight: 500; color: var(--gray-600); }
.tl-note { margin-top: 0.75rem; font-size: 0.85rem; color: var(--gray-700); background: white; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--gray-200); }

.btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 600; border-radius: var(--radius-md); transition: var(--transition); text-decoration: none; border: none; cursor: pointer; }
.btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
.btn-outline-light { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; }
.btn-outline-light:hover { background: rgba(255,255,255,0.1); border-color: white; }
.btn-primary { background: var(--primary); color: white; padding: 0.75rem 1.5rem; font-size: 0.95rem; }
.btn-primary:hover { background: #4338CA; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }

.alert { padding: 1rem 1.25rem; border-radius: var(--radius-md); font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
.alert-success { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
.alert-error { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
</style>
@endpush

@section('content')

@php
$cp = $calonPenerima;
$statusMap = [
    'draft'              => ['label' => 'Draft', 'bg' => 'var(--gray-100)', 'color' => 'var(--gray-700)'],
    'submitted_to_stasi' => ['label' => 'Menunggu Validasi', 'bg' => 'var(--primary-light)', 'color' => 'var(--primary)'],
    'revision_requested' => ['label' => 'Sedang Direvisi', 'bg' => '#FEF3C7', 'color' => 'var(--warning)'],
    'approved_by_stasi'  => ['label' => 'Disetujui Stasi', 'bg' => '#D1FAE5', 'color' => 'var(--secondary)'],
    'rejected'           => ['label' => 'Ditolak', 'bg' => '#FEE2E2', 'color' => 'var(--danger)'],
    'sent_to_paroki'     => ['label' => 'Di Paroki', 'bg' => '#E0F2FE', 'color' => '#0EA5E9'],
    'ranked'             => ['label' => 'Diranking', 'bg' => '#F3E8FF', 'color' => '#9333EA'],
];
$s = $statusMap[$cp->status] ?? ['label' => $cp->status, 'bg' => 'var(--gray-100)', 'color' => 'var(--gray-700)'];

$canValidate = in_array($cp->status, ['submitted_to_stasi', 'approved_by_stasi']);
@endphp

<div class="detail-wrap">

<nav class="breadcrumb anim-fade-up">
    <a href="{{ route('stasi.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('stasi.calons.index') }}">Daftar Calon</a>
    <span class="breadcrumb-sep">/</span>
    <span style="color:var(--dark);font-weight:700;">{{ $cp->name }}</span>
</nav>

@if(session('success'))
<div class="alert alert-success anim-fade-up">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error anim-fade-up">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    {{ session('error') }}
</div>
@endif

<div class="hero-card anim-fade-up">
    <div class="hero-body">
        <div class="hero-avatar">{{ mb_substr($cp->name, 0, 2) }}</div>
        <div class="hero-info">
            <div class="hero-name">{{ $cp->name }}</div>
            <div class="hero-meta">
                <span class="hero-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    NIK: {{ $cp->nik }}
                </span>
                <span class="hero-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    KK: {{ $cp->nomor_kk }}
                </span>
                <span class="hero-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Lingkungan: {{ $cp->lingkungan?->name ?? '-' }}
                </span>
            </div>
            <div>
                <span class="status-badge-big" style="background:{{ $s['bg'] }};color:{{ $s['color'] }};">
                    <div style="width:8px;height:8px;border-radius:50%;background:currentColor;"></div>
                    {{ $s['label'] }}
                </span>
            </div>
        </div>
        <div style="align-self: center;">
            <a href="{{ route('stasi.calons.index') }}" class="btn btn-sm btn-outline-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </a>
        </div>
    </div>
</div>

<div class="detail-grid">
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        
        <div class="detail-card anim-fade-up delay-1">
            <div class="detail-card-header">
                <div class="detail-card-icon" style="background:var(--primary-light); color:var(--primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h3>Data Calon Lengkap</h3>
            </div>
            <div class="detail-card-body">
                <div class="field-grid">
                    <div class="field-group full">
                        <span class="field-label">Alamat Lengkap</span>
                        <span class="field-value" style="line-height: 1.5;">{{ $cp->address }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Penghasilan Bulanan</span>
                        <span class="field-value highlight">Rp {{ number_format($cp->monthly_income, 0, ',', '.') }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Jumlah Tanggungan</span>
                        <span class="field-value">{{ $cp->dependents_count }} orang</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Status Tempat Tinggal</span>
                        <span class="field-value">{{ str_replace('_', ' ', Str::title($cp->housing_status)) }} <span style="color:var(--gray-400);font-size:0.8rem;">(Skor {{ $cp->housing_status_score }})</span></span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Disabilitas</span>
                        <span class="field-value" style="color:{{ $cp->has_disability ? 'var(--danger)' : 'var(--dark)' }};">
                            {{ $cp->has_disability ? 'Ya (Skor '.$cp->disability_score.')' : 'Tidak' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card anim-fade-up delay-2">
            <div class="detail-card-header">
                <div class="detail-card-icon" style="background:#E0F2FE; color:#0EA5E9;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3>Riwayat Validasi</h3>
            </div>
            <div class="detail-card-body">
                @if($cp->validasiLogs->isEmpty())
                    <div style="text-align:center; padding: 2rem 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:0.5rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p style="color:var(--gray-500);font-size:0.9rem;">Belum ada riwayat aktivitas tercatat.</p>
                    </div>
                @else
                    <div class="timeline">
                        @foreach($cp->validasiLogs as $log)
                        @php
                            $dotClass = 'submit';
                            if(str_contains(strtolower($log->action), 'approve')) $dotClass = 'approve';
                            if(str_contains(strtolower($log->action), 'reject')) $dotClass = 'reject';
                            if(str_contains(strtolower($log->action), 'revision')) $dotClass = 'revision';
                            if(str_contains(strtolower($log->action), 'send')) $dotClass = 'send';
                        @endphp
                        <div class="timeline-item">
                            <div class="tl-dot {{ $dotClass }}"></div>
                            <div class="tl-content">
                                <div class="tl-header">
                                    <div>
                                        <div class="tl-action">{{ Str::title(str_replace('_', ' ', $log->action)) }}</div>
                                        <div class="tl-actor">Oleh: {{ $log->actor?->name ?? 'Sistem' }}</div>
                                    </div>
                                    <div class="tl-time">{{ $log->created_at->format('d M Y, H:i') }}</div>
                                </div>
                                @if($log->notes)
                                <div class="tl-note">
                                    <strong style="color:var(--dark);font-size:0.8rem;display:block;margin-bottom:0.2rem;">Catatan:</strong>
                                    {{ $log->notes }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
    </div>
    
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        
        {{-- Form Validasi --}}
        <div class="detail-card anim-fade-up delay-1" style="{{ !$canValidate ? 'opacity:0.8;' : '' }}">
            <div class="detail-card-header" style="background: {{ $canValidate ? 'var(--dark)' : 'var(--gray-100)' }}; color: {{ $canValidate ? 'white' : 'var(--gray-600)' }};">
                <div class="detail-card-icon" style="background: {{ $canValidate ? 'rgba(255,255,255,0.1)' : 'var(--gray-200)' }}; color:inherit;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3 style="color:inherit;">Aksi Validasi Stasi</h3>
            </div>
            <div class="detail-card-body">
                @if($canValidate)
                <form action="{{ route('stasi.calons.process-batch') }}" method="POST" id="form-validasi">
                    @csrf
                    <input type="hidden" name="ids[]" value="{{ $cp->id }}">
                    
                    <div class="action-radio-group">
                        @if($cp->status === 'submitted_to_stasi')
                        <label>
                            <input type="radio" name="action" value="approve" id="act_approve" checked>
                            <div class="ar-card">
                                <div class="ar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                                <div class="ar-text">
                                    <span class="ar-title">Setujui Calon</span>
                                </div>
                            </div>
                        </label>
                        <label>
                            <input type="radio" name="action" value="revision" id="act_revision">
                            <div class="ar-card">
                                <div class="ar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg></div>
                                <div class="ar-text">
                                    <span class="ar-title">Minta Revisi</span>
                                </div>
                            </div>
                        </label>
                        @endif

                        <label>
                            <input type="radio" name="action" value="reject" id="act_reject">
                            <div class="ar-card">
                                <div class="ar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                                <div class="ar-text">
                                    <span class="ar-title">Tolak Mutlak</span>
                                </div>
                            </div>
                        </label>

                        @if($cp->status === 'approved_by_stasi')
                        <label>
                            <input type="radio" name="action" value="send_to_paroki" id="act_send">
                            <div class="ar-card">
                                <div class="ar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></div>
                                <div class="ar-text">
                                    <span class="ar-title">Kirim ke Paroki</span>
                                </div>
                            </div>
                        </label>
                        @endif
                    </div>

                    <div class="note-group" id="note-group">
                        <label class="field-label" style="display:block;margin-bottom:0.5rem;color:var(--dark);">Catatan / Alasan Khusus <span style="color:var(--danger);">*</span></label>
                        <textarea name="note" id="modal_note" class="custom-textarea" rows="4" placeholder="Tuliskan instruksi revisi atau alasan penolakan..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1.5rem;" id="btn-submit-action">Terapkan Aksi Sekarang</button>
                </form>
                @else
                <div style="text-align:center; padding: 2rem 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:1rem;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <p style="color:var(--gray-600);font-size:0.95rem;font-weight:500;">Tindakan validasi tidak tersedia untuk status saat ini.</p>
                </div>
                @endif
            </div>
        </div>
        
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('input[name="action"]');
    const noteGroup = document.getElementById('note-group');
    const noteInput = document.getElementById('modal_note');
    const btnSubmit = document.getElementById('btn-submit-action');
    
    function updateNoteVisibility() {
        if(!document.querySelector('input[name="action"]:checked')) return;
        const selected = document.querySelector('input[name="action"]:checked').value;
        if (selected === 'revision' || selected === 'reject') {
            noteGroup.classList.add('show');
            if(selected === 'revision') noteInput.required = true;
        } else {
            noteGroup.classList.remove('show');
            noteInput.required = false;
        }
    }

    if(radios.length > 0) {
        radios.forEach(r => r.addEventListener('change', updateNoteVisibility));
        updateNoteVisibility();
    }

    const form = document.getElementById('form-validasi');
    if (form) {
        form.addEventListener('submit', () => {
            btnSubmit.disabled = true;
            
            if (!document.getElementById('spin-anim')) {
                const style = document.createElement('style');
                style.id = 'spin-anim';
                style.innerHTML = '@keyframes spin { 100% { transform: rotate(360deg); } } .anim-spin { animation: spin 1s linear infinite; }';
                document.head.appendChild(style);
            }
            
            btnSubmit.innerHTML = `
                <svg class="anim-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                Memproses...
            `;
        });
    }
});
</script>
@endpush
