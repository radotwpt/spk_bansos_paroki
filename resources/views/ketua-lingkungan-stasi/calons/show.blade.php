@extends('layouts.app')

@section('title', 'Detail Calon — ' . $calonPenerima->name)
@section('meta_description', 'Detail lengkap calon penerima bantuan sosial: ' . $calonPenerima->name)

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   SHOW — Detail Calon Penerima · Neobrutalism
   ═══════════════════════════════════════════════════════════ */

.detail-wrap { max-width: 960px; }

/* ── Hero Card ───────────────────────────────────────────── */
.hero-card {
    background: var(--black);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    margin-bottom: 1.5rem;
    position: relative;
}

.hero-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(245,197,24,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(245,197,24,.04) 1px, transparent 1px);
    background-size: 28px 28px;
    pointer-events: none;
}

.hero-body {
    padding: 1.75rem 2rem;
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}

.hero-avatar {
    width: 64px; height: 64px;
    background: var(--yellow);
    border: 3px solid var(--yellow);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--black);
    flex-shrink: 0;
    text-transform: uppercase;
    font-family: var(--font-mono);
}

.hero-info { flex: 1; }

.hero-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--white);
    letter-spacing: -0.03em;
    line-height: 1.15;
    margin-bottom: 0.35rem;
}

.hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.75rem;
}

.hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.18rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 700;
    border: 1.5px solid var(--gray-500);
    border-radius: 2px;
    color: yellow;
    font-family: var(--font-mono);
}

.hero-tag.nik-tag { color: var(--yellow); border-color: rgba(245,197,24,.4); }

.hero-reg {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--gray-500);
    font-family: var(--font-mono);
    margin-top: 0.25rem;
}

.hero-actions {
    display: flex;
    gap: 0.6rem;
    flex-shrink: 0;
    flex-wrap: wrap;
    align-items: flex-start;
    position: relative;
    z-index: 1;
}

/* ── Status Trail ────────────────────────────────────────── */
.status-trail {
    background: rgba(255,255,255,.04);
    border-top: 1px solid rgba(255,255,255,.08);
    padding: 0.85rem 2rem;
    display: flex;
    align-items: center;
    gap: 0;
    overflow-x: auto;
    position: relative;
    z-index: 1;
}

.trail-step {
    display: flex;
    align-items: center;
    gap: 0;
    flex-shrink: 0;
}

.trail-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
    min-width: 80px;
}

.trail-dot {
    width: 28px; height: 28px;
    border-radius: 2px;
    border: 2px solid var(--gray-600);
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.trail-dot.done  { background: var(--lime);   border-color: var(--lime); }
.trail-dot.active{ background: var(--yellow); border-color: var(--yellow); box-shadow: 0 0 0 3px rgba(245,197,24,.3); }
.trail-dot.error { background: var(--red);    border-color: var(--red); }

.trail-label {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: white;
    text-align: center;
    line-height: 1.2;
    max-width: 72px;
}

.trail-label.done   { color: var(--lime); }
.trail-label.active { color: var(--yellow); }
.trail-label.error  { color: var(--red); }

.trail-arrow {
    width: 28px; height: 2px;
    background: var(--gray-700);
    flex-shrink: 0;
    margin-bottom: 14px;
    position: relative;
}

.trail-arrow.done {
    background: var(--lime);
}

.trail-arrow::after {
    content: '';
    position: absolute;
    right: -4px;
    top: -3px;
    width: 0; height: 0;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-left: 5px solid inherit;
}

/* ── Section Grid ────────────────────────────────────────── */
.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.detail-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

/* ── Detail Card ─────────────────────────────────────────── */
.detail-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.detail-card-header {
    background: var(--black);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.detail-card-header h3 {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--white);
}

.detail-card-icon {
    width: 24px; height: 24px;
    background: var(--yellow);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.detail-card-body { padding: 1.25rem; }

/* ── Field Row ───────────────────────────────────────────── */
.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem 1.25rem;
}

.field-group { display: flex; flex-direction: column; gap: 0.2rem; }
.field-group.full { grid-column: span 2; }

.field-label {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gray-400);
}

.field-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--black);
    word-break: break-word;
}

.field-value.mono { font-family: var(--font-mono); font-size: 0.88rem; }
.field-value.big  { font-size: 1.1rem; font-weight: 800; }

.field-empty {
    font-size: 0.85rem;
    color: var(--gray-300);
    font-style: italic;
    font-weight: 400;
}

/* ── Score Badge ─────────────────────────────────────────── */
.score-display {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.score-box {
    width: 36px; height: 36px;
    border: 2.5px solid var(--black);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: 800;
    font-family: var(--font-mono);
    box-shadow: 2px 2px 0 var(--black);
}

.score-1 { background: var(--lime); color: var(--black); }
.score-2 { background: var(--yellow); color: var(--black); }
.score-3 { background: var(--orange); color: var(--white); }
.score-4 { background: var(--red); color: var(--white); }

.score-desc {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--gray-700);
}

/* ── SAW Score Card ──────────────────────────────────────── */
.saw-card {
    background: var(--black);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: 5px 5px 0 var(--yellow);
    padding: 1.5rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.saw-score-big {
    font-size: 2.8rem;
    font-weight: 800;
    font-family: var(--font-mono);
    color: var(--yellow);
    line-height: 1;
}

.saw-score-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--gray-500);
}

.saw-rank {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(245,197,24,.1);
    border: 1.5px solid var(--yellow);
    border-radius: 2px;
    padding: 0.25rem 0.65rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--yellow);
    font-family: var(--font-mono);
}

/* ── Notes Section ───────────────────────────────────────── */
.note-block {
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 0.85rem 1rem;
    font-size: 0.88rem;
    font-weight: 500;
    color: var(--gray-700);
    line-height: 1.65;
    white-space: pre-wrap;
    word-break: break-word;
}

.note-block.warn {
    background: #fff9e6;
    border-color: var(--yellow);
}

.note-block.danger {
    background: #fff0f0;
    border-color: var(--red);
}

.note-block.info {
    background: #eef2ff;
    border-color: var(--blue);
}

/* ── Disability Banner ───────────────────────────────────── */
.disability-banner {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    background: #fff0f0;
    border: 3px solid var(--red);
    border-radius: var(--radius);
    box-shadow: 4px 4px 0 var(--red);
    padding: 0.85rem 1.25rem;
    margin-bottom: 1.25rem;
}

.disability-banner-icon {
    width: 36px; height: 36px;
    background: var(--red);
    border: 2px solid var(--black);
    border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* ── Timeline / Activity Log ─────────────────────────────── */
.timeline { display: flex; flex-direction: column; gap: 0; }

.timeline-item {
    display: grid;
    grid-template-columns: 28px 1fr;
    gap: 0 0.85rem;
    position: relative;
}

.timeline-item:not(:last-child) .tl-line {
    display: block;
}

.tl-dot-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}

.tl-dot {
    width: 28px; height: 28px;
    border: 2.5px solid var(--black);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--white);
}

.tl-dot.created  { background: var(--yellow); }
.tl-dot.submitted{ background: var(--blue); color: white; }
.tl-dot.revision { background: var(--orange); color: white; }
.tl-dot.approved { background: var(--lime); }
.tl-dot.rejected { background: var(--red); color: white; }
.tl-dot.sent     { background: var(--teal); }

.tl-line {
    display: none;
    width: 2px;
    flex: 1;
    background: var(--gray-200);
    min-height: 20px;
}

.tl-content {
    padding-bottom: 1.25rem;
}

.tl-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.2rem;
}

.tl-action {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--black);
}

.tl-time {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--gray-400);
    font-family: var(--font-mono);
    white-space: nowrap;
    flex-shrink: 0;
}

.tl-actor {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--gray-500);
}

.tl-note {
    margin-top: 0.4rem;
    font-size: 0.8rem;
    color: var(--gray-700);
    background: var(--gray-50);
    border: 1.5px solid var(--gray-200);
    border-radius: 2px;
    padding: 0.5rem 0.65rem;
    line-height: 1.55;
}

/* ── Quick Info strip (top of side col) ─────────────────── */
.quick-info-strip {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.qi-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.65rem 0.85rem;
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: 2px;
}

.qi-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--gray-500);
}

.qi-value {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--black);
    text-align: right;
}

/* ── Status badge big ────────────────────────────────────── */
.status-badge-big {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2.5px solid var(--black);
    border-radius: 2px;
    box-shadow: 3px 3px 0 var(--black);
}

/* ── Action Sidebar ──────────────────────────────────────── */
.action-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 1.25rem;
}

.action-card-header {
    background: var(--black);
    padding: 0.75rem 1.25rem;
}

.action-card-header h3 {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--white);
}

.action-btn-block {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.action-btn-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 1.25rem;
    border-bottom: 2px solid var(--gray-100);
    text-decoration: none;
    color: var(--black);
    font-size: 0.88rem;
    font-weight: 700;
    transition: background 0.1s, padding-left 0.15s;
    cursor: pointer;
    background: none;
    border-left: none;
    border-right: none;
    border-top: none;
    border-radius: 0;
    width: 100%;
    text-align: left;
}

.action-btn-item:last-child { border-bottom: none; }

.action-btn-item:hover { background: var(--yellow); padding-left: 1.5rem; }
.action-btn-item.danger:hover { background: #fff0f0; }

.action-btn-item .ab-icon {
    width: 30px; height: 30px;
    border: 2px solid var(--black);
    border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background 0.1s;
}

.action-btn-item:hover .ab-icon { background: var(--black); color: var(--yellow); }
.action-btn-item.danger:hover .ab-icon { background: var(--red); color: var(--white); border-color: var(--red); }

.action-btn-icon-edit   { background: var(--yellow); }
.action-btn-icon-submit { background: var(--blue);   color: white; }
.action-btn-icon-delete { background: var(--red);    color: white; }

/* ── Breadcrumb ──────────────────────────────────────────── */
.breadcrumb {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.8rem; font-weight: 600; color: var(--gray-500);
    margin-bottom: 1.25rem;
}
.breadcrumb a { color: var(--blue); text-decoration: underline; text-underline-offset: 2px; }
.breadcrumb-sep { color: var(--gray-300); }

/* ── Delete modal ────────────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(10,10,10,.6); z-index: 200;
    align-items: center; justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: 10px 10px 0 var(--black);
    padding: 2rem; width: 100%; max-width: 400px;
    margin: 1rem;
    animation: fadeUp 0.2s ease both;
}
.modal-box h3 { font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem; }
.modal-box p  { font-size: 0.88rem; color: var(--gray-700); margin-bottom: 1.5rem; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 900px) {
    .detail-grid   { grid-template-columns: 1fr; }
    .detail-grid-3 { grid-template-columns: 1fr 1fr; }
    .hero-body     { flex-direction: column; }
    .status-trail  { gap: 0; }
}
@media (max-width: 600px) {
    .detail-grid-3 { grid-template-columns: 1fr; }
    .field-grid    { grid-template-columns: 1fr; }
    .field-group.full { grid-column: span 1; }
}
</style>
@endpush

@section('content')

@php
$cp = $calonPenerima;

$statusMap = [
    'draft'              => ['label' => 'Draft',        'class' => 'pill-draft',     'bg' => 'var(--gray-200)',  'color' => 'var(--gray-700)'],
    'submitted_to_stasi' => ['label' => 'Diajukan',     'class' => 'pill-submitted', 'bg' => 'var(--blue)',     'color' => 'white'],
    'revision_requested' => ['label' => 'Perlu Revisi', 'class' => 'pill-revision',  'bg' => 'var(--orange)',   'color' => 'white'],
    'approved_by_stasi'  => ['label' => 'Disetujui',    'class' => 'pill-approved',  'bg' => 'var(--lime)',     'color' => 'var(--black)'],
    'rejected'           => ['label' => 'Ditolak',      'class' => 'pill-rejected',  'bg' => 'var(--red)',      'color' => 'white'],
    'sent_to_paroki'     => ['label' => 'Ke Paroki',    'class' => 'pill-sent',      'bg' => 'var(--teal)',     'color' => 'var(--black)'],
    'ranked'             => ['label' => 'Diranking',    'class' => 'pill-ranked',    'bg' => 'var(--blue)',     'color' => 'white'],
];
$s = $statusMap[$cp->status] ?? ['label' => $cp->status, 'class' => 'pill-draft', 'bg' => 'var(--gray-200)', 'color' => 'var(--gray-700)'];

$canEdit   = in_array($cp->status, ['draft', 'revision_requested']);
$canSubmit = in_array($cp->status, ['draft', 'revision_requested']);
$canDelete = $cp->status === 'draft';

$housingLabels = [
    'milik_sendiri' => 'Milik Sendiri',
    'kontrak'       => 'Kontrak / Sewa',
    'menumpang'     => 'Menumpang',
    'tidak_tetap'   => 'Tidak Tetap',
];

$genderLabels = ['laki_laki' => 'Laki-laki', 'perempuan' => 'Perempuan'];

// Status trail
$trail = [
    ['key' => 'draft',              'label' => "Draft"],
    ['key' => 'submitted_to_stasi', 'label' => "Diajukan\nStasi"],
    ['key' => 'approved_by_stasi',  'label' => "Disetujui\nStasi"],
    ['key' => 'sent_to_paroki',     'label' => "Ke\nParoki"],
    ['key' => 'ranked',             'label' => "Diranking"],
];
$statusOrder = array_column($trail, 'key');
$currentIdx  = array_search($cp->status, $statusOrder);
$isRejected  = $cp->status === 'rejected';
@endphp

<div class="detail-wrap">

{{-- Breadcrumb --}}
<nav class="breadcrumb anim-fade-up">
    <a href="{{ route('ketua-lingkungan.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}">Daftar Calon</a>
    <span class="breadcrumb-sep">›</span>
    <span style="color:var(--black);font-weight:700;">{{ $cp->name }}</span>
</nav>

{{-- Flash --}}
@if(session('success'))
<div class="nb-alert alert-success anim-fade-up" id="flash-msg">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="nb-alert alert-error anim-fade-up">✕ {{ session('error') }}</div>
@endif

{{-- ══ HERO CARD ══════════════════════════════════════════ --}}
<div class="hero-card anim-fade-up">
    <div class="hero-body">
        <div class="hero-avatar">{{ mb_substr($cp->name, 0, 2) }}</div>
        <div class="hero-info">
            <div class="hero-name">{{ $cp->name }}</div>
            <div class="hero-meta">
                <span class="hero-tag nik-tag">NIK {{ $cp->nik }}</span>
                <span class="hero-tag">KK {{ $cp->nomor_kk }}</span>
                @if($cp->gender)
                <span class="hero-tag">{{ $genderLabels[$cp->gender] ?? $cp->gender }}</span>
                @endif
                @if($cp->has_disability)
                <span class="hero-tag" style="color:var(--red);border-color:rgba(255,60,60,.4);">
                    ♿ Difabel
                </span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
                <span class="status-badge-big" style="background:{{ $s['bg'] }};color:{{ $s['color'] }};">
                    {{ $s['label'] }}
                </span>
                @if($cp->registration_number)
                <span class="hero-reg">No. Reg: {{ $cp->registration_number }}</span>
                @endif
            </div>
        </div>
        <div class="hero-actions">
            @if($canEdit)
            <a href="{{ route('ketua-lingkungan-stasi.calons.edit', $cp) }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
            @endif
            <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="btn btn-outline btn-sm" style="border-color:var(--gray-600);color:var(--gray-400);">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- Status Trail --}}
    <div class="status-trail">
        @foreach($trail as $i => $step)
        @php
            $stepIdx = $i;
            if ($isRejected) {
                $stepState = ($stepIdx < $currentIdx) ? 'done' : (($stepIdx === $currentIdx) ? 'error' : 'pending');
            } else {
                $stepState = ($stepIdx < $currentIdx) ? 'done' : (($stepIdx === $currentIdx) ? 'active' : 'pending');
            }
        @endphp
        <div class="trail-step">
            <div class="trail-node">
                <div class="trail-dot {{ $stepState }}">
                    @if($stepState === 'done')
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @elseif($stepState === 'active')
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="var(--black)"><circle cx="12" cy="12" r="6"/></svg>
                    @elseif($stepState === 'error')
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    @else
                    <span style="font-size:0.7rem;font-weight:700;color:white;font-family:var(--font-mono);">{{ $i+1 }}</span>
                    @endif
                </div>
                <span class="trail-label {{ $stepState }}" style="white-space:pre-line;">{{ $step['label'] }}</span>
            </div>
            @if($i < count($trail) - 1)
            <div class="trail-arrow {{ $stepState === 'done' ? 'done' : '' }}"></div>
            @endif
        </div>
        @endforeach

        @if($isRejected)
        <div class="trail-step">
            <div class="trail-node">
                <div class="trail-dot error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </div>
                <span class="trail-label error">Ditolak</span>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ══ DISABILITY BANNER ══════════════════════════════════ --}}
@if($cp->has_disability)
<div class="disability-banner anim-fade-up delay-1">
    <div class="disability-banner-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="5" r="2"/>
            <path d="M19 19H13L10.5 12M7 12l1.5 7H5M10.5 12L9 7"/>
        </svg>
    </div>
    <div>
        <strong style="font-size:0.9rem;font-weight:800;color:var(--red);display:block;">Calon Penyandang Disabilitas</strong>
        <span style="font-size:0.8rem;color:var(--gray-700);">Skor disabilitas: <strong>{{ $cp->disability_score }}</strong>
            @if($cp->disability_note) · {{ $cp->disability_note }} @endif
        </span>
    </div>
</div>
@endif

{{-- ══ REVISION BANNER ════════════════════════════════════ --}}
@if($cp->status === 'revision_requested' && $cp->stasi_validation_note)
<div class="nb-alert alert-warning anim-fade-up delay-1" style="margin-bottom:1.25rem;">
    <strong style="display:block;margin-bottom:0.35rem;">⚠ Catatan Revisi dari Stasi</strong>
    <span style="font-size:0.88rem;white-space:pre-wrap;">{{ $cp->stasi_validation_note }}</span>
</div>
@endif

{{-- ══ MAIN GRID ══════════════════════════════════════════ --}}
<div class="detail-grid">

    {{-- ── LEFT COLUMN ── --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Identitas Pribadi --}}
        <div class="detail-card anim-fade-up delay-1">
            <div class="detail-card-header">
                <div class="detail-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h3>Identitas Pribadi</h3>
            </div>
            <div class="detail-card-body">
                <div class="field-grid">
                    <div class="field-group full">
                        <span class="field-label">Nama Lengkap</span>
                        <span class="field-value big">{{ $cp->name }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">NIK</span>
                        <span class="field-value mono">{{ $cp->nik }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Nomor KK</span>
                        <span class="field-value mono">{{ $cp->nomor_kk }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Kepala Keluarga</span>
                        <span class="field-value">{{ $cp->family_head_name ?: '—' }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Jenis Kelamin</span>
                        <span class="field-value">{{ $genderLabels[$cp->gender] ?? '—' }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Tempat Lahir</span>
                        <span class="field-value">{{ $cp->place_of_birth ?: '—' }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Tanggal Lahir</span>
                        <span class="field-value">
                            @if($cp->date_of_birth)
                                {{ $cp->date_of_birth->format('d M Y') }}
                                <span style="font-size:0.75rem;color:var(--gray-500);margin-left:0.3rem;">
                                    ({{ $cp->date_of_birth->age }} tahun)
                                </span>
                            @else —
                            @endif
                        </span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">No. Telepon</span>
                        <span class="field-value mono">{{ $cp->phone ?: '—' }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Pekerjaan</span>
                        <span class="field-value">{{ $cp->occupation ?: '—' }}</span>
                    </div>
                    <div class="field-group full">
                        <span class="field-label">Alamat</span>
                        <span class="field-value" style="white-space:pre-line;">{{ $cp->address }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Ekonomi --}}
        <div class="detail-card anim-fade-up delay-2">
            <div class="detail-card-header">
                <div class="detail-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/></svg>
                </div>
                <h3>Data Ekonomi</h3>
            </div>
            <div class="detail-card-body">
                <div class="field-grid">
                    <div class="field-group">
                        <span class="field-label">Penghasilan Bulanan</span>
                        <span class="field-value big" style="color:var(--black);">
                            Rp {{ number_format($cp->monthly_income, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Jumlah Tanggungan</span>
                        <span class="field-value big">{{ $cp->dependents_count }} <span style="font-size:0.8rem;font-weight:600;color:var(--gray-500);">orang</span></span>
                    </div>
                    @if($cp->economic_condition_note)
                    <div class="field-group full">
                        <span class="field-label">Catatan Kondisi Ekonomi</span>
                        <div class="note-block">{{ $cp->economic_condition_note }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Tempat Tinggal --}}
        <div class="detail-card anim-fade-up delay-2">
            <div class="detail-card-header">
                <div class="detail-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h3>Status Tempat Tinggal</h3>
            </div>
            <div class="detail-card-body">
                <div class="field-grid">
                    <div class="field-group">
                        <span class="field-label">Status Kepemilikan</span>
                        <span class="field-value">{{ $housingLabels[$cp->housing_status] ?? $cp->housing_status }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Skor Tempat Tinggal</span>
                        <div class="score-display">
                            <div class="score-box score-{{ $cp->housing_status_score }}">{{ $cp->housing_status_score }}</div>
                            <span class="score-desc">
                                @php
                                    $scoreDescs = [1=>'Milik Sendiri', 2=>'Kontrak', 3=>'Menumpang', 4=>'Tidak Tetap'];
                                @endphp
                                {{ $scoreDescs[$cp->housing_status_score] ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Catatan Urgensi --}}
        @if($cp->urgency_note)
        <div class="detail-card anim-fade-up delay-3">
            <div class="detail-card-header">
                <div class="detail-card-icon" style="background:var(--orange);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h3>Catatan Urgensi</h3>
            </div>
            <div class="detail-card-body">
                <div class="note-block warn">{{ $cp->urgency_note }}</div>
            </div>
        </div>
        @endif

        {{-- Riwayat Validasi / Activity Log --}}
        <div class="detail-card anim-fade-up delay-3">
            <div class="detail-card-header">
                <div class="detail-card-icon" style="background:var(--teal);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <h3>Riwayat Aktivitas</h3>
            </div>
            <div class="detail-card-body">
                @php
                    $logIconMap = [
                        'created'           => ['class' => 'created',   'label' => 'Data Dibuat'],
                        'submitted'         => ['class' => 'submitted', 'label' => 'Diajukan ke Stasi'],
                        'revision_requested'=> ['class' => 'revision',  'label' => 'Revisi Diminta'],
                        'approved'          => ['class' => 'approved',  'label' => 'Disetujui Stasi'],
                        'rejected'          => ['class' => 'rejected',  'label' => 'Ditolak'],
                        'sent_to_paroki'    => ['class' => 'sent',      'label' => 'Dikirim ke Paroki'],
                    ];
                @endphp

                @if($cp->validasiLogs->isNotEmpty())
                <div class="timeline">
                    @foreach($cp->validasiLogs as $log)
                    @php
                        $logInfo = $logIconMap[$log->action] ?? ['class' => 'created', 'label' => ucfirst($log->action)];
                    @endphp
                    <div class="timeline-item">
                        <div class="tl-dot-wrap">
                            <div class="tl-dot {{ $logInfo['class'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4"/>
                                </svg>
                            </div>
                            <div class="tl-line"></div>
                        </div>
                        <div class="tl-content">
                            <div class="tl-header">
                                <span class="tl-action">{{ $logInfo['label'] }}</span>
                                <span class="tl-time">{{ $log->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="tl-actor">oleh {{ $log->actor?->name ?? 'Sistem' }}</div>
                            @if($log->notes)
                            <div class="tl-note">{{ $log->notes }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                {{-- Tampilkan event dari timestamp jika tidak ada log validasi --}}
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="tl-dot-wrap">
                            <div class="tl-dot created"></div>
                            @if($cp->submitted_at) <div class="tl-line"></div> @endif
                        </div>
                        <div class="tl-content">
                            <div class="tl-header">
                                <span class="tl-action">Data Dibuat</span>
                                <span class="tl-time">{{ $cp->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="tl-actor">oleh {{ $cp->creator?->name ?? Auth::user()->name }}</div>
                        </div>
                    </div>

                    @if($cp->submitted_at)
                    <div class="timeline-item">
                        <div class="tl-dot-wrap">
                            <div class="tl-dot submitted"></div>
                            @if($cp->validated_at) <div class="tl-line"></div> @endif
                        </div>
                        <div class="tl-content">
                            <div class="tl-header">
                                <span class="tl-action">Diajukan ke Stasi</span>
                                <span class="tl-time">{{ $cp->submitted_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($cp->validated_at)
                    <div class="timeline-item">
                        <div class="tl-dot-wrap">
                            <div class="tl-dot {{ in_array($cp->status, ['rejected']) ? 'rejected' : 'approved' }}"></div>
                            @if($cp->sent_to_paroki_at) <div class="tl-line"></div> @endif
                        </div>
                        <div class="tl-content">
                            <div class="tl-header">
                                <span class="tl-action">{{ $cp->status === 'revision_requested' ? 'Revisi Diminta' : 'Divalidasi Stasi' }}</span>
                                <span class="tl-time">{{ $cp->validated_at->format('d M Y H:i') }}</span>
                            </div>
                            @if($cp->stasi_validation_note)
                            <div class="tl-note">{{ $cp->stasi_validation_note }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($cp->sent_to_paroki_at)
                    <div class="timeline-item">
                        <div class="tl-dot-wrap">
                            <div class="tl-dot sent"></div>
                        </div>
                        <div class="tl-content">
                            <div class="tl-header">
                                <span class="tl-action">Dikirim ke Paroki</span>
                                <span class="tl-time">{{ $cp->sent_to_paroki_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

    </div>{{-- end left col --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Actions Card --}}
        @if($canEdit || $canSubmit || $canDelete)
        <div class="action-card anim-fade-up delay-1">
            <div class="action-card-header">
                <h3>Tindakan</h3>
            </div>
            <div class="action-btn-block">
                @if($canEdit)
                <a href="{{ route('ketua-lingkungan-stasi.calons.edit', $cp) }}" class="action-btn-item">
                    <div class="ab-icon action-btn-icon-edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--black)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </div>
                    Edit Data Calon
                </a>
                @endif

                @if($canSubmit)
                <button type="button" class="action-btn-item" id="btn-open-submit">
                    <div class="ab-icon action-btn-icon-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </div>
                    Ajukan ke Stasi
                </button>
                @endif

                @if($canDelete)
                <button type="button" class="action-btn-item danger" id="btn-open-delete">
                    <div class="ab-icon action-btn-icon-delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6"/><path d="M14 11v6"/>
                        </svg>
                    </div>
                    Hapus Data
                </button>
                @endif
            </div>
        </div>
        @endif

        {{-- SAW Score --}}
        @if($cp->sawResult)
        <div class="saw-card anim-fade-up delay-1">
            <div class="saw-score-label">Skor SAW</div>
            <div class="saw-score-big">{{ number_format($cp->sawResult->saw_score ?? 0, 4) }}</div>
            @if($cp->sawResult->rank_global)
            <div class="saw-rank">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="var(--yellow)"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Rank #{{ $cp->sawResult->rank_global }} Global
            </div>
            @endif
            <div style="font-size:0.72rem;color:var(--gray-500);font-weight:600;text-align:center;line-height:1.5;">
                Dihitung berdasarkan metode Simple Additive Weighting (SAW)
            </div>
        </div>
        @else
        <div class="detail-card anim-fade-up delay-1" style="border-style:dashed;box-shadow:none;">
            <div class="detail-card-body" style="text-align:center;padding:1.5rem;color:var(--gray-400);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.5rem;display:block;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <div style="font-size:0.8rem;font-weight:600;">Skor SAW belum dihitung</div>
                <div style="font-size:0.72rem;margin-top:0.2rem;">Akan tersedia setelah disetujui Stasi</div>
            </div>
        </div>
        @endif

        {{-- Info Pengajuan --}}
        <div class="detail-card anim-fade-up delay-2">
            <div class="detail-card-header">
                <div class="detail-card-icon" style="background:var(--blue);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <h3>Info Pengajuan</h3>
            </div>
            <div class="detail-card-body" style="padding:0;">
                <div class="quick-info-strip">
                    <div class="qi-item">
                        <span class="qi-label">Periode</span>
                        <span class="qi-value">{{ $cp->periodeBantuan?->name ?? '—' }}</span>
                    </div>
                    <div class="qi-item">
                        <span class="qi-label">Paroki</span>
                        <span class="qi-value">{{ $cp->paroki?->name ?? '—' }}</span>
                    </div>
                    <div class="qi-item">
                        <span class="qi-label">Stasi</span>
                        <span class="qi-value">{{ $cp->stasi?->name ?? '—' }}</span>
                    </div>
                    <div class="qi-item">
                        <span class="qi-label">Lingkungan</span>
                        <span class="qi-value">{{ $cp->lingkungan?->name ?? '—' }}</span>
                    </div>
                    <div class="qi-item">
                        <span class="qi-label">Dibuat oleh</span>
                        <span class="qi-value">{{ $cp->creator?->name ?? '—' }}</span>
                    </div>
                    <div class="qi-item">
                        <span class="qi-label">Tgl. Input</span>
                        <span class="qi-value">{{ $cp->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="qi-item">
                        <span class="qi-label">Tgl. Update</span>
                        <span class="qi-value">{{ $cp->updated_at->format('d M Y') }}</span>
                    </div>
                    @if($cp->submitted_at)
                    <div class="qi-item">
                        <span class="qi-label">Tgl. Diajukan</span>
                        <span class="qi-value">{{ $cp->submitted_at->format('d M Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Keputusan Paroki (jika ada) --}}
        @if($cp->paroki_decision_note)
        <div class="detail-card anim-fade-up delay-3">
            <div class="detail-card-header" style="background:var(--{{ $cp->status === 'rejected' ? 'red' : 'teal' }});">
                <div class="detail-card-icon" style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h3 style="color:white;">Keputusan Paroki</h3>
            </div>
            <div class="detail-card-body">
                <div class="note-block {{ $cp->status === 'rejected' ? 'danger' : 'info' }}">{{ $cp->paroki_decision_note }}</div>
            </div>
        </div>
        @endif

    </div>{{-- end right col --}}
</div>{{-- end detail-grid --}}

</div>{{-- end detail-wrap --}}

{{-- ══ MODALS ══════════════════════════════════════════════ --}}

{{-- Submit Modal --}}
@if($canSubmit)
<div class="modal-overlay" id="submit-modal">
    <div class="modal-box">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <div style="width:40px;height:40px;background:var(--blue);border:3px solid var(--black);border-radius:2px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </div>
            <h3 style="margin:0;">Ajukan ke Stasi?</h3>
        </div>
        <p>Data <strong>{{ $cp->name }}</strong> akan diajukan ke Stasi untuk divalidasi.</p>
        <div class="nb-alert alert-warning" style="margin-bottom:1.25rem;font-size:0.82rem;">
            Pastikan semua data sudah lengkap dan benar. Setelah diajukan, data hanya bisa diedit jika Stasi meminta revisi.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="submit-modal-cancel">Batal</button>
            <form action="{{ route('ketua-lingkungan-stasi.calons.submit-to-stasi', $cp) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Ya, Ajukan
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Delete Modal --}}
@if($canDelete)
<div class="modal-overlay" id="delete-modal">
    <div class="modal-box">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <div style="width:40px;height:40px;background:var(--red);border:3px solid var(--black);border-radius:2px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                </svg>
            </div>
            <h3 style="margin:0;">Hapus Data?</h3>
        </div>
        <p>Data <strong>{{ $cp->name }}</strong> akan dihapus permanen. Tindakan ini <strong>tidak dapat dibatalkan</strong>.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="delete-modal-cancel">Batal</button>
            <form action="{{ route('ketua-lingkungan-stasi.calons.destroy', $cp) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-red">Ya, Hapus Permanen</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
/* ── Submit Modal ────────────────────────────────────────── */
const submitModal  = document.getElementById('submit-modal');
const btnSubmit    = document.getElementById('btn-open-submit');
const cancelSubmit = document.getElementById('submit-modal-cancel');

if (btnSubmit && submitModal) {
    btnSubmit.addEventListener('click', () => submitModal.classList.add('show'));
    cancelSubmit.addEventListener('click', () => submitModal.classList.remove('show'));
    submitModal.addEventListener('click', (e) => {
        if (e.target === submitModal) submitModal.classList.remove('show');
    });
}

/* ── Delete Modal ────────────────────────────────────────── */
const deleteModal  = document.getElementById('delete-modal');
const btnDelete    = document.getElementById('btn-open-delete');
const cancelDelete = document.getElementById('delete-modal-cancel');

if (btnDelete && deleteModal) {
    btnDelete.addEventListener('click', () => deleteModal.classList.add('show'));
    cancelDelete.addEventListener('click', () => deleteModal.classList.remove('show'));
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) deleteModal.classList.remove('show');
    });
}

/* ── Keyboard: Esc closes modals ────────────────────────── */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        submitModal?.classList.remove('show');
        deleteModal?.classList.remove('show');
    }
});

/* ── Flash auto-hide ─────────────────────────────────────── */
const flash = document.getElementById('flash-msg');
if (flash) {
    setTimeout(() => {
        flash.style.transition = 'opacity 0.5s';
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 500);
    }, 4000);
}
</script>
@endpush
