@extends('layouts.app')

@section('title', 'Daftar Calon Penerima')
@section('meta_description', 'Kelola dan pantau seluruh calon penerima bantuan sosial di lingkungan Anda.')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   INDEX — Daftar Calon Penerima · Neobrutalism
   ═══════════════════════════════════════════════════════════ */

/* ── Toolbar ─────────────────────────────────────────────── */
.index-toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 220px;
}

.search-box svg {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
    pointer-events: none;
}

.search-box input {
    width: 100%;
    padding: 0.65rem 1rem 0.65rem 2.4rem;
    font-family: var(--font-sans);
    font-size: 0.9rem;
    font-weight: 500;
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    outline: none;
    transition: box-shadow 0.12s, border-color 0.12s;
    color: var(--black);
}

.search-box input:focus {
    border-color: var(--blue);
    box-shadow: 4px 4px 0 var(--blue);
}

.search-box input::placeholder {
    color: var(--gray-300);
    font-weight: 400;
}

.search-box .clear-btn {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    background: var(--gray-200);
    border: 1.5px solid var(--black);
    border-radius: 2px;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.7rem;
    font-weight: 800;
    color: var(--black);
    transition: background 0.1s;
}

.search-box .clear-btn:hover { background: var(--yellow); }

.per-page-select {
    padding: 0.65rem 2rem 0.65rem 0.85rem;
    font-family: var(--font-sans);
    font-size: 0.88rem;
    font-weight: 600;
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    outline: none;
    cursor: pointer;
    color: var(--black);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%230a0a0a' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.6rem center;
}

/* ── Status Tabs ─────────────────────────────────────────── */
.status-tabs {
    display: flex;
    gap: 0.4rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.status-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2.5px solid var(--black);
    border-radius: 2px;
    cursor: pointer;
    text-decoration: none;
    transition: box-shadow 0.12s, transform 0.12s, background 0.12s;
    background: var(--white);
    color: var(--black);
    box-shadow: 3px 3px 0 var(--black);
    white-space: nowrap;
}

.status-tab:hover {
    transform: translate(-1px, -1px);
    box-shadow: 4px 4px 0 var(--black);
}

.status-tab:active {
    transform: translate(1px, 1px);
    box-shadow: 0 0 0 var(--black);
}

.status-tab.active {
    background: var(--black);
    color: var(--white);
    box-shadow: 3px 3px 0 var(--yellow);
}

.tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 18px;
    padding: 0 0.35rem;
    font-size: 0.68rem;
    font-weight: 800;
    border-radius: 2px;
    font-family: var(--font-mono);
    border: 1.5px solid currentColor;
}

.status-tab.active .tab-count {
    background: var(--yellow);
    color: var(--black);
    border-color: var(--yellow);
}

/* Status-colored active tabs */
.tab-draft.active        { background: var(--gray-700); box-shadow: 3px 3px 0 var(--gray-300); }
.tab-submitted.active    { background: var(--blue);     box-shadow: 3px 3px 0 var(--yellow);   }
.tab-revision.active     { background: var(--orange);   box-shadow: 3px 3px 0 var(--black);    }
.tab-approved.active     { background: #2d8a00;         box-shadow: 3px 3px 0 var(--lime);     }
.tab-sent.active         { background: #008a7a;         box-shadow: 3px 3px 0 var(--teal);     }
.tab-rejected.active     { background: var(--red);      box-shadow: 3px 3px 0 var(--black);    }

/* ── Table Card ──────────────────────────────────────────── */
.table-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.table-card-header {
    background: var(--black);
    padding: 0.85rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.table-card-title {
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--white);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-result-info {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-500);
    font-family: var(--font-mono);
}

/* ── Table ───────────────────────────────────────────────── */
.nb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

/* Sortable header */
.nb-table th {
    padding: 0.65rem 1rem;
    text-align: left;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gray-700);
    background: var(--gray-50);
    border-bottom: 2px solid var(--black);
    border-right: 1px solid var(--gray-200);
    white-space: nowrap;
}

.nb-table th:last-child { border-right: none; }

.sort-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    text-decoration: none;
    color: inherit;
    transition: color 0.12s;
}

.sort-link:hover { color: var(--blue); }

.sort-icon {
    display: flex;
    flex-direction: column;
    gap: 1px;
    opacity: 0.35;
}

.sort-icon.asc  .arr-up   { opacity: 1; }
.sort-icon.desc .arr-down { opacity: 1; }

.arr-up, .arr-down {
    width: 0; height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
}

.arr-up   { border-bottom: 5px solid var(--black); }
.arr-down { border-top:    5px solid var(--black); }

/* Table Body */
.nb-table td {
    padding: 0.7rem 1rem;
    border-bottom: 1px solid var(--gray-100);
    border-right: 1px solid var(--gray-100);
    vertical-align: middle;
}

.nb-table td:last-child { border-right: none; }

.nb-table tbody tr {
    transition: background 0.08s;
}

.nb-table tbody tr:hover {
    background: #fffef5;
}

.nb-table tbody tr:last-child td {
    border-bottom: none;
}

/* ── Row cells ───────────────────────────────────────────── */
.calon-no {
    font-family: var(--font-mono);
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--gray-300);
    width: 42px;
    text-align: center;
}

.calon-name-cell .name {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--black);
}

.calon-name-cell .nik {
    font-size: 0.72rem;
    font-family: var(--font-mono);
    color: var(--gray-500);
    margin-top: 0.1rem;
}

.income-cell {
    font-family: var(--font-mono);
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--black);
    white-space: nowrap;
}

.income-cell .income-label {
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--gray-500);
    font-family: var(--font-sans);
    display: block;
}

.dep-cell {
    font-family: var(--font-mono);
    font-size: 0.88rem;
    font-weight: 700;
    text-align: center;
}

.period-cell {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-700);
    max-width: 120px;
}

/* Status pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.18rem 0.6rem;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border: 2px solid var(--black);
    border-radius: 2px;
    white-space: nowrap;
    box-shadow: 2px 2px 0 var(--black);
}

.pill-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

.pill-draft     { background: var(--gray-200); color: var(--gray-700); }
.pill-submitted { background: var(--blue);     color: var(--white); }
.pill-revision  { background: var(--orange);   color: var(--white); }
.pill-approved  { background: var(--lime);     color: var(--black); }
.pill-rejected  { background: var(--red);      color: var(--white); }
.pill-sent      { background: var(--teal);     color: var(--black); }
.pill-ranked    { background: var(--blue);     color: var(--white); }

/* ── Action buttons ──────────────────────────────────────── */
.action-group {
    display: flex;
    gap: 0.3rem;
    align-items: center;
}

.tbl-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px; height: 30px;
    border: 2px solid var(--black);
    border-radius: 2px;
    background: var(--white);
    box-shadow: 2px 2px 0 var(--black);
    cursor: pointer;
    text-decoration: none;
    color: var(--black);
    transition: transform 0.1s, box-shadow 0.1s, background 0.1s;
    flex-shrink: 0;
}

.tbl-btn:hover {
    transform: translate(-1px, -1px);
    box-shadow: 3px 3px 0 var(--black);
}

.tbl-btn:active {
    transform: translate(1px, 1px) !important;
    box-shadow: 0 0 0 var(--black) !important;
}

.tbl-btn-view   { }
.tbl-btn-edit   { background: var(--yellow); }
.tbl-btn-submit { background: var(--blue); color: var(--white); }
.tbl-btn-delete { background: var(--red); color: var(--white); }

/* ── Submit form inline ──────────────────────────────────── */
.inline-form { display: inline; }

/* ── Empty State ─────────────────────────────────────────── */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}

.empty-icon {
    width: 72px; height: 72px;
    background: var(--gray-100);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
}

.empty-state h3 {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--black);
    margin-bottom: 0.4rem;
}

.empty-state p {
    font-size: 0.85rem;
    color: var(--gray-500);
    font-weight: 500;
    max-width: 300px;
    margin: 0 auto 1.5rem;
}

/* ── Pagination ──────────────────────────────────────────── */
.pagination-wrap {
    padding: 1rem 1.5rem;
    border-top: 2px solid var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.pagination-info {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--gray-500);
    font-family: var(--font-mono);
}

.pagination-links {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 0.5rem;
    font-size: 0.8rem;
    font-weight: 700;
    border: 2px solid var(--black);
    border-radius: 2px;
    text-decoration: none;
    color: var(--black);
    background: var(--white);
    box-shadow: 2px 2px 0 var(--black);
    transition: transform 0.1s, box-shadow 0.1s, background 0.1s;
}

.page-btn:hover {
    transform: translate(-1px, -1px);
    box-shadow: 3px 3px 0 var(--black);
    background: var(--gray-50);
}

.page-btn.active {
    background: var(--black);
    color: var(--yellow);
    box-shadow: 2px 2px 0 var(--yellow);
}

.page-btn.disabled {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

/* ── Bulk selection ──────────────────────────────────────── */
.nb-checkbox-table {
    appearance: none;
    width: 16px; height: 16px;
    border: 2px solid var(--black);
    border-radius: 2px;
    background: var(--white);
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
    box-shadow: 1.5px 1.5px 0 var(--black);
    transition: background 0.1s;
    vertical-align: middle;
}

.nb-checkbox-table:checked {
    background: var(--black);
}

.nb-checkbox-table:checked::after {
    content: '';
    position: absolute;
    left: 2px; top: -1px;
    width: 5px; height: 9px;
    border-right: 2px solid var(--white);
    border-bottom: 2px solid var(--white);
    transform: rotate(45deg);
}

/* Bulk bar */
.bulk-bar {
    display: none;
    align-items: center;
    gap: 0.75rem;
    background: var(--black);
    border: 3px solid var(--yellow);
    border-radius: var(--radius);
    box-shadow: 5px 5px 0 var(--yellow);
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
}

.bulk-bar.show { display: flex; }

.bulk-count {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--white);
    font-family: var(--font-mono);
}

/* ── Delete confirm modal ────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10,10,10,0.6);
    z-index: 200;
    align-items: center;
    justify-content: center;
}

.modal-overlay.show { display: flex; }

.modal-box {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: 10px 10px 0 var(--black);
    padding: 2rem;
    width: 100%;
    max-width: 400px;
    margin: 1rem;
    animation: fadeUp 0.2s ease both;
}

.modal-box h3 {
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.modal-box p {
    font-size: 0.88rem;
    color: var(--gray-700);
    margin-bottom: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

/* ── Disability badge ────────────────────────────────────── */
.dis-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.1rem 0.35rem;
    border: 1.5px solid var(--red);
    border-radius: 2px;
    color: var(--red);
    background: #fff0f0;
    margin-left: 0.25rem;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 900px) {
    .index-toolbar { flex-direction: column; align-items: stretch; }
    .status-tabs { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 0.25rem; }
}

@media (max-width: 640px) {
    .hide-sm { display: none; }
}
</style>
@endpush

@section('content')

{{-- ══ PAGE HEADER ══════════════════════════════════════════ --}}
<div class="page-header anim-fade-up">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1 class="page-title">Daftar Calon Penerima</h1>
            <p class="page-subtitle">
                Lingkungan <strong>{{ Auth::user()->lingkungan?->name ?? '—' }}</strong>
                · <span class="text-mono">{{ $totalAll }}</span> total calon terdaftar
            </p>
        </div>
        <a href="{{ route('ketua-lingkungan-stasi.calons.create') }}"
           class="btn btn-primary"
           id="btn-add-calon"
           style="flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Tambah Calon
        </a>
    </div>
</div>

{{-- ══ FLASH MESSAGES ══════════════════════════════════════ --}}
@if(session('success'))
<div class="nb-alert alert-success anim-fade-up" id="flash-success" role="alert">
    <span>✓</span> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="nb-alert alert-error anim-fade-up" role="alert">
    <span>✕</span> {{ session('error') }}
</div>
@endif

{{-- ══ STATUS TABS ══════════════════════════════════════════ --}}
@php
    $tabConfig = [
        ''                   => ['label' => 'Semua',        'cls' => 'tab-all'],
        'draft'              => ['label' => 'Draft',         'cls' => 'tab-draft'],
        'submitted_to_stasi' => ['label' => 'Diajukan',      'cls' => 'tab-submitted'],
        'revision_requested' => ['label' => 'Revisi',        'cls' => 'tab-revision'],
        'approved_by_stasi'  => ['label' => 'Disetujui',     'cls' => 'tab-approved'],
        'sent_to_paroki'     => ['label' => 'Ke Paroki',     'cls' => 'tab-sent'],
        'rejected'           => ['label' => 'Ditolak',       'cls' => 'tab-rejected'],
    ];
@endphp

<div class="status-tabs anim-fade-up delay-1" role="tablist">
    @foreach($tabConfig as $tabStatus => $tabInfo)
    @php
        $isActive = ($status ?? '') === $tabStatus;
        $count    = $tabStatus === '' ? $totalAll : ($statusCounts[$tabStatus] ?? 0);
        $tabUrl   = route('ketua-lingkungan-stasi.calons.index', array_filter([
            'status'   => $tabStatus ?: null,
            'search'   => $search,
            'per_page' => $perPage != 10 ? $perPage : null,
        ]));
    @endphp
    <a href="{{ $tabUrl }}"
       class="status-tab {{ $tabInfo['cls'] }} {{ $isActive ? 'active' : '' }}"
       role="tab"
       aria-selected="{{ $isActive ? 'true' : 'false' }}">
        {{ $tabInfo['label'] }}
        @if($count > 0 || $tabStatus === '')
        <span class="tab-count">{{ $count }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- ══ SEARCH + FILTER TOOLBAR ════════════════════════════ --}}
<form action="{{ route('ketua-lingkungan-stasi.calons.index') }}"
      method="GET"
      id="filter-form">

    @if($status)
    <input type="hidden" name="status" value="{{ $status }}">
    @endif

    <div class="index-toolbar anim-fade-up delay-1">
        {{-- Search --}}
        <div class="search-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text"
                   name="search"
                   id="search-input"
                   value="{{ $search }}"
                   placeholder="Cari nama, NIK, atau alamat..."
                   autocomplete="off">
            @if($search)
            <a href="{{ route('ketua-lingkungan-stasi.calons.index', array_filter(['status' => $status])) }}"
               class="clear-btn" title="Hapus pencarian">✕</a>
            @endif
        </div>

        {{-- Per page --}}
        <select name="per_page"
                class="per-page-select"
                onchange="document.getElementById('filter-form').submit()"
                title="Jumlah per halaman">
            @foreach([10, 25, 50] as $pp)
            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} / hal</option>
            @endforeach
        </select>

        {{-- Search submit --}}
        <button type="submit" class="btn btn-dark btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Cari
        </button>

        @if($search || $status)
        <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="btn btn-outline btn-sm">
            Reset
        </a>
        @endif
    </div>
</form>

{{-- ══ BULK ACTION BAR ════════════════════════════════════ --}}
<div class="bulk-bar" id="bulk-bar">
    <div class="bulk-count">
        <span id="bulk-count-text">0</span> data terpilih
    </div>
    <button type="button" class="btn btn-primary btn-sm" id="btn-bulk-submit">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        Ajukan Batch ke Stasi
    </button>
</div>

{{-- ══ TABLE CARD ══════════════════════════════════════════ --}}
<div class="table-card anim-fade-up delay-2">

    {{-- Table Header --}}
    <div class="table-card-header">
        <div class="table-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--yellow)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Calon Penerima
            @if($search)
            <span style="font-size:0.7rem; color:var(--yellow); font-weight:600; font-family:var(--font-mono);">
                · hasil "{{ $search }}"
            </span>
            @endif
        </div>
        <span class="table-result-info">
            {{ $calons->total() }} data · hal {{ $calons->currentPage() }}/{{ $calons->lastPage() }}
        </span>
    </div>

    @if($calons->isEmpty())
    {{-- Empty State --}}
    <div class="empty-state">
        <div class="empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        @if($search || $status)
        <h3>Tidak ada hasil</h3>
        <p>Tidak ditemukan calon yang sesuai dengan filter yang diterapkan.</p>
        <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="btn btn-outline">
            Tampilkan Semua
        </a>
        @else
        <h3>Belum ada calon terdaftar</h3>
        <p>Mulai tambahkan calon penerima bantuan dari lingkungan Anda.</p>
        <a href="{{ route('ketua-lingkungan-stasi.calons.create') }}" class="btn btn-primary">
            + Tambah Calon Pertama
        </a>
        @endif
    </div>

    @else

    {{-- Data Table --}}
    <div style="overflow-x:auto;">
        <form action="{{ route('ketua-lingkungan-stasi.calons.submit-bulk') }}" method="POST" id="form-bulk-submit">
            @csrf
            <table class="nb-table" id="calon-table">
                <thead>
                    <tr>
                        <th style="width:36px; text-align:center;">
                            <input type="checkbox" class="nb-checkbox-table" id="check-all" title="Pilih semua data halaman ini">
                        </th>
                        <th style="width:42px; text-align:center;">#</th>
                    <th>
                        @include('ketua-lingkungan-stasi.calons._sort_th', [
                            'col' => 'name', 'label' => 'Nama / NIK',
                            'currentSort' => $sort, 'currentDir' => $direction,
                            'query' => ['search' => $search, 'status' => $status, 'per_page' => $perPage]
                        ])
                    </th>
                    <th class="hide-sm">
                        @include('ketua-lingkungan-stasi.calons._sort_th', [
                            'col' => 'monthly_income', 'label' => 'Penghasilan',
                            'currentSort' => $sort, 'currentDir' => $direction,
                            'query' => ['search' => $search, 'status' => $status, 'per_page' => $perPage]
                        ])
                    </th>
                    <th class="hide-sm" style="text-align:center;">
                        @include('ketua-lingkungan-stasi.calons._sort_th', [
                            'col' => 'dependents_count', 'label' => 'Tang.',
                            'currentSort' => $sort, 'currentDir' => $direction,
                            'query' => ['search' => $search, 'status' => $status, 'per_page' => $perPage]
                        ])
                    </th>
                    <th class="hide-sm">Periode</th>
                    <th>Status</th>
                    <th class="hide-sm">
                        @include('ketua-lingkungan-stasi.calons._sort_th', [
                            'col' => 'created_at', 'label' => 'Tgl. Input',
                            'currentSort' => $sort, 'currentDir' => $direction,
                            'query' => ['search' => $search, 'status' => $status, 'per_page' => $perPage]
                        ])
                    </th>
                    <th style="text-align:center; min-width:110px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calons as $i => $calon)
                @php
                    $rowNum = ($calons->currentPage() - 1) * $calons->perPage() + $i + 1;
                    $statusMap = [
                        'draft'              => ['label' => 'Draft',       'class' => 'pill-draft'],
                        'submitted_to_stasi' => ['label' => 'Diajukan',    'class' => 'pill-submitted'],
                        'revision_requested' => ['label' => 'Revisi',      'class' => 'pill-revision'],
                        'approved_by_stasi'  => ['label' => 'Disetujui',   'class' => 'pill-approved'],
                        'rejected'           => ['label' => 'Ditolak',     'class' => 'pill-rejected'],
                        'sent_to_paroki'     => ['label' => 'Ke Paroki',   'class' => 'pill-sent'],
                        'ranked'             => ['label' => 'Diranking',   'class' => 'pill-ranked'],
                    ];
                    $s = $statusMap[$calon->status] ?? ['label' => $calon->status, 'class' => 'pill-draft'];
                    $canEdit   = in_array($calon->status, ['draft', 'revision_requested']);
                    $canSubmit = in_array($calon->status, ['draft', 'revision_requested']);
                    $canDelete = $calon->status === 'draft';
                @endphp
                <tr id="row-{{ $calon->id }}">

                    {{-- Checkbox --}}
                    <td style="text-align:center;">
                        @if($canSubmit)
                        <input type="checkbox" name="ids[]" value="{{ $calon->id }}" class="nb-checkbox-table check-item">
                        @else
                        <input type="checkbox" class="nb-checkbox-table" disabled style="opacity:0.3; cursor:not-allowed;">
                        @endif
                    </td>

                    {{-- No --}}
                    <td class="calon-no">{{ $rowNum }}</td>

                    {{-- Nama / NIK --}}
                    <td>
                        <div class="calon-name-cell">
                            <div class="name">
                                <a href="{{ route('ketua-lingkungan-stasi.calons.show', $calon) }}"
                                   style="text-decoration:none; color:inherit;">
                                    {{ $calon->name }}
                                </a>
                                @if($calon->has_disability)
                                <span class="dis-tag">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><path d="M18 18.7c-.8.7-2 .7-2.8 0l-2.5-2.5c-.3-.3-.8-.5-1.3-.5H9v-3h1.5c.5 0 1 .2 1.4.6l1.1 1.1 1.7-3.5A2 2 0 0 0 12.8 8H9.5A2 2 0 0 0 8 9.2l-2 4.8H3v2h3l2-4h1.5v3H8v2.5c0 .8.3 1.5.9 2L11.2 22c.8.8 2 .8 2.8 0l4-4"/></svg>
                                    Difabel
                                </span>
                                @endif
                            </div>
                            <div class="nik">{{ $calon->nik }}</div>
                        </div>
                    </td>

                    {{-- Penghasilan --}}
                    <td class="hide-sm">
                        <div class="income-cell">
                            Rp {{ number_format($calon->monthly_income, 0, ',', '.') }}
                            <span class="income-label">per bulan</span>
                        </div>
                    </td>

                    {{-- Tanggungan --}}
                    <td class="hide-sm dep-cell">
                        {{ $calon->dependents_count }}
                        <span style="font-size:0.65rem; color:var(--gray-400); font-family:var(--font-sans);">org</span>
                    </td>

                    {{-- Periode --}}
                    <td class="hide-sm period-cell">
                        {{ $calon->periodeBantuan?->name ?? '—' }}
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="status-pill {{ $s['class'] }}">
                            <span class="pill-dot"></span>
                            {{ $s['label'] }}
                        </span>
                    </td>

                    {{-- Tanggal --}}
                    <td class="hide-sm" style="font-size:0.75rem; color:var(--gray-500); font-weight:600; white-space:nowrap;">
                        {{ $calon->created_at->format('d M Y') }}
                    </td>

                    {{-- Aksi --}}
                    <td>
                        <div class="action-group">

                            {{-- View --}}
                            <a href="{{ route('ketua-lingkungan-stasi.calons.show', $calon) }}"
                               class="tbl-btn tbl-btn-view"
                               title="Lihat detail">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>

                            {{-- Edit (hanya draft/revisi) --}}
                            @if($canEdit)
                            <a href="{{ route('ketua-lingkungan-stasi.calons.edit', $calon) }}"
                               class="tbl-btn tbl-btn-edit"
                               title="Edit calon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            @endif

                            {{-- Submit to Stasi (hanya draft/revisi) --}}
                            @if($canSubmit)
                            <form action="{{ route('ketua-lingkungan-stasi.calons.submit-to-stasi', $calon) }}"
                                  method="POST"
                                  class="inline-form submit-form"
                                  data-name="{{ $calon->name }}">
                                @csrf
                                <button type="button"
                                        class="tbl-btn tbl-btn-submit btn-trigger-submit"
                                        title="Ajukan ke Stasi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                </button>
                            </form>
                            @endif

                            {{-- Delete (hanya draft) --}}
                            @if($canDelete)
                            <button type="button"
                                    class="tbl-btn tbl-btn-delete btn-trigger-delete"
                                    data-id="{{ $calon->id }}"
                                    data-name="{{ $calon->name }}"
                                    data-action="{{ route('ketua-lingkungan-stasi.calons.destroy', $calon) }}"
                                    title="Hapus calon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6"/><path d="M14 11v6"/>
                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                            </button>
                            @endif

                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </form>
    </div>

    {{-- Pagination --}}
    @if($calons->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">
            Menampilkan {{ $calons->firstItem() }}–{{ $calons->lastItem() }} dari {{ $calons->total() }} data
        </div>
        <div class="pagination-links" role="navigation" aria-label="Navigasi halaman">
            {{-- Prev --}}
            @if($calons->onFirstPage())
            <span class="page-btn disabled" aria-disabled="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </span>
            @else
            <a href="{{ $calons->previousPageUrl() }}" class="page-btn" aria-label="Halaman sebelumnya">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            @endif

            {{-- Pages --}}
            @foreach($calons->getUrlRange(max(1, $calons->currentPage()-2), min($calons->lastPage(), $calons->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}"
               class="page-btn {{ $page == $calons->currentPage() ? 'active' : '' }}"
               aria-current="{{ $page == $calons->currentPage() ? 'page' : '' }}">
                {{ $page }}
            </a>
            @endforeach

            {{-- Next --}}
            @if($calons->hasMorePages())
            <a href="{{ $calons->nextPageUrl() }}" class="page-btn" aria-label="Halaman berikutnya">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @else
            <span class="page-btn disabled" aria-disabled="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
            @endif
        </div>
    </div>
    @endif

    @endif {{-- end if not empty --}}
</div>{{-- end table-card --}}

{{-- ══ DELETE CONFIRM MODAL ════════════════════════════════ --}}
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-box">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="width:40px; height:40px; background:var(--red); border:3px solid var(--black); border-radius:2px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                </svg>
            </div>
            <h3 id="modal-title" style="margin:0;">Hapus Calon?</h3>
        </div>
        <p id="modal-desc">
            Data calon <strong id="modal-name">—</strong> akan dihapus secara permanen dan tidak dapat dikembalikan.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="modal-cancel">Batal</button>
            <form id="delete-form" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-red" id="modal-confirm">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ══ SUBMIT TO STASI CONFIRM MODAL ═════════════════════ --}}
<div class="modal-overlay" id="submit-modal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="width:40px; height:40px; background:var(--blue); border:3px solid var(--black); border-radius:2px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </div>
            <h3 style="margin:0;">Ajukan ke Stasi?</h3>
        </div>
        <p>
            Calon <strong id="submit-modal-name">—</strong> akan diajukan ke Stasi untuk divalidasi.
            Status akan berubah menjadi <span class="status-pill pill-submitted" style="display:inline-flex;">Diajukan</span>.
        </p>
        <div class="nb-alert alert-warning" style="margin-bottom:1rem; font-size:0.82rem;">
            Pastikan data sudah lengkap dan benar sebelum mengajukan. Setelah diajukan, data hanya bisa diedit jika Stasi meminta revisi.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="submit-modal-cancel">Batal</button>
            <form id="submit-form" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Ya, Ajukan ke Stasi
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ══ BULK SUBMIT CONFIRM MODAL ═══════════════════════════ --}}
<div class="modal-overlay" id="bulk-modal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
            <div style="width:40px; height:40px; background:var(--blue); border:3px solid var(--black); border-radius:2px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </div>
            <h3 style="margin:0;">Ajukan Batch ke Stasi?</h3>
        </div>
        <p>
            Sebanyak <strong id="bulk-modal-count">0</strong> data calon akan diajukan ke Stasi sekaligus untuk divalidasi.
        </p>
        <div class="nb-alert alert-warning" style="margin-bottom:1rem; font-size:0.82rem;">
            Pastikan data sudah lengkap dan benar. Setelah diajukan, data hanya bisa diedit jika Stasi meminta revisi.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="bulk-modal-cancel">Batal</button>
            <button type="button" class="btn btn-blue" id="bulk-modal-confirm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                Ya, Ajukan Batch
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   INDEX — JS
   ═══════════════════════════════════════════════════════════ */

/* ── Delete Modal ────────────────────────────────────────── */
const deleteModal   = document.getElementById('delete-modal');
const deleteForm    = document.getElementById('delete-form');
const modalName     = document.getElementById('modal-name');
const modalCancel   = document.getElementById('modal-cancel');

document.querySelectorAll('.btn-trigger-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const name   = btn.dataset.name;
        const action = btn.dataset.action;
        modalName.textContent  = name;
        deleteForm.action      = action;
        deleteModal.classList.add('show');
    });
});

modalCancel.addEventListener('click', () => deleteModal.classList.remove('show'));
deleteModal.addEventListener('click', (e) => {
    if (e.target === deleteModal) deleteModal.classList.remove('show');
});

/* ── Submit Modal ────────────────────────────────────────── */
const submitModal       = document.getElementById('submit-modal');
const submitForm        = document.getElementById('submit-form');
const submitModalName   = document.getElementById('submit-modal-name');
const submitModalCancel = document.getElementById('submit-modal-cancel');

document.querySelectorAll('.btn-trigger-submit').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = btn.closest('.submit-form');
        const name = form.dataset.name;
        submitModalName.textContent = name;
        submitForm.action           = form.action;
        submitModal.classList.add('show');
    });
});

submitModalCancel.addEventListener('click', () => submitModal.classList.remove('show'));
submitModal.addEventListener('click', (e) => {
    if (e.target === submitModal) submitModal.classList.remove('show');
});

/* ── Bulk Selection ──────────────────────────────────────── */
const checkAll = document.getElementById('check-all');
const checkItems = document.querySelectorAll('.check-item');
const bulkBar = document.getElementById('bulk-bar');
const bulkCountText = document.getElementById('bulk-count-text');
const btnBulkSubmit = document.getElementById('btn-bulk-submit');

const bulkModal = document.getElementById('bulk-modal');
const bulkModalCount = document.getElementById('bulk-modal-count');
const bulkModalCancel = document.getElementById('bulk-modal-cancel');
const bulkModalConfirm = document.getElementById('bulk-modal-confirm');
const formBulkSubmit = document.getElementById('form-bulk-submit');

function updateBulkUI() {
    if (!checkItems.length) return;
    const checkedCount = document.querySelectorAll('.check-item:checked').length;
    
    // Update check-all state
    if (checkAll) {
        checkAll.checked = checkedCount > 0 && checkedCount === checkItems.length;
        checkAll.indeterminate = checkedCount > 0 && checkedCount < checkItems.length;
    }
    
    // Toggle bulk bar
    if (checkedCount > 0) {
        bulkCountText.textContent = checkedCount;
        bulkBar.classList.add('show');
    } else {
        bulkBar.classList.remove('show');
    }
}

if (checkAll) {
    checkAll.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        checkItems.forEach(item => { item.checked = isChecked; });
        updateBulkUI();
    });
}

checkItems.forEach(item => {
    item.addEventListener('change', updateBulkUI);
});

if (btnBulkSubmit && bulkModal) {
    btnBulkSubmit.addEventListener('click', () => {
        const count = document.querySelectorAll('.check-item:checked').length;
        bulkModalCount.textContent = count;
        bulkModal.classList.add('show');
    });
    
    bulkModalCancel.addEventListener('click', () => bulkModal.classList.remove('show'));
    bulkModal.addEventListener('click', (e) => {
        if (e.target === bulkModal) bulkModal.classList.remove('show');
    });
    
    bulkModalConfirm.addEventListener('click', () => {
        bulkModalConfirm.disabled = true;
        bulkModalConfirm.innerHTML = 'Memproses...';
        formBulkSubmit.submit();
    });
}

/* ── Keyboard: close modals on Escape ───────────────────── */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        deleteModal?.classList.remove('show');
        submitModal?.classList.remove('show');
        bulkModal?.classList.remove('show');
    }
});

/* ── Flash auto-hide ─────────────────────────────────────── */
const flash = document.getElementById('flash-success');
if (flash) {
    setTimeout(() => {
        flash.style.transition = 'opacity 0.5s';
        flash.style.opacity    = '0';
        setTimeout(() => flash.remove(), 500);
    }, 4000);
}

/* ── Live search (debounce) ──────────────────────────────── */
const searchInput = document.getElementById('search-input');
let searchTimer;

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        document.getElementById('filter-form').submit();
    }, 600);
});
</script>
@endpush
