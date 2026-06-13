@extends('layouts.app')

@section('title', 'Validasi Calon Penerima')
@section('meta_description', 'Lakukan validasi data calon penerima yang masuk dari berbagai lingkungan.')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   INDEX VALIDASI STASI — Premium Modern UI
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

.page-header { margin-bottom: 2rem; }
.page-title { font-size: 1.8rem; font-weight: 800; color: var(--dark); letter-spacing: -0.02em; margin-bottom: 0.5rem; }
.page-subtitle { font-size: 0.95rem; color: var(--gray-500); }

/* ── Status Tabs ─────────────────────────────────────────── */
.status-tabs { 
    display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; 
    background: white; padding: 0.5rem; border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm); border: 1px solid var(--gray-100);
}
.status-tab {
    display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;
    border-radius: var(--radius-md); cursor: pointer; text-decoration: none; transition: var(--transition);
    background: transparent; color: var(--gray-600); white-space: nowrap; border: 1px solid transparent;
}
.status-tab:hover { background: var(--gray-50); color: var(--dark); }
.status-tab.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); border-color: var(--primary); }

.tab-count {
    display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; padding: 0 0.4rem;
    font-size: 0.7rem; font-weight: 700; border-radius: 20px; background: var(--gray-100); color: var(--gray-700);
    transition: var(--transition);
}
.status-tab.active .tab-count { background: rgba(255,255,255,0.2); color: white; }

/* ── Toolbar ─────────────────────────────────────────────── */
.index-toolbar {
    display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;
    background: white; padding: 1rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--gray-100);
}

.search-box { position: relative; flex: 1; min-width: 250px; }
.search-box svg { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); pointer-events: none; }
.search-box input {
    width: 100%; padding: 0.65rem 1rem 0.65rem 2.75rem; font-size: 0.9rem; font-weight: 500;
    background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-md); 
    outline: none; transition: var(--transition); color: var(--dark);
}
.search-box input:focus { background: white; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }
.search-box input::placeholder { color: var(--gray-400); font-weight: 400; }
.search-box .clear-btn { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: var(--gray-200); border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.75rem; color: var(--gray-600); text-decoration: none; transition: var(--transition); }
.search-box .clear-btn:hover { background: var(--gray-300); color: var(--dark); }

.form-select {
    padding: 0.65rem 2.25rem 0.65rem 1rem; font-size: 0.9rem; font-weight: 500;
    background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-md); 
    outline: none; cursor: pointer; color: var(--dark); appearance: none; transition: var(--transition);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 16px;
}
.form-select:focus { background-color: white; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }

.btn-primary { background: var(--primary); color: white; border: none; padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; }
.btn-primary:hover { background: #4338CA; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
.btn-secondary { background: white; color: var(--gray-700); border: 1px solid var(--gray-200); padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
.btn-secondary:hover { background: var(--gray-50); color: var(--dark); border-color: var(--gray-300); }

/* ── Table Card ──────────────────────────────────────────── */
.table-card { background: white; border: 1px solid var(--gray-100); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 2rem; }
.table-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-100); display: flex; align-items: center; justify-content: space-between; }
.table-card-title { font-size: 1.05rem; font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 0.5rem; }

/* ── Table ───────────────────────────────────────────────── */
.custom-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
.custom-table th { padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200); white-space: nowrap; }
.custom-table td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-100); vertical-align: middle; transition: background 0.15s; }
.custom-table tbody tr:hover td { background: var(--gray-50); }
.custom-table tbody tr:last-child td { border-bottom: none; }

.calon-name-cell .name { font-weight: 600; font-size: 0.95rem; color: var(--dark); margin-bottom: 0.2rem; }
.calon-name-cell .nik { font-size: 0.8rem; color: var(--gray-500); display: flex; align-items: center; gap: 0.3rem; }
.income-cell { font-weight: 600; color: var(--dark); }
.income-label { font-size: 0.75rem; font-weight: 500; color: var(--gray-500); display: block; margin-bottom: 0.1rem; }

/* Status pill */
.status-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.8rem; font-size: 0.75rem; font-weight: 600; border-radius: 20px; white-space: nowrap; }
.pill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.pill-draft     { background: var(--gray-100); color: var(--gray-700); }
.pill-submitted { background: var(--primary-light); color: var(--primary); }
.pill-revision  { background: #FEF3C7; color: var(--warning); }
.pill-approved  { background: #D1FAE5; color: var(--secondary); }
.pill-rejected  { background: #FEE2E2; color: var(--danger); }
.pill-sent      { background: #E0F2FE; color: #0EA5E9; }
.pill-ranked    { background: #F3E8FF; color: #9333EA; }

/* ── Checkbox ────────────────────────────────────────────── */
.custom-checkbox {
    appearance: none; width: 18px; height: 18px; border: 2px solid var(--gray-300); border-radius: 4px;
    background: white; cursor: pointer; position: relative; transition: var(--transition); vertical-align: middle;
}
.custom-checkbox:hover { border-color: var(--primary); }
.custom-checkbox:checked { background: var(--primary); border-color: var(--primary); }
.custom-checkbox:checked::after {
    content: ''; position: absolute; left: 5px; top: 2px; width: 5px; height: 9px;
    border-right: 2px solid white; border-bottom: 2px solid white; transform: rotate(45deg);
}
.custom-checkbox:disabled { opacity: 0.5; cursor: not-allowed; background: var(--gray-100); }

/* ── Bulk Action Bar ─────────────────────────────────────── */
.bulk-bar {
    display: none; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;
    background: var(--dark); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); 
    margin-bottom: 1.5rem; position: sticky; top: 1rem; z-index: 100; animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.bulk-bar.show { display: flex; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.bulk-count { font-size: 0.95rem; font-weight: 600; color: white; display: flex; align-items: center; gap: 0.5rem; }
.bulk-count-badge { background: rgba(255,255,255,0.2); padding: 0.2rem 0.6rem; border-radius: 20px; font-weight: 700; }

/* ── Modals ──────────────────────────────────────────────── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 200; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal-box { background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); padding: 2rem; width: 100%; max-width: 500px; margin: 1rem; animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); max-height: 90vh; overflow-y: auto; }
@keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal-box h3 { font-size: 1.25rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem; }
.modal-box p { font-size: 0.9rem; color: var(--gray-500); margin-bottom: 1.5rem; line-height: 1.5; }
.modal-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }

/* Radio Cards for Action Selection */
.action-radio-group { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem; }
.ar-card { position: relative; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 1rem; cursor: pointer; transition: var(--transition); background: white; display: flex; align-items: center; gap: 1rem; }
.ar-card input[type="radio"] { opacity: 0; position: absolute; }
.ar-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--gray-50); color: var(--gray-500); transition: var(--transition); }
.ar-text { flex: 1; }
.ar-title { display: block; font-size: 0.95rem; font-weight: 600; color: var(--dark); margin-bottom: 0.1rem; }
.ar-desc { display: block; font-size: 0.8rem; color: var(--gray-500); }

.ar-card:hover { border-color: var(--gray-300); background: var(--gray-50); }

/* Selected States */
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
.form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; }
.custom-textarea { width: 100%; padding: 0.75rem 1rem; font-family: inherit; font-size: 0.9rem; border: 1px solid var(--gray-200); border-radius: var(--radius-md); outline: none; transition: var(--transition); resize: vertical; }
.custom-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }

/* ── Pagination ──────────────────────────────────────────── */
.pagination-wrap { padding: 1.25rem 1.5rem; border-top: 1px solid var(--gray-100); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.pagination-info { font-size: 0.85rem; color: var(--gray-500); }
.pagination-links { display: flex; gap: 0.25rem; }
.page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 0.5rem; font-size: 0.85rem; font-weight: 500; border-radius: 8px; text-decoration: none; color: var(--gray-600); transition: var(--transition); }
.page-btn:hover:not(.disabled) { background: var(--gray-100); color: var(--dark); }
.page-btn.active { background: var(--primary); color: white; font-weight: 600; box-shadow: 0 4px 10px rgba(79,70,229,0.3); }
.page-btn.disabled { opacity: 0.4; cursor: not-allowed; }

.alert { padding: 1rem 1.25rem; border-radius: var(--radius-md); font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
.alert-success { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
.alert-error { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
</style>
@endpush

@section('content')

{{-- ══ PAGE HEADER ══════════════════════════════════════════ --}}
<div class="page-header anim-fade-up">
    <h1 class="page-title">Validasi Data Calon</h1>
    <p class="page-subtitle">Wilayah <strong>{{ Auth::user()->stasi?->name ?? '—' }}</strong> · Kelola dan validasi calon yang diajukan oleh lingkungan</p>
</div>

{{-- ══ FLASH MESSAGES ══════════════════════════════════════ --}}
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

{{-- ══ STATUS TABS ══════════════════════════════════════════ --}}
@php
    $tabConfig = [
        ''                   => ['label' => 'Semua', 'cls' => ''],
        'submitted_to_stasi' => ['label' => 'Perlu Validasi', 'cls' => ''],
        'revision_requested' => ['label' => 'Menunggu Revisi','cls' => ''],
        'approved_by_stasi'  => ['label' => 'Disetujui', 'cls' => ''],
        'sent_to_paroki'     => ['label' => 'Dikirim ke Paroki','cls' => ''],
        'rejected'           => ['label' => 'Ditolak', 'cls' => ''],
    ];
@endphp

<div class="status-tabs anim-fade-up delay-1">
    @foreach($tabConfig as $tabStatus => $tabInfo)
    @php
        $isActive = ($status ?? '') === $tabStatus;
        $count    = $tabStatus === '' ? $totalAll : ($statusCounts[$tabStatus] ?? 0);
        $tabUrl   = route('stasi.calons.index', array_filter([
            'status'   => $tabStatus ?: null,
            'search'   => $search,
            'lingkungan_id' => $lingkungan_id,
            'per_page' => $perPage != 25 ? $perPage : null,
        ]));
    @endphp
    <a href="{{ $tabUrl }}" class="status-tab {{ $tabInfo['cls'] }} {{ $isActive ? 'active' : '' }}">
        {{ $tabInfo['label'] }}
        @if($count > 0 || $tabStatus === '')
        <span class="tab-count">{{ $count }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- ══ SEARCH + FILTER TOOLBAR ════════════════════════════ --}}
<form action="{{ route('stasi.calons.index') }}" method="GET" id="filter-form">
    @if($status)
    <input type="hidden" name="status" value="{{ $status }}">
    @endif

    <div class="index-toolbar anim-fade-up delay-1">
        <div class="search-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIK..." autocomplete="off">
            @if($search)
            <a href="{{ route('stasi.calons.index', array_filter(['status' => $status, 'lingkungan_id' => $lingkungan_id])) }}" class="clear-btn" title="Hapus pencarian">✕</a>
            @endif
        </div>

        <select name="lingkungan_id" class="form-select" onchange="document.getElementById('filter-form').submit()" style="flex: 1; max-width: 250px;">
            <option value="">Semua Lingkungan</option>
            @foreach($lingkungans as $ling)
            <option value="{{ $ling->id }}" {{ ($lingkungan_id ?? '') == $ling->id ? 'selected' : '' }}>{{ $ling->name }}</option>
            @endforeach
        </select>

        @if(($status ?? '') === 'approved_by_stasi')
        <form action="{{ route('stasi.surat-permohonan.store') }}" method="POST" style="margin-right: auto;" onsubmit="return confirm('Buat Surat Pengantar untuk semua calon yang Disetujui? Status mereka akan berubah menjadi Dikirim ke Paroki.');">
            @csrf
            <button type="submit" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Buat Surat Pengantar
            </button>
        </form>
        @endif

        <select name="per_page" class="form-select" onchange="document.getElementById('filter-form').submit()">
            @foreach([10, 25, 50, 100] as $pp)
            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} / halaman</option>
            @endforeach
        </select>

        <button type="submit" class="btn-primary">Terapkan</button>
        @if($search || $status || $lingkungan_id)
        <a href="{{ route('stasi.calons.index') }}" class="btn-secondary">Reset</a>
        @endif
    </div>
</form>

{{-- ══ BULK ACTION BAR ════════════════════════════════════ --}}
<div class="bulk-bar" id="bulk-bar">
    <div class="bulk-count">
        <span class="bulk-count-badge" id="bulk-count-text">0</span> calon dipilih
    </div>
    <button type="button" class="btn-primary" style="background: white; color: var(--dark);" id="btn-bulk-action">
        Tentukan Tindakan Batch
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </button>
</div>

{{-- ══ TABLE CARD ══════════════════════════════════════════ --}}
<div class="table-card anim-fade-up delay-2">
    <div class="table-card-header">
        <div class="table-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Daftar Calon
        </div>
    </div>

    @if($calons->isEmpty())
    <div style="padding: 5rem 2rem; text-align: center;">
        <div style="width: 64px; height: 64px; background: var(--gray-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        </div>
        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem;">Data Tidak Ditemukan</h3>
        <p style="font-size: 0.95rem; color: var(--gray-500);">Tidak ada calon penerima yang sesuai dengan kriteria filter saat ini.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <form action="{{ route('stasi.calons.process-batch') }}" method="POST" id="form-bulk-action">
            @csrf
            <input type="hidden" name="action" id="bulk_action_val" value="">
            <input type="hidden" name="note" id="bulk_note_val" value="">
            
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">
                            <input type="checkbox" class="custom-checkbox" id="check-all" title="Pilih semua di halaman ini">
                        </th>
                        <th>Informasi Calon</th>
                        <th>Lingkungan & Periode</th>
                        <th>Kondisi Ekonomi</th>
                        <th>Tgl. Diajukan</th>
                        <th>Status Validasi</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calons as $calon)
                    @php
                        $canSelect = in_array($calon->status, ['submitted_to_stasi', 'approved_by_stasi']);
                    @endphp
                    <tr>
                        <td style="text-align:center;">
                            @if($canSelect)
                            <input type="checkbox" name="ids[]" value="{{ $calon->id }}" class="custom-checkbox check-item" data-status="{{ $calon->status }}">
                            @else
                            <input type="checkbox" class="custom-checkbox" disabled>
                            @endif
                        </td>
                        <td class="calon-name-cell">
                            <div class="name">{{ $calon->name }}</div>
                            <div class="nik">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $calon->nik }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--dark); margin-bottom:0.2rem;">{{ $calon->lingkungan?->name ?? '-' }}</div>
                            <div style="font-size:0.8rem; color:var(--gray-500);">{{ $calon->periodeBantuan?->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="income-cell">
                                <span class="income-label">Penghasilan/Bulan</span>
                                Rp {{ number_format($calon->monthly_income, 0, ',', '.') }}
                            </div>
                        </td>
                        <td style="font-size:0.85rem; color:var(--gray-600); font-weight:500;">
                            {{ $calon->submitted_at ? $calon->submitted_at->format('d M Y') : '-' }}
                        </td>
                        <td>
                            @php
                                $statusConf = [
                                    'draft' => ['Draft', 'pill-draft'],
                                    'submitted_to_stasi' => ['Perlu Validasi', 'pill-submitted'],
                                    'revision_requested' => ['Revisi', 'pill-revision'],
                                    'approved_by_stasi' => ['Disetujui', 'pill-approved'],
                                    'rejected' => ['Ditolak', 'pill-rejected'],
                                    'sent_to_paroki' => ['Ke Paroki', 'pill-sent'],
                                    'ranked' => ['Diranking', 'pill-ranked']
                                ];
                                $cConf = $statusConf[$calon->status] ?? [$calon->status, 'pill-draft'];
                            @endphp
                            <div class="status-pill {{ $cConf[1] }}">
                                <div class="pill-dot"></div>
                                {{ $cConf[0] }}
                            </div>
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('stasi.calons.show', $calon) }}" class="btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                Periksa Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>

    @if($calons->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">
            Menampilkan <strong>{{ $calons->firstItem() }}</strong> - <strong>{{ $calons->lastItem() }}</strong> dari <strong>{{ $calons->total() }}</strong> data
        </div>
        <div class="pagination-links">
            @if($calons->onFirstPage())
                <span class="page-btn disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $calons->previousPageUrl() }}" class="page-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif
            
            @foreach($calons->getUrlRange(max(1, $calons->currentPage() - 2), min($calons->lastPage(), $calons->currentPage() + 2)) as $page => $url)
                @if($page == $calons->currentPage())
                    <span class="page-btn active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if($calons->hasMorePages())
                <a href="{{ $calons->nextPageUrl() }}" class="page-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span class="page-btn disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
    </div>
    @endif
    
    @endif
</div>

{{-- ══ BULK ACTION MODAL ══════════════════════════════════ --}}
<div class="modal-overlay" id="bulk-modal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <h3>Pilih Tindakan Massal</h3>
        <p>Anda akan menerapkan tindakan ini pada <strong id="bulk-modal-count" style="color:var(--dark);">0</strong> data calon yang dipilih secara otomatis.</p>
        
        <div class="action-radio-group">
            <label>
                <input type="radio" name="modal_action" value="approve" id="act_approve" checked>
                <div class="ar-card">
                    <div class="ar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="ar-text">
                        <span class="ar-title">Setujui (Approve)</span>
                        <span class="ar-desc">Calon dinyatakan memenuhi syarat dan valid.</span>
                    </div>
                </div>
            </label>
            
            <label>
                <input type="radio" name="modal_action" value="revision" id="act_revision">
                <div class="ar-card">
                    <div class="ar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </div>
                    <div class="ar-text">
                        <span class="ar-title">Minta Revisi (Return)</span>
                        <span class="ar-desc">Kembalikan ke Lingkungan untuk dilakukan perbaikan.</span>
                    </div>
                </div>
            </label>
            
            <label>
                <input type="radio" name="modal_action" value="reject" id="act_reject">
                <div class="ar-card">
                    <div class="ar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <div class="ar-text">
                        <span class="ar-title">Tolak (Reject)</span>
                        <span class="ar-desc">Calon dinyatakan tidak memenuhi syarat secara mutlak.</span>
                    </div>
                </div>
            </label>

            <label>
                <input type="radio" name="modal_action" value="send_to_paroki" id="act_send">
                <div class="ar-card">
                    <div class="ar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </div>
                    <div class="ar-text">
                        <span class="ar-title">Kirim ke Paroki</span>
                        <span class="ar-desc">Kirim data yang telah disetujui untuk di-ranking oleh Paroki.</span>
                    </div>
                </div>
            </label>
        </div>

        <div class="note-group" id="note-group">
            <label class="form-label" for="modal_note">Catatan / Alasan Khusus</label>
            <textarea id="modal_note" class="custom-textarea" rows="3" placeholder="Tuliskan catatan opsional (sangat disarankan untuk penolakan/revisi)..."></textarea>
            <div style="font-size:0.8rem; color:var(--gray-400); margin-top:0.4rem;">Catatan ini akan dilampirkan serentak pada semua data yang Anda pilih.</div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" id="bulk-modal-cancel">Batal</button>
            <button type="button" class="btn-primary" id="bulk-modal-confirm">Terapkan Sekarang</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkAll = document.getElementById('check-all');
    const checkItems = document.querySelectorAll('.check-item');
    const bulkBar = document.getElementById('bulk-bar');
    const bulkCountText = document.getElementById('bulk-count-text');
    const btnBulkAction = document.getElementById('btn-bulk-action');

    const bulkModal = document.getElementById('bulk-modal');
    const bulkModalCount = document.getElementById('bulk-modal-count');
    const bulkModalCancel = document.getElementById('bulk-modal-cancel');
    const bulkModalConfirm = document.getElementById('bulk-modal-confirm');
    const formBulkAction = document.getElementById('form-bulk-action');
    
    const inputActionVal = document.getElementById('bulk_action_val');
    const inputNoteVal = document.getElementById('bulk_note_val');
    const radios = document.querySelectorAll('input[name="modal_action"]');
    const modalNote = document.getElementById('modal_note');
    const noteGroup = document.getElementById('note-group');

    function updateBulkUI() {
        if (!checkItems.length) return;
        const checkedCount = document.querySelectorAll('.check-item:checked').length;
        
        if (checkAll) {
            checkAll.checked = checkedCount > 0 && checkedCount === checkItems.length;
            checkAll.indeterminate = checkedCount > 0 && checkedCount < checkItems.length;
        }
        
        if (checkedCount > 0) {
            bulkCountText.textContent = checkedCount;
            bulkBar.classList.add('show');
        } else {
            bulkBar.classList.remove('show');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', (e) => {
            checkItems.forEach(item => { if(!item.disabled) item.checked = e.target.checked; });
            updateBulkUI();
        });
    }

    checkItems.forEach(item => item.addEventListener('change', updateBulkUI));

    function updateNoteVisibility() {
        const selected = document.querySelector('input[name="modal_action"]:checked').value;
        if (selected === 'revision' || selected === 'reject') {
            noteGroup.classList.add('show');
            modalNote.focus();
        } else {
            noteGroup.classList.remove('show');
            modalNote.value = '';
        }
    }

    radios.forEach(r => r.addEventListener('change', updateNoteVisibility));

    if (btnBulkAction && bulkModal) {
        btnBulkAction.addEventListener('click', () => {
            const count = document.querySelectorAll('.check-item:checked').length;
            bulkModalCount.textContent = count;
            bulkModal.classList.add('show');
            updateNoteVisibility();
        });
        
        bulkModalCancel.addEventListener('click', () => bulkModal.classList.remove('show'));
        
        bulkModal.addEventListener('click', (e) => {
            if (e.target === bulkModal) bulkModal.classList.remove('show');
        });
        
        bulkModalConfirm.addEventListener('click', () => {
            const selectedAction = document.querySelector('input[name="modal_action"]:checked').value;
            
            inputActionVal.value = selectedAction;
            inputNoteVal.value = modalNote.value;

            bulkModalConfirm.disabled = true;
            bulkModalConfirm.innerHTML = `
                <svg class="anim-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:0.5rem;"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                Memproses...
            `;
            
            // Add keyframes for anim-spin programmatically to head
            if (!document.getElementById('spin-anim')) {
                const style = document.createElement('style');
                style.id = 'spin-anim';
                style.innerHTML = '@keyframes spin { 100% { transform: rotate(360deg); } } .anim-spin { animation: spin 1s linear infinite; }';
                document.head.appendChild(style);
            }

            formBulkAction.submit();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') bulkModal?.classList.remove('show');
    });
});
</script>
@endpush
