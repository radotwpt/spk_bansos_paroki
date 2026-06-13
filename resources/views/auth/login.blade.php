@extends('layouts.auth')

@section('title', 'Masuk — SPK Bansos')
@section('meta_description', 'Login ke Sistem Pendukung Keputusan Bantuan Sosial.')

@push('styles')
<style>
/* ── Login Page ─────────────────────────────────── */
.login-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: var(--cream);
}

/* Left: Decorative Panel */
.login-panel-left {
    background: var(--black);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 4rem 3.5rem;
    position: relative;
    overflow: hidden;
    border-right: 4px solid var(--yellow);
}

/* Retro grid background */
.login-panel-left::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(245,197,24,0.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(245,197,24,0.06) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}

/* Floating decorative shapes */
.deco-shape {
    position: absolute;
    border: 3px solid;
    border-radius: 4px;
}

.deco-1 {
    width: 80px; height: 80px;
    border-color: var(--yellow);
    top: 8%; right: 12%;
    transform: rotate(15deg);
    animation: float1 6s ease-in-out infinite;
}

.deco-2 {
    width: 40px; height: 40px;
    border-color: var(--lime);
    background: var(--lime);
    bottom: 20%; left: 8%;
    transform: rotate(-10deg);
    animation: float2 8s ease-in-out infinite;
}

.deco-3 {
    width: 60px; height: 60px;
    border-color: var(--blue);
    background: var(--blue);
    bottom: 10%; right: 10%;
    transform: rotate(25deg);
    animation: float1 7s ease-in-out infinite 1s;
}

.deco-4 {
    width: 100px; height: 6px;
    border-color: var(--orange);
    background: var(--orange);
    top: 30%; left: 5%;
    border-radius: 2px;
    border: none;
    animation: float2 5s ease-in-out infinite 0.5s;
}

@keyframes float1 {
    0%, 100% { transform: rotate(15deg) translateY(0); }
    50%       { transform: rotate(15deg) translateY(-14px); }
}

@keyframes float2 {
    0%, 100% { transform: rotate(-10deg) translateY(0); }
    50%       { transform: rotate(-10deg) translateY(10px); }
}

/* Brand on left panel */
.login-brand {
    position: relative;
    z-index: 1;
    margin-bottom: 3rem;
}

.login-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--yellow);
    border: 2px solid var(--yellow);
    border-radius: 2px;
    padding: 0.3rem 0.75rem;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--black);
    margin-bottom: 1.25rem;
    box-shadow: 3px 3px 0 rgba(245,197,24,0.3);
}

.login-brand h1 {
    color: var(--white);
    font-size: 2.8rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.05;
    margin-bottom: 0.75rem;
}

.login-brand h1 em {
    font-style: normal;
    color: var(--yellow);
}

.login-brand p {
    color: var(--gray-300);
    font-size: 1rem;
    max-width: 380px;
    line-height: 1.6;
    font-weight: 400;
}

/* Feature list */
.login-features {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: auto;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.feature-icon {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border: 2px solid;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.fi-yellow { border-color: var(--yellow); background: rgba(245,197,24,0.12); color: var(--yellow); }
.fi-lime   { border-color: var(--lime);   background: rgba(184,245,58,0.12);  color: var(--lime); }
.fi-blue   { border-color: var(--blue);   background: rgba(67,97,238,0.12);   color: var(--blue); }

.feature-text {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--gray-200);
    line-height: 1.3;
}

.feature-text small {
    display: block;
    font-weight: 400;
    color: var(--gray-500);
    font-size: 0.78rem;
    margin-top: 0.1rem;
}

/* Right: Form Panel */
.login-panel-right {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    background: var(--cream);
    position: relative;
}

/* Subtle dot pattern */
.login-panel-right::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(var(--gray-200) 1.5px, transparent 1.5px);
    background-size: 28px 28px;
    pointer-events: none;
    opacity: 0.5;
}

.login-form-wrap {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 440px;
}

/* Header of form */
.login-form-header {
    margin-bottom: 2.25rem;
}

.login-form-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--gray-500);
    margin-bottom: 0.6rem;
}

.login-form-eyebrow::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 2px;
    background: var(--gray-500);
}

.login-form-title {
    font-size: 2.2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.1;
    color: var(--black);
    margin-bottom: 0.5rem;
}

.login-form-title span {
    position: relative;
    display: inline-block;
}

.login-form-title span::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 2px;
    width: 100%;
    height: 6px;
    background: var(--yellow);
    z-index: -1;
}

.login-form-sub {
    font-size: 0.9rem;
    color: var(--gray-500);
    font-weight: 500;
}

/* The card containing the form */
.login-card {
    background: var(--white);
    border: 3px solid var(--black);
    border-radius: 4px;
    box-shadow: 8px 8px 0 var(--black);
    padding: 2rem 2.25rem;
}

/* Password wrapper */
.input-password-wrap {
    position: relative;
}

.input-password-wrap .nb-input {
    padding-right: 3rem;
}

.toggle-password {
    position: absolute;
    right: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    padding: 0.3rem;
    transition: color 0.15s;
}

.toggle-password:hover {
    color: var(--black);
}

/* Remember + Forgot row */
.login-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.nb-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    user-select: none;
}

.nb-checkbox input[type="checkbox"] {
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid var(--black);
    border-radius: 2px;
    background: var(--white);
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
    box-shadow: 2px 2px 0 var(--black);
    transition: background 0.12s;
}

.nb-checkbox input[type="checkbox"]:checked {
    background: var(--black);
}

.nb-checkbox input[type="checkbox"]:checked::after {
    content: '';
    position: absolute;
    left: 3px;
    top: 0px;
    width: 6px;
    height: 10px;
    border-right: 2.5px solid var(--white);
    border-bottom: 2.5px solid var(--white);
    transform: rotate(45deg);
}

.nb-checkbox span {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-700);
}

.forgot-link {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--blue);
    text-decoration: underline;
    text-underline-offset: 2px;
    transition: color 0.12s;
}

.forgot-link:hover { color: var(--blue-dark); }

/* Submit button override */
.btn-login {
    width: 100%;
    padding: 0.85rem 2rem;
    font-size: 1rem;
    font-weight: 800;
    background: var(--black);
    color: var(--yellow);
    border: 3px solid var(--black);
    border-radius: 4px;
    box-shadow: 5px 5px 0 var(--yellow);
    cursor: pointer;
    font-family: var(--font-sans);
    letter-spacing: 0.03em;
    transition: box-shadow 0.12s, transform 0.12s, background 0.12s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
}

.btn-login:hover {
    box-shadow: 8px 8px 0 var(--yellow);
    transform: translate(-2px, -2px);
}

.btn-login:active {
    box-shadow: 2px 2px 0 var(--yellow) !important;
    transform: translate(2px, 2px) !important;
}

.btn-login svg {
    transition: transform 0.2s;
}

.btn-login:hover svg {
    transform: translateX(4px);
}

/* Footer note */
.login-footer-note {
    text-align: center;
    margin-top: 1.75rem;
    font-size: 0.78rem;
    color: var(--gray-500);
    font-weight: 500;
    line-height: 1.6;
}

/* Version tag */
.version-tag {
    position: absolute;
    bottom: 1.5rem;
    right: 1.5rem;
    font-size: 0.7rem;
    font-family: var(--font-mono);
    color: var(--gray-300);
    background: var(--white);
    border: 2px solid var(--gray-200);
    padding: 0.2rem 0.6rem;
    border-radius: 2px;
}

/* Responsive */
@media (max-width: 900px) {
    .login-page {
        grid-template-columns: 1fr;
    }
    .login-panel-left {
        display: none;
    }
    .login-panel-right {
        min-height: 100vh;
    }
}
</style>
@endpush

@section('content')
<div class="login-page">

    {{-- ══ LEFT PANEL ══════════════════════════════════════ --}}
    <div class="login-panel-left">
        <div class="deco-shape deco-1"></div>
        <div class="deco-shape deco-2"></div>
        <div class="deco-shape deco-3"></div>
        <div class="deco-4"></div>

        {{-- Brand --}}
        <div class="login-brand anim-fade-up">
            <div class="login-brand-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Lingkungan Paroki
            </div>
            <h1>
                SPK<br><em>Bansos</em>
            </h1>
            <p>
                Sistem Pendukung Keputusan Bantuan Sosial berbasis metode SAW untuk distribusi bantuan yang adil dan tepat sasaran.
            </p>
        </div>

        {{-- Features --}}
        <div class="login-features anim-fade-up delay-2">
            <div class="feature-item">
                <div class="feature-icon fi-yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="feature-text">
                    Keputusan Transparan
                    <small>Berbasis metode SAW yang terukur</small>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon fi-lime">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div class="feature-text">
                    Validasi Berjenjang
                    <small>Dari Lingkungan → Stasi → Paroki</small>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon fi-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <div class="feature-text">
                    Akses Multi-peran
                    <small>Ketua Lingkungan, Stasi, dan Paroki</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ RIGHT PANEL ═════════════════════════════════════ --}}
    <div class="login-panel-right">
        <div class="login-form-wrap anim-fade-up">

            {{-- Header --}}
            <div class="login-form-header">
                <div class="login-form-eyebrow">Portal Masuk</div>
                <h2 class="login-form-title">
                    Selamat<br><span>Datang</span> Kembali
                </h2>
                <p class="login-form-sub">Masuk untuk mengelola data calon penerima bantuan.</p>
            </div>

            {{-- Alert Errors --}}
            @if ($errors->any())
            <div class="nb-alert alert-error anim-fade-up" role="alert" id="login-error-alert">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <strong>Login gagal!</strong>
                </div>
                <ul style="margin-top: 0.5rem; padding-left: 1.5rem; font-size: 0.88rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('status'))
            <div class="nb-alert alert-success anim-fade-up">
                {{ session('status') }}
            </div>
            @endif

            {{-- Form Card --}}
            <div class="login-card">
                <form action="{{ route('login') }}" method="POST" id="login-form" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label" for="email">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 3px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Alamat Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="nb-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ old('email') }}"
                            placeholder="contoh@parokiku.org"
                            autocomplete="email"
                            autofocus
                            required
                        >
                        @error('email')
                            <span class="form-error" id="email-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 3px;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Kata Sandi
                        </label>
                        <div class="input-password-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="nb-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="toggle-password" id="toggle-pwd" aria-label="Tampilkan/sembunyikan kata sandi">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Options Row --}}
                    <div class="login-options">
                        <label class="nb-checkbox" for="remember">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ingat saya</span>
                        </label>
                        <a href="#" class="forgot-link">Lupa kata sandi?</a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-login" id="login-btn">
                        <span id="btn-text">Masuk Sekarang</span>
                        <svg id="btn-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Footer Note --}}
            <div class="login-footer-note">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Akses terbatas untuk pengguna terdaftar.<br>
                Hubungi administrator jika Anda belum memiliki akun.
            </div>
        </div>

        <div class="version-tag" style="font-family: var(--font-mono);">SPK Bansos v2.0</div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Toggle password visibility
const toggleBtn = document.getElementById('toggle-pwd');
const pwdInput  = document.getElementById('password');
const eyeIcon   = document.getElementById('eye-icon');

const eyeOpenSVG = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
const eyeOffSVG  = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;

toggleBtn.addEventListener('click', () => {
    const isPass = pwdInput.type === 'password';
    pwdInput.type = isPass ? 'text' : 'password';
    eyeIcon.innerHTML = isPass ? eyeOffSVG : eyeOpenSVG;
    toggleBtn.style.color = isPass ? 'var(--blue)' : 'var(--gray-500)';
});

// Loading state on submit
const form    = document.getElementById('login-form');
const loginBtn = document.getElementById('login-btn');
const btnText  = document.getElementById('btn-text');

form.addEventListener('submit', (e) => {
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;

    if (!email || !pass) return; // let browser handle

    loginBtn.disabled = true;
    loginBtn.style.opacity = '0.75';
    btnText.textContent = 'Memproses...';
    loginBtn.querySelector('svg').innerHTML = `
        <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="50 100">
            <animateTransform attributeName="transform" type="rotate" dur="0.8s" repeatCount="indefinite" from="0 12 12" to="360 12 12"/>
        </circle>
    `;
});

// Input focus ring color tweak for errors
document.querySelectorAll('.nb-input.is-invalid').forEach(input => {
    input.addEventListener('input', () => {
        input.classList.remove('is-invalid');
    });
});
</script>
@endpush
