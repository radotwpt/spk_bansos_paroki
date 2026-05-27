<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#174E4A" />
    <meta name="description" content="SPK Bansos - Sistem Pendukung Keputusan Bantuan Sosial" />
    <title>SPK Bansos - Sistem Pendukung Keputusan</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%23174e4a' width='100' height='100' rx='20'/><text x='50' y='70' font-size='60' font-weight='bold' fill='white' text-anchor='middle' font-family='sans-serif'>S</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
    <div id="app" class="app-root" data-api-base="/api/v1">
      <!-- ====== LOGIN SCREEN ====== -->
      <section id="login-screen" class="auth-screen" aria-labelledby="login-title">
        <div class="auth-panel">
          <div class="flex items-center gap-4 mb-8">
            <div class="brand-mark">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-neutral-900">SPK Bansos</h1>
              <p class="text-sm text-neutral-500">Sistem Pendukung Keputusan</p>
            </div>
          </div>

          <div class="space-y-2 mb-6">
            <h2 id="login-title" class="text-xl font-semibold text-neutral-900">Masuk ke Akun Anda</h2>
            <p class="text-sm text-neutral-600">Kelola pendataan, verifikasi, ranking, dan keputusan bantuan sosial dengan mudah.</p>
          </div>

          <form id="login-form" class="auth-form space-y-4" novalidate>
            <div class="form-group">
              <label for="login-email" class="block text-sm font-medium text-neutral-700 mb-2">Email Address</label>
              <input 
                id="login-email" 
                name="email" 
                type="email" 
                autocomplete="email" 
                value="test@example.com" 
                class="input" 
                placeholder="nama@contoh.com"
                required 
              />
            </div>

            <div class="form-group">
              <label for="login-password" class="block text-sm font-medium text-neutral-700 mb-2">Password</label>
              <input 
                id="login-password" 
                name="password" 
                type="password" 
                autocomplete="current-password" 
                value="password" 
                class="input" 
                placeholder="••••••••"
                required 
              />
            </div>

            <div class="flex items-center justify-between text-sm">
              <label class="flex items-center gap-2">
                <input type="checkbox" class="w-4 h-4 rounded border-neutral-300" />
                <span class="text-neutral-700">Ingat saya</span>
              </label>
              <a href="#" class="text-primary-600 hover:text-primary-700">Lupa password?</a>
            </div>

            <p id="login-error" class="form-error hidden" role="alert"></p>
            
            <button id="login-submit" class="btn-primary w-full" type="submit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2m14-4V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4m14 0a2 2 0 01-2 2H7a2 2 0 01-2-2m14 0V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4"/>
              </svg>
              Masuk ke Aplikasi
            </button>
          </form>

          <div class="border-t border-neutral-200 pt-6">
            <p class="text-xs text-neutral-600 font-medium uppercase tracking-wider mb-3">Akun Demo untuk Testing</p>
            <div class="grid grid-cols-2 gap-2" aria-label="Demo accounts">
              <button class="demo-user text-xs" type="button" data-email="admin@example.com" title="Full access">
                <span class="block font-semibold">Admin</span>
                <span class="text-xs text-neutral-500">Super Admin</span>
              </button>
              <button class="demo-user text-xs" type="button" data-email="test@example.com" title="Head of Stasi">
                <span class="block font-semibold">Ketua Stasi</span>
                <span class="text-xs text-neutral-500">Stasi Head</span>
              </button>
              <button class="demo-user text-xs" type="button" data-email="stasi@example.com" title="Stasi staff">
                <span class="block font-semibold">Stasi</span>
                <span class="text-xs text-neutral-500">Staff</span>
              </button>
              <button class="demo-user text-xs" type="button" data-email="ketua.paroki@example.com" title="Head of Parish">
                <span class="block font-semibold">Ketua Paroki</span>
                <span class="text-xs text-neutral-500">Parish Head</span>
              </button>
            </div>
          </div>

          <div class="mt-6 p-4 rounded-lg bg-primary-50 border border-primary-200">
            <p class="text-xs text-primary-700">
              <strong>💡 Tip:</strong> Gunakan kredensial demo untuk mengeksplorasi sistem dengan peran yang berbeda.
            </p>
          </div>
        </div>
      </section>

      <!-- ====== MAIN SHELL ====== -->
      <section id="shell-screen" class="shell" hidden>
        <!-- SIDEBAR -->
        <aside class="sidebar" aria-label="Navigasi utama" id="sidebar-nav">
          <div class="sidebar-brand">
            <span class="brand-mark compact">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002 2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </span>
            <div>
              <strong class="text-neutral-900 block">SPK Bansos</strong>
              <small id="sidebar-role" class="text-neutral-500">Loading...</small>
            </div>
          </div>
          
          <nav id="main-menu" class="main-menu" role="navigation">
            <!-- Menu items akan diisi via JavaScript -->
          </nav>

          <div class="border-t border-neutral-200 p-4 mt-auto">
            <p class="text-xs font-semibold text-neutral-600 uppercase tracking-wider mb-3">Informasi</p>
            <div class="space-y-2 text-xs text-neutral-600">
              <div>
                <p class="font-medium">Versi Aplikasi</p>
                <p>1.0.0</p>
              </div>
              <div>
                <p class="font-medium">Database</p>
                <p id="db-status" class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-success-500 inline-block"></span> Connected</p>
              </div>
            </div>
          </div>
        </aside>

        <!-- MAIN WORKSPACE -->
        <main class="workspace">
          <!-- TOPBAR -->
          <header class="topbar" role="banner">
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <button 
                id="menu-toggle" 
                class="btn-ghost" 
                type="button" 
                aria-label="Toggle sidebar"
                aria-expanded="false"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
              </button>

              <div class="flex-1 min-w-0">
                <p class="eyebrow">Dashboard</p>
                <h2 id="page-title" class="text-2xl font-bold text-neutral-900 truncate">Ringkasan</h2>
              </div>
            </div>

            <!-- TOPBAR RIGHT -->
            <div class="flex items-center gap-4">
              <!-- Search -->
              <div class="hidden md:flex relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                  type="search" 
                  id="topbar-search" 
                  placeholder="Cari..." 
                  class="pl-9 pr-4 py-2 text-sm border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <!-- Notifications -->
              <button class="btn-ghost relative" type="button" aria-label="Notifications">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-danger-500 rounded-full"></span>
              </button>

              <!-- User Menu -->
              <div class="flex items-center gap-3 pl-4 border-l border-neutral-200">
                <div class="hidden sm:block text-right">
                  <strong id="user-name" class="block text-sm text-neutral-900">User Name</strong>
                  <span id="user-email" class="text-xs text-neutral-500">email@example.com</span>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-white font-semibold cursor-pointer hover:shadow-lg transition-all" id="user-avatar">
                  U
                </div>

                <!-- Dropdown Menu -->
                <div class="relative group">
                  <button class="btn-ghost p-2" type="button" aria-label="User menu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                  
                  <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-neutral-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-250 z-50">
                    <a href="#" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 first:rounded-t-lg">Profil Saya</a>
                    <a href="#" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50">Pengaturan</a>
                    <a href="#" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50">Bantuan</a>
                    <hr class="border-neutral-200 my-1">
                    <button id="logout-button" class="w-full text-left px-4 py-2.5 text-sm text-danger-600 hover:bg-danger-50 last:rounded-b-lg" type="button">
                      Keluar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </header>

          <!-- STATUS REGION -->
          <section id="status-region" class="status-region" aria-live="polite" aria-atomic="true">
            <!-- Alert messages will appear here -->
          </section>

          <!-- CONTENT REGION -->
          <section id="content-region" class="content-region">
            <!-- Page content will be loaded here -->
          </section>
        </main>
      </section>
    </div>

    <!-- Mobile Overlay for Sidebar -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 lg:hidden hidden z-30" aria-hidden="true"></div>
  </body>
</html>
