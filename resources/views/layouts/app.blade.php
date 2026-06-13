<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — SPK Bansos</title>
    <meta name="description" content="@yield('meta_description', 'Dashboard Sistem Pendukung Keputusan Bantuan Sosial.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

    {{-- ══ NAVBAR ══════════════════════════════════════════ --}}
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ route('dashboard') }}" class="navbar-brand">
                <div class="brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0a0a0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div>
                    <span class="brand-name">SPK Bansos</span>
                    <span class="brand-sub">{{ Auth::user()->lingkungan?->name ?? Auth::user()->stasi?->name ?? Auth::user()->paroki?->name ?? 'Dashboard' }}</span>
                </div>
            </a>

            <ul class="navbar-nav">
                <li>
                    <span style="color: var(--gray-300); font-size: 0.85rem; font-weight: 600; padding: 0.4rem 0.9rem;">
                        {{ Auth::user()->name }}
                    </span>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="color: var(--white); border-color: var(--gray-700); box-shadow: none; font-size: 0.82rem; padding: 0.4rem 0.9rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    {{-- ══ BODY ════════════════════════════════════════════ --}}
    <div class="page-wrapper">

        {{-- ── SIDEBAR ─────────────────────────────────── --}}
        <aside class="sidebar">
            <span class="sidebar-section-title">Menu</span>

            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('stasi.dashboard') || request()->routeIs('ketua-lingkungan.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>

            @if(Auth::user()->hasRole('ketua_lingkungan_stasi'))
            <span class="sidebar-section-title" style="margin-top:0.75rem;">Calon Penerima</span>
            <a href="{{ route('ketua-lingkungan-stasi.calons.index') }}"
               class="sidebar-link {{ request()->routeIs('ketua-lingkungan-stasi.calons.*') && !request()->routeIs('ketua-lingkungan-stasi.calons.create') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Daftar Calon
            </a>
            <a href="{{ route('ketua-lingkungan-stasi.calons.create') }}"
               class="sidebar-link {{ request()->routeIs('ketua-lingkungan-stasi.calons.create') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                Tambah Calon
            </a>
            @endif

            @if(Auth::user()->hasRole('stasi'))
            <span class="sidebar-section-title" style="margin-top:0.75rem;">Validasi Stasi</span>
            
            <a href="{{ route('stasi.calons.index', ['status' => 'submitted_to_stasi']) }}"
               class="sidebar-link {{ request()->routeIs('stasi.calons.index') && request('status', 'submitted_to_stasi') === 'submitted_to_stasi' ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Pengajuan Masuk
                @php
                    $pendingCount = \App\Models\CalonPenerima::where('stasi_id', Auth::user()->stasi_id)
                        ->where('status', 'submitted_to_stasi')->count();
                @endphp
                @if($pendingCount > 0)
                <span style="background:var(--yellow);color:var(--black);font-size:0.65rem;font-weight:800;padding:0.1rem 0.4rem;border-radius:2px;margin-left:auto;border:1.5px solid var(--black);">{{ $pendingCount }}</span>
                @endif
            </a>
            
            <a href="{{ route('stasi.calons.index', ['status' => 'approved_by_stasi']) }}"
               class="sidebar-link {{ request()->routeIs('stasi.calons.index') && request('status') === 'approved_by_stasi' ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Disetujui
            </a>
            
            <a href="{{ route('stasi.calons.index', ['status' => 'rejected']) }}"
               class="sidebar-link {{ request()->routeIs('stasi.calons.index') && request('status') === 'rejected' ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                Ditolak
            </a>
            
            <a href="{{ route('stasi.surat-permohonan.index') }}"
               class="sidebar-link {{ request()->routeIs('stasi.surat-permohonan.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Surat Pengantar
            </a>
            @endif

            @if(Auth::user()->hasRole('stasi') || Auth::user()->hasRole('admin'))
            <span class="sidebar-section-title" style="margin-top:0.75rem;">Pengaturan & Manajemen</span>
            
            @if(Auth::user()->hasRole('stasi'))
            <a href="{{ route('stasi.profile.edit') }}"
               class="sidebar-link {{ request()->routeIs('stasi.profile.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Profil Stasi
            </a>
            @endif
            <a href="{{ route('users.index') }}"
               class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Kelola Pengguna
            </a>
            @endif
        </aside>

        {{-- ── MAIN CONTENT ───────────────────────────── --}}
        <main class="page-content">
            @if (session('success'))
                <div class="nb-alert alert-success anim-fade-up" style="margin-bottom: 1.5rem;">
                    <span>✓</span> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="nb-alert alert-error anim-fade-up" style="margin-bottom: 1.5rem;">
                    <span>✕</span> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>
</html>
