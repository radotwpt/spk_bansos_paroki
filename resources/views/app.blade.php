<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#174E4A">
  <meta name="description" content="SPK Bansos - Sistem Pendukung Keputusan Bantuan Sosial">
  <title>SPK Bansos - Sistem Pendukung Keputusan</title>
  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%23174e4a' width='100' height='100' rx='20'/><text x='50' y='70' font-size='60' font-weight='bold' fill='white' text-anchor='middle' font-family='sans-serif'>S</text></svg>">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @vite(['resources/css/app.css', 'resources/js/app-modern.js'])
</head>
<body>
  <div id="app" class="app-root" data-api-base="/api/v1">
    <!-- ====== LOGIN SCREEN ====== -->
    <div id="login-screen" class="auth-screen">
      <div class="auth-panel">
        <div class="mb-4">
          <div class="d-flex align-items-center" style="gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #174e4a 0%, #0d3734 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.75rem;">
              <i class="bi bi-graph-up"></i>
            </div>
            <div>
              <h1 class="h4 mb-0 fw-bold text-primary">SPK Bansos</h1>
              <small class="text-muted">Sistem Pendukung Keputusan</small>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <h2 class="h5 fw-bold mb-2">Masuk ke Akun Anda</h2>
          <p class="text-muted mb-0">Masukkan email dan password untuk melanjutkan.</p>
        </div>

        <form id="login-form" class="needs-validation" novalidate>
          <div class="mb-3">
            <label for="login-email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="login-email" name="email" value="test@example.com" placeholder="nama@contoh.com" required>
          </div>

          <div class="mb-3">
            <label for="login-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="login-password" name="password" value="password" placeholder="••••••••" required>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3 small">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember" name="remember">
              <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <a href="#" class="text-decoration-none">Lupa password?</a>
          </div>

          <div id="login-error" class="alert alert-danger d-none" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <span id="error-text"></span>
          </div>

          <button type="submit" id="login-submit" class="btn btn-primary w-100 fw-bold mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Aplikasi
          </button>
        </form>

        <hr class="my-3">

        <div class="mb-3">
          <p class="eyebrow">Akun Demo untuk Testing</p>
          <div class="row g-2">
            <div class="col-6">
              <button class="btn btn-outline-primary btn-sm w-100 demo-user" type="button" data-email="admin@example.com" style="text-align: left;">
                <div class="fw-bold">Admin</div>
                <small class="text-muted d-block">Super Admin</small>
              </button>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-primary btn-sm w-100 demo-user" type="button" data-email="test@example.com" style="text-align: left;">
                <div class="fw-bold">Ketua Stasi</div>
                <small class="text-muted d-block">Stasi Head</small>
              </button>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-primary btn-sm w-100 demo-user" type="button" data-email="stasi@example.com" style="text-align: left;">
                <div class="fw-bold">Stasi</div>
                <small class="text-muted d-block">Staff</small>
              </button>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-primary btn-sm w-100 demo-user" type="button" data-email="ketua.paroki@example.com" style="text-align: left;">
                <div class="fw-bold">Ketua Paroki</div>
                <small class="text-muted d-block">Parish Head</small>
              </button>
            </div>
          </div>
        </div>

        <div class="alert alert-info py-2 small" role="info">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Tip:</strong> Gunakan akun demo untuk testing atau masuk dengan kredensial Anda sendiri.
        </div>
      </div>
    </div>

    <!-- ====== APPLICATION SHELL ====== -->
    <div id="shell-screen" class="d-flex" style="min-height: 100vh; display: none;" hidden>
      <!-- Sidebar -->
      <nav id="sidebar" class="sidebar" style="width: 280px; display: none;">
        <div class="sidebar-brand">
          <i class="bi bi-graph-up text-primary" style="font-size: 1.5rem;"></i>
          <div>
            <div class="fw-bold text-primary">SPK Bansos</div>
            <small class="text-muted d-block">v2.0</small>
          </div>
        </div>

        <div class="sidebar-menu" style="flex: 1; overflow-y: auto;">
          <nav id="main-menu" class="nav flex-column"></nav>
        </div>

        <div class="sidebar-footer p-3 border-top small text-muted">
          <div class="d-flex align-items-center" style="gap: 0.5rem;">
            <span class="badge bg-success rounded-circle" style="width: 8px; height: 8px;"></span>
            <span>Connected</span>
          </div>
        </div>
      </nav>

      <!-- Main Content -->
      <div class="main-content" style="flex: 1; display: flex; flex-direction: column;">
        <!-- Topbar -->
        <header class="navbar navbar-expand-lg border-bottom" style="background-color: white;">
          <div class="container-fluid px-4">
            <button id="toggle-sidebar" class="btn btn-sm btn-outline-secondary d-lg-none me-2" type="button" aria-label="Toggle sidebar">
              <i class="bi bi-list"></i>
            </button>

            <div style="flex: 1;">
              <p class="eyebrow mb-0">Dashboard</p>
              <h1 id="page-title" class="h4 mb-0 fw-bold">Ringkasan</h1>
            </div>

            <form class="d-none d-md-flex me-3" style="flex-shrink: 0;">
              <input class="form-control form-control-sm" type="search" placeholder="Cari..." aria-label="Search">
            </form>

            <div class="d-flex align-items-center" style="gap: 0.5rem; flex-shrink: 0;">
              <button class="btn btn-sm btn-link position-relative" type="button" style="text-decoration: none;">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger rounded-circle position-absolute" style="top: -8px; right: -8px; width: 16px; height: 16px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 0.625rem;">3</span>
              </button>

              <div class="dropdown">
                <button class="btn btn-sm btn-link dropdown-toggle" type="button" id="user-menu" data-bs-toggle="dropdown" style="text-decoration: none;">
                  <img class="rounded-circle" src="https://ui-avatars.com/api/?name=User&background=174e4a&color=fff" alt="User" style="width: 32px; height: 32px;">
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-menu">
                  <li><h6 class="dropdown-header" id="user-name">User Name</h6></li>
                  <li><small class="dropdown-header text-muted" id="user-email">user@example.com</small></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><button id="logout-button" class="dropdown-item" type="button"><i class="bi bi-box-arrow-right me-2"></i>Logout</button></li>
                </ul>
              </div>
            </div>
          </div>
        </header>

        <!-- Status Region -->
        <div id="status-region" class="px-4 py-2"></div>

        <!-- Content Region -->
        <main id="content-region" style="flex: 1; padding: 1.5rem; overflow-y: auto;"></main>
      </div>

      <!-- Sidebar Overlay -->
      <div id="sidebar-overlay" class="position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index: 1029; display: none;"></div>
    </div>
  </div>

  <!-- Modal for confirmations -->
  <div id="confirm-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="modal-title">Konfirmasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modal-body">Apakah Anda yakin?</div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="modal-confirm">Konfirmasi</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading spinner template -->
  <template id="loading-template">
    <div class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-muted mt-3 small">Memuat konten...</p>
    </div>
  </template>
</body>
</html>
