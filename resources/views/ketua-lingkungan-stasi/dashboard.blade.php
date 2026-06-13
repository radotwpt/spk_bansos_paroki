@extends('layouts.app')

@section('title', 'Dashboard — Ketua Lingkungan')
@section('meta_description', 'Dashboard Ketua Lingkungan Stasi — pantau calon penerima bantuan sosial di lingkungan Anda.')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   DASHBOARD — Ketua Lingkungan Stasi
   ═══════════════════════════════════════════════════════════ */

/* ── Hero Welcome Strip ────────────────────────────────────── */
.dash-hero {
    background: var(--black);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    padding: 1.75rem 2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    position: relative;
    overflow: hidden;
}

.dash-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(245,197,24,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(245,197,24,0.05) 1px, transparent 1px);
    background-size: 32px 32px;
    pointer-events: none;
}

.dash-hero-left {
    position: relative;
    z-index: 1;
}

.dash-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--yellow);
    color: var(--black);
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 0.25rem 0.65rem;
    border-radius: 2px;
    margin-bottom: 0.65rem;
}

.dash-hero h2 {
    color: var(--white);
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.15;
    margin-bottom: 0.35rem;
}

.dash-hero h2 span {
    color: var(--yellow);
}

.dash-hero-sub {
    color: var(--gray-300);
    font-size: 0.85rem;
    font-weight: 500;
}

.dash-hero-right {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

.dash-hero-time {
    text-align: right;
}

.dash-hero-time .time-val {
    font-family: var(--font-mono);
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--yellow);
    line-height: 1;
}

.dash-hero-time .time-date {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-500);
    margin-top: 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ── Needs Action Banner ─────────────────────────────────── */
.needs-action-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: #fffbeb;
    border: 3px solid var(--yellow);
    border-radius: var(--radius);
    box-shadow: 5px 5px 0 var(--yellow);
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
}

.needs-action-banner.hidden { display: none; }

.banner-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.banner-icon {
    width: 40px;
    height: 40px;
    background: var(--yellow);
    border: 2px solid var(--black);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    animation: pulse-border 2s ease-in-out infinite;
}

@keyframes pulse-border {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245,197,24,0.5); }
    50%       { box-shadow: 0 0 0 6px rgba(245,197,24,0); }
}

.banner-text strong {
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--black);
    display: block;
}

.banner-text span {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--gray-700);
}

/* ── Stats Grid ─────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    padding: 1.4rem 1.4rem 1.4rem 1.75rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s, box-shadow 0.15s;
    text-decoration: none;
    display: block;
    color: inherit;
}

.stat-card:hover {
    transform: translate(-3px, -3px);
    box-shadow: var(--shadow-lg);
}

.stat-card .stat-accent {
    position: absolute;
    top: 0; left: 0;
    width: 6px;
    height: 100%;
}

.accent-yellow { background: var(--yellow); }
.accent-blue   { background: var(--blue); }
.accent-orange { background: var(--orange); }
.accent-lime   { background: var(--lime); }
.accent-red    { background: var(--red); }
.accent-teal   { background: var(--teal); }

.stat-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border: 2px solid var(--black);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-yellow { background: var(--yellow); }
.stat-icon-blue   { background: var(--blue);   color: white; }
.stat-icon-orange { background: var(--orange); color: white; }
.stat-icon-lime   { background: var(--lime);   }
.stat-icon-red    { background: var(--red);    color: white; }
.stat-icon-teal   { background: var(--teal);   }

.stat-number {
    font-size: 2.4rem;
    font-weight: 800;
    font-family: var(--font-mono);
    line-height: 1;
    color: var(--black);
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--gray-500);
    margin-top: 0.2rem;
}

.stat-change {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.72rem;
    font-weight: 700;
    margin-top: 0.5rem;
    padding: 0.15rem 0.5rem;
    border: 1.5px solid var(--black);
    border-radius: 2px;
}

/* ── Progress Section ────────────────────────────────────── */
.progress-section {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

/* Completion Ring Card */
.completion-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 1rem;
}

.ring-wrap {
    position: relative;
    width: 140px;
    height: 140px;
}

.ring-svg {
    transform: rotate(-90deg);
    width: 100%;
    height: 100%;
}

.ring-bg {
    fill: none;
    stroke: var(--gray-200);
    stroke-width: 10;
}

.ring-fill {
    fill: none;
    stroke: var(--yellow);
    stroke-width: 10;
    stroke-linecap: round;
    stroke-dasharray: 339.3; /* 2π × 54 */
    stroke-dashoffset: 339.3;
    transition: stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1);
}

.ring-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.ring-pct {
    font-size: 1.8rem;
    font-weight: 800;
    font-family: var(--font-mono);
    color: var(--black);
    line-height: 1;
}

.ring-pct-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gray-500);
    margin-top: 0.1rem;
}

.completion-title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--black);
    letter-spacing: -0.02em;
}

.completion-sub {
    font-size: 0.78rem;
    color: var(--gray-500);
    font-weight: 500;
}

/* Status Pipeline Card */
.pipeline-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    padding: 1.5rem;
}

.pipeline-card-title {
    font-size: 0.85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--black);
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pipeline-row {
    display: grid;
    grid-template-columns: 140px 1fr 48px;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
}

.pipeline-row:last-child { margin-bottom: 0; }

.pipeline-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--gray-700);
    white-space: nowrap;
}

.pipeline-bar-wrap {
    height: 18px;
    background: var(--gray-100);
    border: 2px solid var(--black);
    border-radius: 2px;
    overflow: hidden;
}

.pipeline-bar {
    height: 100%;
    border-radius: 1px;
    transition: width 1s cubic-bezier(.4,0,.2,1);
    width: 0%;
}

.pipeline-count {
    font-size: 0.85rem;
    font-weight: 800;
    font-family: var(--font-mono);
    text-align: right;
}

/* ── Activity Section ────────────────────────────────────── */
.activity-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

/* Recent Candidates Table Card */
.table-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.table-card-header {
    padding: 1rem 1.5rem;
    background: var(--black);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.table-card-header h3 {
    font-size: 0.85rem;
    font-weight: 800;
    color: var(--white);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.nb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.nb-table thead tr {
    background: var(--gray-50);
    border-top: 2px solid var(--black);
    border-bottom: 2px solid var(--black);
}

.nb-table th {
    padding: 0.6rem 1rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gray-700);
    border-right: 1px solid var(--gray-200);
}

.nb-table th:last-child { border-right: none; }

.nb-table td {
    padding: 0.7rem 1rem;
    border-bottom: 2px solid var(--gray-100);
    border-right: 1px solid var(--gray-100);
    font-weight: 500;
    vertical-align: middle;
}

.nb-table td:last-child { border-right: none; }

.nb-table tbody tr {
    transition: background 0.1s;
}

.nb-table tbody tr:hover {
    background: var(--gray-50);
}

.nb-table tbody tr:last-child td {
    border-bottom: none;
}

/* Inline Status Badge */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.55rem;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border: 2px solid var(--black);
    border-radius: 2px;
    white-space: nowrap;
}

.pill-draft        { background: var(--gray-200); color: var(--gray-700); }
.pill-submitted    { background: var(--yellow);   color: var(--black); }
.pill-revision     { background: var(--orange);   color: var(--white); }
.pill-approved     { background: var(--lime);     color: var(--black); }
.pill-rejected     { background: var(--red);      color: var(--white); }
.pill-sent         { background: var(--teal);     color: var(--black); }
.pill-ranked       { background: var(--blue);     color: var(--white); }

/* Empty State */
.empty-state {
    padding: 3rem 2rem;
    text-align: center;
    color: var(--gray-500);
}

.empty-state-icon {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    display: block;
}

.empty-state h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--gray-700);
    margin-bottom: 0.25rem;
}

.empty-state p {
    font-size: 0.82rem;
    font-weight: 500;
}

/* ── Quick Actions Card ──────────────────────────────────── */
.quick-actions-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.quick-actions-header {
    padding: 1rem 1.25rem;
    background: var(--black);
}

.quick-actions-header h3 {
    font-size: 0.85rem;
    font-weight: 800;
    color: var(--white);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1.25rem;
    border-bottom: 2px solid var(--gray-100);
    text-decoration: none;
    color: var(--black);
    transition: background 0.12s, transform 0.12s;
    font-weight: 600;
    font-size: 0.88rem;
}

.quick-action-item:last-child {
    border-bottom: none;
}

.quick-action-item:hover {
    background: var(--yellow);
    transform: translateX(4px);
}

.qa-icon {
    width: 34px;
    height: 34px;
    border: 2px solid var(--black);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.12s;
}

.qa-icon-yellow { background: var(--yellow); }
.qa-icon-blue   { background: var(--blue);   color: white; }
.qa-icon-lime   { background: var(--lime); }
.qa-icon-red    { background: var(--red);    color: white; }

.quick-action-item:hover .qa-icon {
    background: var(--black);
    color: var(--yellow);
}

.qa-arrow {
    margin-left: auto;
    color: var(--gray-300);
    transition: transform 0.12s, color 0.12s;
}

.quick-action-item:hover .qa-arrow {
    transform: translateX(4px);
    color: var(--black);
}

.qa-badge {
    margin-left: auto;
    background: var(--red);
    color: var(--white);
    font-size: 0.68rem;
    font-weight: 800;
    padding: 0.1rem 0.5rem;
    border: 2px solid var(--black);
    border-radius: 2px;
}

/* ── Candidate Name Cell ──────────────────────────────────── */
.calon-name-cell {
    font-weight: 700;
    color: var(--black);
    font-size: 0.88rem;
}

.calon-nik {
    font-size: 0.72rem;
    font-family: var(--font-mono);
    color: var(--gray-500);
    margin-top: 0.1rem;
}

/* ── Action Buttons in Table ─────────────────────────────── */
.tbl-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 2px solid var(--black);
    border-radius: 2px;
    background: var(--white);
    box-shadow: 2px 2px 0 var(--black);
    cursor: pointer;
    text-decoration: none;
    color: var(--black);
    transition: transform 0.1s, box-shadow 0.1s, background 0.1s;
}

.tbl-btn:hover {
    transform: translate(-1px, -1px);
    box-shadow: 3px 3px 0 var(--black);
}

.tbl-btn:active {
    transform: translate(1px, 1px);
    box-shadow: 0 0 0 var(--black);
}

.tbl-btn-view  { }
.tbl-btn-edit  { background: var(--yellow); }

/* ── Section Titles ──────────────────────────────────────── */
.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 16px;
    background: var(--yellow);
    border-radius: 1px;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 1100px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .progress-section { grid-template-columns: 1fr; }
    .activity-section { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .dash-hero { flex-direction: column; align-items: flex-start; }
    .dash-hero-right { align-self: flex-start; }
}
</style>
@endpush

@section('content')

{{-- ══ HERO WELCOME ═══════════════════════════════════════════ --}}
<div class="dash-hero anim-fade-up">
    <div class="dash-hero-left">
        <div class="dash-hero-tag">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="12" r="10"/>
            </svg>
            Sistem Aktif
        </div>
        <h2>Halo, <span>{{ Auth::user()->name }}</span></h2>
        <p class="dash-hero-sub">
            Lingkungan&nbsp;
            <strong style="color: var(--yellow); font-weight: 700;">
                {{ Auth::user()->lingkungan?->name ?? '—' }}
            </strong>
            &nbsp;·&nbsp;Ketua Lingkungan Stasi
        </p>
    </div>
    <div class="dash-hero-right">
        <div class="dash-hero-time">
            <div class="time-val" id="live-clock">--:--</div>
            <div class="time-date" id="live-date">---</div>
        </div>
    </div>
</div>

{{-- ══ NEEDS ACTION BANNER ════════════════════════════════════ --}}
@if($needsAction > 0)
<div class="needs-action-banner anim-fade-up delay-1">
    <div class="banner-left">
        <div class="banner-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="banner-text">
            <strong>{{ $needsAction }} calon membutuhkan tindakan segera</strong>
            <span>
                @if($draft > 0) {{ $draft }} draft belum diajukan @endif
                @if($draft > 0 && $revisionRequested > 0) · @endif
                @if($revisionRequested > 0) {{ $revisionRequested }} perlu revisi @endif
            </span>
        </div>
    </div>
    <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="btn btn-dark btn-sm">
        Lihat Sekarang
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </svg>
    </a>
</div>
@endif

{{-- ══ STAT CARDS ══════════════════════════════════════════════ --}}
<div class="stats-grid">

    {{-- Total --}}
    <div class="stat-card anim-fade-up delay-1">
        <div class="stat-accent accent-yellow"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number">{{ $totalCalon }}</div>
                <div class="stat-label">Total Calon</div>
            </div>
            <div class="stat-icon stat-icon-yellow">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: var(--gray-100);">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
            </svg>
            Semua periode
        </div>
    </div>

    {{-- Draft --}}
    <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'draft']) }}" class="stat-card anim-fade-up delay-2">
        <div class="stat-accent accent-orange"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number">{{ $draft }}</div>
                <div class="stat-label">Draft</div>
            </div>
            <div class="stat-icon stat-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: var(--orange); color: white; border-color: var(--orange);">
            Belum diajukan
        </div>
    </a>

    {{-- Submitted --}}
    <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'submitted_to_stasi']) }}" class="stat-card anim-fade-up delay-2">
        <div class="stat-accent accent-blue"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number">{{ $submittedToStasi }}</div>
                <div class="stat-label">Diajukan ke Stasi</div>
            </div>
            <div class="stat-icon stat-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: var(--blue); color: white; border-color: var(--blue);">
            Menunggu validasi
        </div>
    </a>

    {{-- Revision --}}
    <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'revision_requested']) }}" class="stat-card anim-fade-up delay-3">
        <div class="stat-accent" style="background: #ff7c2a;"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number" style="{{ $revisionRequested > 0 ? 'color: var(--red)' : '' }}">{{ $revisionRequested }}</div>
                <div class="stat-label">Perlu Revisi</div>
            </div>
            <div class="stat-icon" style="background: #fff0e0; border: 2px solid var(--black);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="{{ $revisionRequested > 0 ? 'background: var(--red); color: white; border-color: var(--red)' : 'background: var(--gray-100)' }}">
            {{ $revisionRequested > 0 ? '⚠ Segera ditangani' : '✓ Tidak ada' }}
        </div>
    </a>

    {{-- Approved --}}
    <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'approved_by_stasi']) }}" class="stat-card anim-fade-up delay-3">
        <div class="stat-accent accent-lime"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number">{{ $approvedByStasi }}</div>
                <div class="stat-label">Disetujui Stasi</div>
            </div>
            <div class="stat-icon stat-icon-lime">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: var(--lime); color: var(--black); border-color: var(--lime);">
            ✓ Lolos validasi
        </div>
    </a>

    {{-- Sent to Paroki --}}
    <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'sent_to_paroki']) }}" class="stat-card anim-fade-up delay-3">
        <div class="stat-accent accent-teal"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number">{{ $sentToParoki }}</div>
                <div class="stat-label">Dikirim ke Paroki</div>
            </div>
            <div class="stat-icon stat-icon-teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: var(--teal); color: var(--black); border-color: var(--teal);">
            Tahap akhir
        </div>
    </a>

    {{-- Rejected --}}
    <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'rejected']) }}" class="stat-card anim-fade-up delay-4">
        <div class="stat-accent accent-red"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number">{{ $rejected }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
            <div class="stat-icon stat-icon-red">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: var(--gray-100);">
            Tidak lolos
        </div>
    </a>

    {{-- Completion Rate --}}
    <div class="stat-card anim-fade-up delay-4" style="background: var(--black);">
        <div class="stat-accent" style="background: var(--yellow);"></div>
        <div class="stat-card-top">
            <div>
                <div class="stat-number" style="color: var(--yellow);">{{ $completionRate }}%</div>
                <div class="stat-label" style="color: var(--gray-500);">Tingkat Kelulusan</div>
            </div>
            <div class="stat-icon" style="background: var(--yellow); border-color: var(--yellow);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
        </div>
        <div class="stat-change" style="background: rgba(245,197,24,0.15); color: var(--yellow); border-color: var(--yellow);">
            Approved + Dikirim
        </div>
    </div>
</div>

{{-- ══ PROGRESS + PIPELINE ════════════════════════════════════ --}}
<div class="progress-section">

    {{-- Completion Ring --}}
    <div class="completion-card anim-fade-up delay-2">
        <div class="ring-wrap">
            <svg class="ring-svg" viewBox="0 0 120 120">
                <circle class="ring-bg" cx="60" cy="60" r="54"/>
                <circle class="ring-fill" id="progress-ring" cx="60" cy="60" r="54"
                    data-pct="{{ $completionRate }}"/>
            </svg>
            <div class="ring-center">
                <div class="ring-pct">{{ $completionRate }}%</div>
                <div class="ring-pct-label">Selesai</div>
            </div>
        </div>
        <div>
            <div class="completion-title">Progress Kelulusan</div>
            <div class="completion-sub" style="margin-top: 0.25rem;">
                {{ $approvedByStasi + $sentToParoki }} dari {{ $totalCalon }} calon lolos seleksi
            </div>
        </div>
        <a href="{{ route('ketua-lingkungan-stasi.calons.create') }}" class="btn btn-primary" style="width: 100%; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Tambah Calon Baru
        </a>
    </div>

    {{-- Status Pipeline --}}
    <div class="pipeline-card anim-fade-up delay-3">
        <div class="pipeline-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            Pipeline Status Calon
        </div>

        @php
            $total = max($totalCalon, 1);
            $pipeline = [
                ['label' => 'Draft',           'count' => $draft,             'color' => 'var(--gray-300)',   'pct' => round($draft/$total*100)],
                ['label' => 'Diajukan Stasi',  'count' => $submittedToStasi,  'color' => 'var(--blue)',       'pct' => round($submittedToStasi/$total*100)],
                ['label' => 'Perlu Revisi',    'count' => $revisionRequested, 'color' => 'var(--orange)',     'pct' => round($revisionRequested/$total*100)],
                ['label' => 'Disetujui Stasi', 'count' => $approvedByStasi,   'color' => 'var(--lime)',       'pct' => round($approvedByStasi/$total*100)],
                ['label' => 'Dikirim Paroki',  'count' => $sentToParoki,      'color' => 'var(--teal)',       'pct' => round($sentToParoki/$total*100)],
                ['label' => 'Ditolak',         'count' => $rejected,          'color' => 'var(--red)',        'pct' => round($rejected/$total*100)],
            ];
        @endphp

        @foreach($pipeline as $row)
        <div class="pipeline-row">
            <span class="pipeline-label">{{ $row['label'] }}</span>
            <div class="pipeline-bar-wrap">
                <div class="pipeline-bar"
                     data-width="{{ $row['pct'] }}"
                     style="background: {{ $row['color'] }};">
                </div>
            </div>
            <span class="pipeline-count">{{ $row['count'] }}</span>
        </div>
        @endforeach

        @if($totalCalon === 0)
        <div style="text-align: center; padding: 1.5rem 0; color: var(--gray-500); font-size: 0.82rem; font-weight: 500;">
            Belum ada data calon penerima.
        </div>
        @endif
    </div>
</div>

{{-- ══ ACTIVITY — TABLE + QUICK ACTIONS ═══════════════════════ --}}
<div class="activity-section">

    {{-- Recent Candidates Table --}}
    <div class="table-card anim-fade-up delay-3">
        <div class="table-card-header">
            <h3>Calon Terbaru</h3>
            <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="btn btn-primary btn-sm">
                Lihat Semua
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

        @if($calons->isEmpty())
        <div class="empty-state">
            <span class="empty-state-icon">📋</span>
            <h4>Belum Ada Calon</h4>
            <p>Mulai tambahkan calon penerima bantuan pertama Anda.</p>
            <a href="{{ route('ketua-lingkungan-stasi.calons.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                + Tambah Calon
            </a>
        </div>
        @else
        <table class="nb-table">
            <thead>
                <tr>
                    <th>Nama / NIK</th>
                    <th>Penghasilan</th>
                    <th>Status</th>
                    <th>Tgl. Input</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calons as $calon)
                <tr>
                    <td>
                        <div class="calon-name-cell">{{ $calon->name }}</div>
                        <div class="calon-nik">{{ $calon->nik ?? '—' }}</div>
                    </td>
                    <td>
                        <span class="text-mono" style="font-size: 0.82rem;">
                            Rp {{ number_format($calon->monthly_income ?? 0, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusMap = [
                                'draft'             => ['label' => 'Draft',          'class' => 'pill-draft'],
                                'submitted_to_stasi'=> ['label' => 'Diajukan',       'class' => 'pill-submitted'],
                                'revision_requested'=> ['label' => 'Revisi',         'class' => 'pill-revision'],
                                'approved_by_stasi' => ['label' => 'Disetujui',      'class' => 'pill-approved'],
                                'rejected'          => ['label' => 'Ditolak',        'class' => 'pill-rejected'],
                                'sent_to_paroki'    => ['label' => 'Ke Paroki',      'class' => 'pill-sent'],
                                'ranked'            => ['label' => 'Diranking',      'class' => 'pill-ranked'],
                            ];
                            $s = $statusMap[$calon->status] ?? ['label' => $calon->status, 'class' => 'pill-draft'];
                        @endphp
                        <span class="status-pill {{ $s['class'] }}">{{ $s['label'] }}</span>
                    </td>
                    <td style="font-size: 0.78rem; color: var(--gray-500); font-weight: 500;">
                        {{ $calon->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.35rem;">
                            <a href="{{ route('ketua-lingkungan-stasi.calons.show', $calon) }}"
                               class="tbl-btn tbl-btn-view"
                               title="Lihat detail">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            @if(in_array($calon->status, ['draft', 'revision_requested']))
                            <a href="{{ route('ketua-lingkungan-stasi.calons.edit', $calon) }}"
                               class="tbl-btn tbl-btn-edit"
                               title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div class="anim-fade-up delay-4" style="display: flex; flex-direction: column; gap: 1.25rem;">

        <div class="quick-actions-card">
            <div class="quick-actions-header">
                <h3>Aksi Cepat</h3>
            </div>

            <a href="{{ route('ketua-lingkungan-stasi.calons.create') }}" class="quick-action-item" id="qa-create">
                <div class="qa-icon qa-icon-yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.88rem;">Tambah Calon Baru</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">Daftarkan calon penerima baru</div>
                </div>
                <svg class="qa-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>

            <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="quick-action-item" id="qa-list">
                <div class="qa-icon qa-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.88rem;">Daftar Semua Calon</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">Kelola & pantau status</div>
                </div>
                <svg class="qa-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>

            @if($draft > 0)
            <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'draft']) }}" class="quick-action-item" id="qa-draft">
                <div class="qa-icon qa-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.88rem;">Draft Belum Diajukan</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">{{ $draft }} calon menunggu pengajuan</div>
                </div>
                <span class="qa-badge">{{ $draft }}</span>
            </a>
            @endif

            @if($revisionRequested > 0)
            <a href="{{ route('ketua-lingkungan-stasi.calons.index', ['status' => 'revision_requested']) }}" class="quick-action-item">
                <div class="qa-icon" style="background: var(--orange); border: 2px solid var(--black);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.88rem;">Perlu Revisi</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">{{ $revisionRequested }} calon perlu diperbaiki</div>
                </div>
                <span class="qa-badge" style="background: var(--orange);">{{ $revisionRequested }}</span>
            </a>
            @endif

        </div>

        {{-- Info Card --}}
        <div class="nb-card" style="background: var(--black); border-color: var(--yellow); box-shadow: 5px 5px 0 var(--yellow);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                <div style="width: 28px; height: 28px; background: var(--yellow); border: 2px solid var(--yellow); border-radius: 2px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--yellow);">Alur Pengajuan</span>
            </div>
            <ol style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                @php
                    $steps = [
                        ['Draft',             'var(--gray-300)'],
                        ['Ajukan ke Stasi',   'var(--blue)'],
                        ['Validasi Stasi',    'var(--teal)'],
                        ['Kirim ke Paroki',   'var(--lime)'],
                        ['Keputusan Paroki',  'var(--yellow)'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                <li style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="width: 20px; height: 20px; background: {{ $step[1] }}; border: 2px solid var(--black); border-radius: 1px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; color: var(--black); flex-shrink: 0;">{{ $i + 1 }}</span>
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--gray-300);">{{ $step[0] }}</span>
                </li>
                @endforeach
            </ol>
        </div>

    </div>{{-- end right column --}}
</div>{{-- end activity-section --}}

@endsection

@push('scripts')
<script>
/* ── Live Clock ──────────────────────────────────────────── */
function updateClock() {
    const now = new Date();
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    const hh = String(now.getHours()).padStart(2,'0');
    const mm = String(now.getMinutes()).padStart(2,'0');
    const ss = String(now.getSeconds()).padStart(2,'0');

    const clockEl = document.getElementById('live-clock');
    const dateEl  = document.getElementById('live-date');
    if (clockEl) clockEl.textContent = `${hh}:${mm}:${ss}`;
    if (dateEl) dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
}
updateClock();
setInterval(updateClock, 1000);

/* ── Progress Ring Animation ─────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const ring = document.getElementById('progress-ring');
    if (ring) {
        const pct      = parseFloat(ring.dataset.pct) || 0;
        const circumf  = 2 * Math.PI * 54; // r=54
        const offset   = circumf - (pct / 100) * circumf;
        setTimeout(() => {
            ring.style.strokeDashoffset = offset;
        }, 300);
    }

    /* ── Pipeline Bars ─────────────────────────────────────── */
    document.querySelectorAll('.pipeline-bar').forEach(bar => {
        const w = bar.dataset.width || 0;
        setTimeout(() => {
            bar.style.width = w + '%';
        }, 400);
    });
});
</script>
@endpush
