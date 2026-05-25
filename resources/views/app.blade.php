<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#174E4A" />
    <title>SPK Bansos</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
    <div id="app" class="app-root" data-api-base="/api/v1">
      <section id="login-screen" class="auth-screen" aria-labelledby="login-title">
        <div class="auth-panel">
          <div class="brand-mark">SPK</div>
          <div>
            <p class="eyebrow">Sistem Pendukung Keputusan</p>
            <h1 id="login-title">SPK Bansos</h1>
            <p class="auth-copy">Masuk untuk mengelola pendataan, verifikasi, ranking, dan keputusan bantuan sosial.</p>
          </div>

          <form id="login-form" class="auth-form" novalidate>
            <label>
              Email
              <input id="login-email" name="email" type="email" autocomplete="email" value="test@example.com" required />
            </label>

            <label>
              Password
              <input id="login-password" name="password" type="password" autocomplete="current-password" value="password" required />
            </label>

            <p id="login-error" class="form-error" hidden></p>
            <button id="login-submit" class="primary-button" type="submit">Masuk</button>
          </form>

          <div class="demo-users" aria-label="Akun demo">
            <button class="demo-user" type="button" data-email="admin@example.com">Super Admin</button>
            <button class="demo-user" type="button" data-email="test@example.com">Ketua Stasi</button>
            <button class="demo-user" type="button" data-email="stasi@example.com">Stasi</button>
            <button class="demo-user" type="button" data-email="ketua.paroki@example.com">Ketua Paroki</button>
            <button class="demo-user" type="button" data-email="paroki@example.com">Paroki</button>
          </div>
        </div>
      </section>

      <section id="shell-screen" class="shell" hidden>
        <aside class="sidebar" aria-label="Navigasi utama">
          <div class="sidebar-brand">
            <span class="brand-mark compact">SPK</span>
            <div>
              <strong>SPK Bansos</strong>
              <small id="sidebar-role">Role</small>
            </div>
          </div>
          <nav id="main-menu" class="main-menu"></nav>
        </aside>

        <main class="workspace">
          <header class="topbar">
            <button id="menu-toggle" class="ghost-button menu-toggle" type="button" aria-label="Buka menu">Menu</button>
            <div>
              <p class="eyebrow">Dashboard</p>
              <h2 id="page-title">Ringkasan</h2>
            </div>
            <div class="user-box">
              <div>
                <strong id="user-name">User</strong>
                <span id="user-email">email@example.com</span>
              </div>
              <button id="logout-button" class="ghost-button" type="button">Keluar</button>
            </div>
          </header>

          <section id="status-region" class="status-region" aria-live="polite"></section>
          <section id="content-region" class="content-region"></section>
        </main>
      </section>
    </div>
  </body>
</html>
