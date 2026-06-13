@extends('layouts.app')

@section('title', 'Tambah Calon Penerima')
@section('meta_description', 'Formulir pendaftaran calon penerima bantuan sosial untuk lingkungan Anda.')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   FORM CREATE CALON PENERIMA — Neobrutalism
   ═══════════════════════════════════════════════════════════ */

/* ── Page Layout ─────────────────────────────────────────── */
.form-page-wrap {
    max-width: 860px;
}

/* ── Section Cards ───────────────────────────────────────── */
.form-section {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.form-section:focus-within {
    box-shadow: var(--shadow-lg);
}

.form-section-header {
    background: var(--black);
    padding: 0.9rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-section-num {
    width: 28px;
    height: 28px;
    background: var(--yellow);
    border: 2px solid var(--yellow);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--black);
    flex-shrink: 0;
    font-family: var(--font-mono);
}

.form-section-title {
    font-size: 0.82rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--white);
}

.form-section-subtitle {
    font-size: 0.72rem;
    font-weight: 500;
    color: var(--gray-500);
    margin-left: auto;
}

.form-section-body {
    padding: 1.5rem;
}

/* ── 2-column Grid ───────────────────────────────────────── */
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.25rem;
}

.form-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem 1.25rem;
}

.col-span-2 { grid-column: span 2; }
.col-span-3 { grid-column: span 3; }

/* ── Form Label ──────────────────────────────────────────── */
.form-label {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.4rem;
    color: var(--gray-700);
}

.form-label .req {
    color: var(--red);
    font-size: 0.9rem;
    line-height: 1;
}

.form-label .opt {
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--gray-300);
    text-transform: none;
    letter-spacing: 0;
    border: 1.5px solid var(--gray-200);
    padding: 0.05rem 0.35rem;
    border-radius: 2px;
}

/* ── Input Hint ──────────────────────────────────────────── */
.form-hint {
    font-size: 0.74rem;
    font-weight: 500;
    color: var(--gray-500);
    margin-top: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* ── Score Radio Group ───────────────────────────────────── */
.score-group {
    display: flex;
    gap: 0.5rem;
}

.score-option {
    flex: 1;
}

.score-option input[type="radio"] {
    display: none;
}

.score-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
    padding: 0.6rem 0.5rem;
    border: 2px solid var(--black);
    border-radius: 2px;
    cursor: pointer;
    transition: background 0.12s, transform 0.1s, box-shadow 0.1s;
    box-shadow: 2px 2px 0 var(--black);
    text-align: center;
    background: var(--white);
    font-weight: 700;
    font-size: 0.78rem;
    user-select: none;
}

.score-option label .score-num {
    font-size: 1.1rem;
    font-weight: 800;
    font-family: var(--font-mono);
    line-height: 1;
}

.score-option label .score-desc {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--gray-500);
}

.score-option input:checked + label {
    background: var(--black);
    color: var(--white);
    box-shadow: 3px 3px 0 var(--yellow);
    border-color: var(--black);
    transform: translate(-1px, -1px);
}

.score-option input:checked + label .score-desc {
    color: var(--yellow);
}

/* Score variants per color */
.score-opt-1 input:checked + label { background: var(--lime); color: var(--black); box-shadow: 3px 3px 0 var(--black); }
.score-opt-1 input:checked + label .score-desc { color: var(--gray-700); }
.score-opt-2 input:checked + label { background: var(--yellow); color: var(--black); box-shadow: 3px 3px 0 var(--black); }
.score-opt-2 input:checked + label .score-desc { color: var(--gray-700); }
.score-opt-3 input:checked + label { background: var(--orange); color: var(--white); box-shadow: 3px 3px 0 var(--black); }
.score-opt-3 input:checked + label .score-desc { color: var(--white); }
.score-opt-4 input:checked + label { background: var(--red); color: var(--white); box-shadow: 3px 3px 0 var(--black); }
.score-opt-4 input:checked + label .score-desc { color: var(--white); }

/* ── Toggle Switch ───────────────────────────────────────── */
.nb-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1rem;
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    background: var(--white);
    cursor: pointer;
    transition: background 0.12s, box-shadow 0.12s;
}

.nb-toggle-wrap:has(input:checked) {
    background: #fff0f0;
    box-shadow: 4px 4px 0 var(--red);
    border-color: var(--red);
}

.nb-toggle {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.nb-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.nb-toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--gray-200);
    border: 2px solid var(--black);
    border-radius: 2px;
    cursor: pointer;
    transition: background 0.2s;
}

.nb-toggle-slider::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    left: 2px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--black);
    border-radius: 1px;
    transition: transform 0.2s;
}

.nb-toggle input:checked + .nb-toggle-slider {
    background: var(--red);
}

.nb-toggle input:checked + .nb-toggle-slider::before {
    transform: translateY(-50%) translateX(20px);
    background: var(--white);
}

.nb-toggle-label {
    flex: 1;
}

.nb-toggle-label strong {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--black);
}

.nb-toggle-label span {
    display: block;
    font-size: 0.74rem;
    color: var(--gray-500);
    font-weight: 500;
    margin-top: 0.1rem;
}

/* ── Disability sub-section ──────────────────────────────── */
.disability-detail {
    margin-top: 1rem;
    padding: 1.25rem;
    background: #fff0f0;
    border: 2px solid var(--red);
    border-radius: var(--radius);
    display: none;
}

.disability-detail.show {
    display: block;
    animation: fadeUp 0.25s ease both;
}

/* ── Breadcrumb ──────────────────────────────────────────── */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--gray-500);
    margin-bottom: 1.5rem;
}

.breadcrumb a {
    color: var(--blue);
    text-decoration: underline;
    text-underline-offset: 2px;
}

.breadcrumb-sep {
    color: var(--gray-300);
}

/* ── Sticky Submit Bar ───────────────────────────────────── */
.submit-bar {
    position: sticky;
    bottom: 0;
    background: var(--cream);
    border-top: 3px solid var(--black);
    padding: 1rem 0;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    z-index: 50;
}

.submit-bar-info {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.submit-bar-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* ── Section Progress Indicator ─────────────────────────── */
.form-progress {
    display: flex;
    gap: 0.35rem;
    margin-bottom: 1.75rem;
}

.progress-step {
    flex: 1;
    height: 5px;
    background: var(--gray-200);
    border-radius: 2px;
    border: 1px solid var(--black);
    transition: background 0.3s;
}

.progress-step.done {
    background: var(--yellow);
}

.progress-step.active {
    background: var(--blue);
}

/* ── Info Panel ──────────────────────────────────────────── */
.info-panel {
    background: var(--black);
    border: 3px solid var(--black);
    border-radius: var(--radius);
    box-shadow: 5px 5px 0 var(--yellow);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.info-panel-icon {
    width: 32px;
    height: 32px;
    background: var(--yellow);
    border: 2px solid var(--yellow);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-panel-text {
    font-size: 0.82rem;
    color: var(--gray-300);
    font-weight: 500;
    line-height: 1.6;
}

.info-panel-text strong {
    color: var(--yellow);
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}

/* ── Char counter ────────────────────────────────────────── */
.char-counter {
    font-size: 0.7rem;
    font-family: var(--font-mono);
    color: var(--gray-300);
    text-align: right;
    margin-top: 0.2rem;
    transition: color 0.2s;
}

.char-counter.warn { color: var(--orange); }
.char-counter.over { color: var(--red); font-weight: 700; }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 700px) {
    .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
    .col-span-2, .col-span-3 { grid-column: span 1; }
    .score-group { flex-wrap: wrap; }
    .score-option { min-width: calc(50% - 0.25rem); }
}
</style>
@endpush

@section('content')
<div class="form-page-wrap">

    {{-- ── Breadcrumb ────────────────────────────────────── --}}
    <nav class="breadcrumb anim-fade-up" aria-label="breadcrumb">
        <a href="{{ route('ketua-lingkungan.dashboard') }}">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}">Daftar Calon</a>
        <span class="breadcrumb-sep">›</span>
        <span style="color: var(--black); font-weight: 700;">Tambah Calon Baru</span>
    </nav>

    {{-- ── Page Header ───────────────────────────────────── --}}
    <div class="page-header anim-fade-up">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
            <div>
                <h1 class="page-title">Tambah Calon Penerima</h1>
                <p class="page-subtitle">
                    Lingkungan <strong>{{ Auth::user()->lingkungan?->name ?? '—' }}</strong>
                    · Stasi <strong>{{ Auth::user()->stasi?->name ?? '—' }}</strong>
                </p>
            </div>
            <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}" class="btn btn-outline btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>



    {{-- ── Validation Errors ─────────────────────────────── --}}
    @if ($errors->any())
    <div class="nb-alert alert-error anim-fade-up" id="form-errors">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <strong>Terdapat {{ $errors->count() }} kesalahan — periksa isian berikut:</strong>
        </div>
        <ul style="padding-left: 1.5rem; font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.2rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- FORM                                               --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <form action="{{ route('ketua-lingkungan-stasi.calons.store') }}"
          method="POST"
          id="form-create-calon"
          novalidate>
        @csrf

        {{-- ─────────────────────────────────────────────── --}}
        {{-- SEKSI 1 — PERIODE BANTUAN                       --}}
        {{-- ─────────────────────────────────────────────── --}}
        <div class="form-section anim-fade-up delay-1">
            <div class="form-section-header">
                <div class="form-section-num">01</div>
                <span class="form-section-title">Periode Bantuan</span>
                <span class="form-section-subtitle">Wajib dipilih</span>
            </div>
            <div class="form-section-body">
                <div class="form-group">
                    <label class="form-label" for="periode_bantuan_id">
                        Periode Bantuan Aktif
                        <span class="req">*</span>
                    </label>
                    @if($periodeBantuans->isEmpty())
                        <div class="nb-alert alert-warning" style="margin-bottom: 0;">
                            <strong>Tidak ada periode bantuan aktif.</strong>
                            Hubungi administrator Paroki untuk membuka periode bantuan baru.
                        </div>
                        <input type="hidden" name="periode_bantuan_id" value="">
                    @else
                        <select name="periode_bantuan_id"
                                id="periode_bantuan_id"
                                class="nb-select {{ $errors->has('periode_bantuan_id') ? 'is-invalid' : '' }}"
                                required>
                            <option value="" disabled {{ old('periode_bantuan_id') ? '' : 'selected' }}>— Pilih periode bantuan —</option>
                            @foreach($periodeBantuans as $periode)
                                <option value="{{ $periode->id }}"
                                    {{ old('periode_bantuan_id') == $periode->id ? 'selected' : '' }}>
                                    {{ $periode->name }} ({{ $periode->starts_at->format('M Y') }})
                                    · {{ ucfirst($periode->status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('periode_bantuan_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    @endif
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────── --}}
        {{-- SEKSI 2 — IDENTITAS PRIBADI                     --}}
        {{-- ─────────────────────────────────────────────── --}}
        <div class="form-section anim-fade-up delay-2">
            <div class="form-section-header">
                <div class="form-section-num">02</div>
                <span class="form-section-title">Identitas Pribadi</span>
                <span class="form-section-subtitle">Data diri calon penerima</span>
            </div>
            <div class="form-section-body">
                <div class="form-grid-2">

                    {{-- Nama Lengkap --}}
                    <div class="form-group col-span-2">
                        <label class="form-label" for="name">
                            Nama Lengkap <span class="req">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="nb-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}"
                               placeholder="Nama sesuai KTP"
                               maxlength="255"
                               required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- NIK --}}
                    <div class="form-group">
                        <label class="form-label" for="nik">
                            NIK <span class="req">*</span>
                        </label>
                        <input type="text"
                               id="nik"
                               name="nik"
                               class="nb-input {{ $errors->has('nik') ? 'is-invalid' : '' }}"
                               value="{{ old('nik') }}"
                               placeholder="16 digit NIK"
                               maxlength="32"
                               inputmode="numeric"
                               pattern="[0-9]{16}"
                               required>

                        @error('nik')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nomor KK --}}
                    <div class="form-group">
                        <label class="form-label" for="nomor_kk">
                            Nomor KK <span class="req">*</span>
                        </label>
                        <input type="text"
                               id="nomor_kk"
                               name="nomor_kk"
                               class="nb-input {{ $errors->has('nomor_kk') ? 'is-invalid' : '' }}"
                               value="{{ old('nomor_kk') }}"
                               placeholder="16 digit Nomor KK"
                               maxlength="32"
                               inputmode="numeric"
                               required>
                        @error('nomor_kk')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nama Kepala Keluarga --}}
                    <div class="form-group">
                        <label class="form-label" for="family_head_name">
                            Nama Kepala Keluarga <span class="opt">opsional</span>
                        </label>
                        <input type="text"
                               id="family_head_name"
                               name="family_head_name"
                               class="nb-input {{ $errors->has('family_head_name') ? 'is-invalid' : '' }}"
                               value="{{ old('family_head_name') }}"
                               placeholder="Nama kepala keluarga"
                               maxlength="255">
                        @error('family_head_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="form-group">
                        <label class="form-label" for="gender">
                            Jenis Kelamin <span class="opt">opsional</span>
                        </label>
                        <select name="gender"
                                id="gender"
                                class="nb-select {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                            <option value="" {{ old('gender') ? '' : 'selected' }}>— Pilih —</option>
                            <option value="laki_laki"  {{ old('gender') == 'laki_laki'  ? 'selected' : '' }}>Laki-laki</option>
                            <option value="perempuan"  {{ old('gender') == 'perempuan'  ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tempat Lahir --}}
                    <div class="form-group">
                        <label class="form-label" for="place_of_birth">
                            Tempat Lahir <span class="opt">opsional</span>
                        </label>
                        <input type="text"
                               id="place_of_birth"
                               name="place_of_birth"
                               class="nb-input"
                               value="{{ old('place_of_birth') }}"
                               placeholder="Kota/kabupaten"
                               maxlength="255">
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="form-group">
                        <label class="form-label" for="date_of_birth">
                            Tanggal Lahir <span class="opt">opsional</span>
                        </label>
                        <input type="date"
                               id="date_of_birth"
                               name="date_of_birth"
                               class="nb-input"
                               value="{{ old('date_of_birth') }}"
                               max="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Alamat --}}
                    <div class="form-group col-span-2">
                        <label class="form-label" for="address">
                            Alamat Lengkap <span class="req">*</span>
                        </label>
                        <textarea id="address"
                                  name="address"
                                  class="nb-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                  placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..."
                                  rows="3"
                                  maxlength="1000">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Telepon --}}
                    <div class="form-group">
                        <label class="form-label" for="phone">
                            No. Telepon / HP <span class="opt">opsional</span>
                        </label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               class="nb-input"
                               value="{{ old('phone') }}"
                               placeholder="08xx-xxxx-xxxx"
                               maxlength="20"
                               inputmode="tel">
                    </div>

                    {{-- Pekerjaan --}}
                    <div class="form-group">
                        <label class="form-label" for="occupation">
                            Pekerjaan <span class="opt">opsional</span>
                        </label>
                        <input type="text"
                               id="occupation"
                               name="occupation"
                               class="nb-input"
                               value="{{ old('occupation') }}"
                               placeholder="Buruh, pedagang, petani, dll."
                               maxlength="255">
                    </div>

                </div>{{-- end form-grid-2 --}}
            </div>
        </div>

        {{-- ─────────────────────────────────────────────── --}}
        {{-- SEKSI 3 — DATA EKONOMI                          --}}
        {{-- ─────────────────────────────────────────────── --}}
        <div class="form-section anim-fade-up delay-2">
            <div class="form-section-header">
                <div class="form-section-num">03</div>
                <span class="form-section-title">Data Ekonomi</span>
                <span class="form-section-subtitle">Digunakan untuk perhitungan SAW</span>
            </div>
            <div class="form-section-body">
                <div class="form-grid-2">

                    {{-- Penghasilan Bulanan --}}
                    <div class="form-group">
                        <label class="form-label" for="monthly_income">
                            Penghasilan Bulanan (Rp) <span class="req">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); font-weight: 700; font-family: var(--font-mono); color: var(--gray-500); font-size: 0.88rem; pointer-events: none;">Rp</span>
                            <input type="number"
                                   id="monthly_income"
                                   name="monthly_income"
                                   class="nb-input {{ $errors->has('monthly_income') ? 'is-invalid' : '' }}"
                                   value="{{ old('monthly_income', 0) }}"
                                   placeholder="0"
                                   min="0"
                                   step="1000"
                                   style="padding-left: 2.5rem;"
                                   required>
                        </div>
                        <div class="form-hint">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span id="income-display">Rp 0</span>
                        </div>
                        @error('monthly_income')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Jumlah Tanggungan --}}
                    <div class="form-group">
                        <label class="form-label" for="dependents_count">
                            Jumlah Tanggungan <span class="req">*</span>
                        </label>
                        <input type="number"
                               id="dependents_count"
                               name="dependents_count"
                               class="nb-input {{ $errors->has('dependents_count') ? 'is-invalid' : '' }}"
                               value="{{ old('dependents_count', 0) }}"
                               placeholder="0"
                               min="0"
                               max="20"
                               required>

                        @error('dependents_count')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                {{-- Catatan Kondisi Ekonomi --}}
                <div class="form-group" style="margin-top: 0.25rem;">
                    <label class="form-label" for="economic_condition_note">
                        Catatan Kondisi Ekonomi <span class="opt">opsional</span>
                    </label>
                    <textarea id="economic_condition_note"
                              name="economic_condition_note"
                              class="nb-textarea"
                              placeholder="Jelaskan kondisi ekonomi secara singkat: sumber penghasilan, beban pengeluaran, kondisi keuangan keluarga..."
                              rows="3"
                              maxlength="1000"
                              data-maxlen="1000">{{ old('economic_condition_note') }}</textarea>
                    <div class="char-counter" id="eco-counter">0 / 1000</div>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────── --}}
        {{-- SEKSI 4 — STATUS TEMPAT TINGGAL + SKOR          --}}
        {{-- ─────────────────────────────────────────────── --}}
        <div class="form-section anim-fade-up delay-3">
            <div class="form-section-header">
                <div class="form-section-num">04</div>
                <span class="form-section-title">Status Tempat Tinggal</span>
                <span class="form-section-subtitle">Kriteria SAW — pilih skor yang sesuai</span>
            </div>
            <div class="form-section-body">

                {{-- Pilih Status Hunian --}}
                <div class="form-group">
                    <label class="form-label" for="housing_status">
                        Status Kepemilikan Rumah <span class="req">*</span>
                    </label>
                    <select name="housing_status"
                            id="housing_status"
                            class="nb-select {{ $errors->has('housing_status') ? 'is-invalid' : '' }}"
                            required>
                        <option value="" disabled {{ old('housing_status') ? '' : 'selected' }}>— Pilih status —</option>
                        <option value="milik_sendiri" {{ old('housing_status') == 'milik_sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                        <option value="kontrak"       {{ old('housing_status') == 'kontrak'       ? 'selected' : '' }}>Kontrak / Sewa</option>
                        <option value="menumpang"     {{ old('housing_status') == 'menumpang'     ? 'selected' : '' }}>Menumpang</option>
                        <option value="tidak_tetap"   {{ old('housing_status') == 'tidak_tetap'   ? 'selected' : '' }}>Tidak Tetap</option>
                    </select>
                    @error('housing_status')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Skor Tempat Tinggal --}}
                <div class="form-group" style="display: none;">
                    <label class="form-label">
                        Skor Tempat Tinggal <span class="req">*</span>
                        <span style="font-size: 0.68rem; font-weight: 500; text-transform: none; color: var(--gray-500); margin-left: 0.3rem;">
                            (1 = Paling baik, 4 = Paling rentan)
                        </span>
                    </label>
                    <div class="score-group">
                        @foreach([
                            [1, 'Milik Sendiri', 'score-opt-1'],
                            [2, 'Kontrak/Sewa',  'score-opt-2'],
                            [3, 'Menumpang',     'score-opt-3'],
                            [4, 'Tidak Tetap',   'score-opt-4'],
                        ] as [$val, $desc, $cls])
                        <div class="score-option {{ $cls }}">
                            <input type="radio"
                                   name="housing_status_score"
                                   id="hss_{{ $val }}"
                                   value="{{ $val }}"
                                   {{ old('housing_status_score', '') == $val ? 'checked' : '' }}>
                            <label for="hss_{{ $val }}">
                                <span class="score-num">{{ $val }}</span>
                                <span class="score-desc">{{ $desc }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('housing_status_score')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────── --}}
        {{-- SEKSI 5 — DISABILITAS                           --}}
        {{-- ─────────────────────────────────────────────── --}}
        <div class="form-section anim-fade-up delay-3">
            <div class="form-section-header">
                <div class="form-section-num">05</div>
                <span class="form-section-title">Kondisi Disabilitas</span>
                <span class="form-section-subtitle">Kriteria SAW tambahan</span>
            </div>
            <div class="form-section-body">

                {{-- Toggle --}}
                <label class="nb-toggle-wrap" for="has_disability" id="disability-toggle-wrap">
                    <div class="nb-toggle">
                        <input type="checkbox"
                               name="has_disability"
                               id="has_disability"
                               value="1"
                               {{ old('has_disability') ? 'checked' : '' }}>
                        <div class="nb-toggle-slider"></div>
                    </div>
                    <div class="nb-toggle-label">
                        <strong id="disability-toggle-text">
                            {{ old('has_disability') ? 'Ya, memiliki disabilitas' : 'Tidak memiliki disabilitas' }}
                        </strong>
                        <span>Aktifkan jika calon penerima memiliki kondisi difabel/keterbatasan fisik/mental</span>
                    </div>
                    <div class="nb-badge badge-gray" id="disability-badge" style="{{ old('has_disability') ? 'background: var(--red); color: white; border-color: var(--red);' : '' }}">
                        {{ old('has_disability') ? 'DIFABEL' : 'NORMAL' }}
                    </div>
                </label>

                {{-- Disability Detail (hidden until toggled) --}}
                <div class="disability-detail {{ old('has_disability') ? 'show' : '' }}" id="disability-detail">
                    <div class="form-grid-2">

                        {{-- Skor Disabilitas --}}
                        <div class="form-group col-span-2" style="display: none;">
                            <label class="form-label">
                                Skor Disabilitas <span class="req">*</span>
                                <span style="font-size: 0.68rem; font-weight: 500; text-transform: none; color: var(--gray-500);">
                                    (1 = Tanpa disabilitas, 2 = Ada disabilitas)
                                </span>
                            </label>
                            <div class="score-group" style="max-width: 280px;">
                                <div class="score-option score-opt-1">
                                    <input type="radio" name="disability_score" id="ds_1" value="1" {{ old('disability_score', 1) == 1 ? 'checked' : '' }}>
                                    <label for="ds_1">
                                        <span class="score-num">1</span>
                                        <span class="score-desc">Tidak ada</span>
                                    </label>
                                </div>
                                <div class="score-option score-opt-4">
                                    <input type="radio" name="disability_score" id="ds_2" value="2" {{ old('disability_score') == 2 ? 'checked' : '' }}>
                                    <label for="ds_2">
                                        <span class="score-num">2</span>
                                        <span class="score-desc">Ada disabilitas</span>
                                    </label>
                                </div>
                            </div>
                            @error('disability_score')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Catatan Disabilitas --}}
                        <div class="form-group col-span-2">
                            <label class="form-label" for="disability_note">
                                Keterangan Disabilitas <span class="opt">opsional</span>
                            </label>
                            <textarea id="disability_note"
                                      name="disability_note"
                                      class="nb-textarea"
                                      placeholder="Jelaskan jenis dan kondisi disabilitas secara singkat..."
                                      rows="2"
                                      maxlength="500"
                                      data-maxlen="500">{{ old('disability_note') }}</textarea>
                            <div class="char-counter" id="dis-counter">0 / 500</div>
                        </div>

                    </div>
                </div>

                {{-- Default skor jika tidak ada disabilitas --}}
                @if(!old('has_disability'))
                <input type="hidden" name="disability_score" value="1">
                @endif

            </div>
        </div>

        {{-- ─────────────────────────────────────────────── --}}
        {{-- SEKSI 6 — CATATAN URGENSI                       --}}
        {{-- ─────────────────────────────────────────────── --}}
        <div class="form-section anim-fade-up delay-4">
            <div class="form-section-header">
                <div class="form-section-num">06</div>
                <span class="form-section-title">Catatan Urgensi</span>
                <span class="form-section-subtitle">Pertimbangan tambahan</span>
            </div>
            <div class="form-section-body">
                <div class="form-group">
                    <label class="form-label" for="urgency_note">
                        Alasan Urgensi Khusus <span class="opt">opsional</span>
                    </label>
                    <textarea id="urgency_note"
                              name="urgency_note"
                              class="nb-textarea"
                              placeholder="Tuliskan kondisi darurat, bencana, penyakit kronis, PHK, atau situasi mendesak lainnya yang perlu menjadi pertimbangan..."
                              rows="4"
                              maxlength="1000"
                              data-maxlen="1000">{{ old('urgency_note') }}</textarea>
                    <div class="char-counter" id="urg-counter">0 / 1000</div>

                </div>
            </div>
        </div>

        {{-- ── Sticky Submit Bar ──────────────────────────── --}}
        <div class="submit-bar">
            <div class="submit-bar-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Data akan disimpan sebagai <strong>&nbsp;DRAFT</strong>
            </div>
            <div class="submit-bar-actions">
                <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}"
                   class="btn btn-outline"
                   id="btn-cancel">
                    Batal
                </a>
                <button type="submit"
                        class="btn btn-dark"
                        id="btn-submit"
                        style="box-shadow: 5px 5px 0 var(--yellow);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    <span id="btn-submit-text">Simpan sebagai Draft</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   FORM CREATE CALON — JS
   ═══════════════════════════════════════════════════════════ */

/* ── 1. Disability Toggle ────────────────────────────────── */
const disabilityCheck  = document.getElementById('has_disability');
const disabilityDetail = document.getElementById('disability-detail');
const disabilityText   = document.getElementById('disability-toggle-text');
const disabilityBadge  = document.getElementById('disability-badge');

function updateDisabilityUI() {
    const checked = disabilityCheck.checked;

    disabilityDetail.classList.toggle('show', checked);
    disabilityText.textContent = checked ? 'Ya, memiliki disabilitas' : 'Tidak memiliki disabilitas';

    if (checked) {
        disabilityBadge.textContent = 'DIFABEL';
        disabilityBadge.style.background   = 'var(--red)';
        disabilityBadge.style.color        = 'white';
        disabilityBadge.style.borderColor  = 'var(--red)';

        // Set score ke 2 jika ada disabilitas
        const ds2 = document.getElementById('ds_2');
        if (ds2) ds2.checked = true;

        // Hapus hidden input skor
        const hiddenScore = document.querySelector('input[name="disability_score"][type="hidden"]');
        if (hiddenScore) hiddenScore.remove();
    } else {
        disabilityBadge.textContent = 'NORMAL';
        disabilityBadge.style.background  = '';
        disabilityBadge.style.color       = '';
        disabilityBadge.style.borderColor = '';

        // Reset ke skor 1
        const ds1 = document.getElementById('ds_1');
        if (ds1) ds1.checked = true;
    }
}

disabilityCheck.addEventListener('change', updateDisabilityUI);

/* ── 2. Income Formatter ─────────────────────────────────── */
const incomeInput   = document.getElementById('monthly_income');
const incomeDisplay = document.getElementById('income-display');

function formatRupiah(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function updateIncomeDisplay() {
    const val = parseFloat(incomeInput.value) || 0;
    incomeDisplay.textContent = formatRupiah(val);
}

incomeInput.addEventListener('input', updateIncomeDisplay);
updateIncomeDisplay();

/* ── 3. Housing Status → Auto Score ─────────────────────── */
const housingSelect = document.getElementById('housing_status');
const scoreMap = {
    'milik_sendiri': 'hss_1',
    'kontrak':       'hss_2',
    'menumpang':     'hss_3',
    'tidak_tetap':   'hss_4',
};

housingSelect.addEventListener('change', () => {
    const radioId = scoreMap[housingSelect.value];
    if (radioId) {
        const radio = document.getElementById(radioId);
        if (radio) radio.checked = true;
    }
});

/* ── 4. Char Counters ────────────────────────────────────── */
function initCharCounter(textareaId, counterId) {
    const ta      = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    if (!ta || !counter) return;

    const max = parseInt(ta.dataset.maxlen) || 1000;

    function update() {
        const len = ta.value.length;
        counter.textContent = `${len} / ${max}`;
        counter.className = 'char-counter';
        if (len > max * 0.9) counter.classList.add('warn');
        if (len >= max)      counter.classList.add('over');
    }

    ta.addEventListener('input', update);
    update();
}

initCharCounter('economic_condition_note', 'eco-counter');
initCharCounter('disability_note',         'dis-counter');
initCharCounter('urgency_note',            'urg-counter');

/* ── 5. Submit Loading State ─────────────────────────────── */
const form      = document.getElementById('form-create-calon');
const btnSubmit = document.getElementById('btn-submit');
const btnText   = document.getElementById('btn-submit-text');

form.addEventListener('submit', () => {
    btnSubmit.disabled = true;
    btnSubmit.style.opacity = '0.8';
    btnText.textContent = 'Menyimpan...';
});

/* ── 6. Scroll to errors ─────────────────────────────────── */
const errBlock = document.getElementById('form-errors');
if (errBlock) {
    errBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* ── 7. NIK: hanya angka ─────────────────────────────────── */
document.getElementById('nik').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 16);
});

document.getElementById('nomor_kk').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 16);
});
</script>
@endpush
