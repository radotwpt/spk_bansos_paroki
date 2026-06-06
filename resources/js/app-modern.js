import './bootstrap';
import './sw-register';
import { KetuaLingkunganStasiModule } from './modules/ketua-lingkungan-stasi.js';
import { StasiModule } from './modules/stasi.js';
import { KetuaLingkunganParokiModule } from './modules/ketua-lingkungan-paroki.js';
import { ParokiModule } from './modules/paroki.js';

// Utility Functions
function toggleSidebar(sidebar, overlay, toggle, force) {
    const isOpen = sidebar.style.display !== 'none';
    const shouldOpen = force !== undefined ? force : !isOpen;
    
    if (shouldOpen) {
        sidebar.style.display = '';
        overlay.style.display = '';
    } else {
        sidebar.style.display = 'none';
        overlay.style.display = 'none';
    }
    
    if (toggle) {
        toggle.setAttribute('aria-expanded', shouldOpen);
    }
}

function createMenuItemHtml(item, isActive = false) {
    const activeClass = isActive ? 'active' : '';
    return `
        <a class="menu-item ${activeClass}" href="#" data-menu="${item.id}" role="menuitem">
            <i class="bi bi-${item.icon}"></i>
            <span>${item.label}</span>
        </a>
    `;
}

function formatViewTitle(viewId) {
    const menuItem = Object.values(roleMenus)
        .flat()
        .find((item) => item.id === viewId);
    return menuItem?.label ?? viewId.replace(/-/g, ' ');
}

function showConfirmModal(title, message, confirmLabel = 'Konfirmasi', cancelLabel = 'Batal') {
    const modalElement = document.getElementById('confirm-modal');
    if (!modalElement || !window.bootstrap?.Modal) {
        return Promise.resolve(window.confirm(message));
    }

    return new Promise((resolve) => {
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
        const titleElement = document.getElementById('modal-title');
        const bodyElement = document.getElementById('modal-body');
        const confirmButton = document.getElementById('modal-confirm');
        const cancelButton = modalElement.querySelector('[data-bs-dismiss="modal"]');
        let settled = false;

        if (titleElement) titleElement.textContent = title;
        if (bodyElement) bodyElement.textContent = message;
        if (confirmButton) confirmButton.textContent = confirmLabel;
        if (cancelButton) cancelButton.textContent = cancelLabel;

        const cleanup = () => {
            modalElement.removeEventListener('hidden.bs.modal', onHidden);
            confirmButton?.removeEventListener('click', onConfirm);
        };

        const onConfirm = () => {
            settled = true;
            cleanup();
            modal.hide();
            resolve(true);
        };

        const onHidden = () => {
            cleanup();
            if (!settled) {
                resolve(false);
            }
        };

        confirmButton?.addEventListener('click', onConfirm, { once: true });
        modalElement.addEventListener('hidden.bs.modal', onHidden, { once: true });
        modal.show();
    });
}

const appRoot = document.getElementById('app');
const apiBase = appRoot?.dataset.apiBase ?? '/api/v1';
const tokenKey = 'spk_bansos_token';

// Role-based menu configuration dengan Bootstrap icons
const roleMenus = {
    super_admin: [
        { id: 'dashboard', label: 'Ringkasan', icon: 'graph-up' },
        { id: 'admin-master', label: 'Master Data', icon: 'gear' },
        { id: 'candidates', label: 'Calon Penerima', icon: 'people' },
        { id: 'activity-log', label: 'Log Aktivitas', icon: 'clipboard-data' },
        { id: 'documents', label: 'Dokumen', icon: 'file-text' },
    ],
    ketua_lingkungan_stasi: [
        { id: 'dashboard', label: 'Ringkasan', icon: 'graph-up' },
        { id: 'calon-penerima', label: 'Calon Penerima', icon: 'people' },
        { id: 'activity-log', label: 'Log Aktivitas', icon: 'clipboard-data' },
    ],
    stasi: [
        { id: 'dashboard', label: 'Ringkasan', icon: 'graph-up' },
        { id: 'stasi-recap', label: 'Rekap Stasi', icon: 'bar-chart' },
        { id: 'documents', label: 'Surat', icon: 'file-text' },
        { id: 'activity-log', label: 'Log Aktivitas', icon: 'clipboard-data' },
    ],
    ketua_lingkungan_paroki: [
        { id: 'dashboard', label: 'Ringkasan', icon: 'graph-up' },
        { id: 'saw', label: 'Proses SAW', icon: 'gear' },
        { id: 'activity-log', label: 'Log Aktivitas', icon: 'clipboard-data' },
    ],
    paroki: [
        { id: 'dashboard', label: 'Ringkasan', icon: 'graph-up' },
        { id: 'ranking', label: 'Ranking', icon: 'trophy' },
        { id: 'documents', label: 'Dokumen', icon: 'file-text' },
        { id: 'activity-log', label: 'Log Aktivitas', icon: 'clipboard-data' },
    ],
};

const roleLabels = {
    super_admin: 'Super Admin',
    paroki: 'Paroki',
    ketua_lingkungan_paroki: 'Ketua Lingkungan Paroki',
    stasi: 'Stasi',
    ketua_lingkungan_stasi: 'Ketua Lingkungan Stasi',
};

// Application state
const state = {
    token: localStorage.getItem(tokenKey),
    user: null,
    activeView: 'dashboard',
    sidebarOpen: window.innerWidth >= 1024,
    modules: {
        ketuaLingkunganStasi: null,
        stasi: null,
        ketuaLingkunganParoki: null,
        paroki: null,
    },
};

// DOM Elements
const els = {
    // Screens
    loginScreen: document.getElementById('login-screen'),
    shellScreen: document.getElementById('shell-screen'),

    // Login Form
    loginForm: document.getElementById('login-form'),
    loginEmail: document.getElementById('login-email'),
    loginPassword: document.getElementById('login-password'),
    loginSubmit: document.getElementById('login-submit'),
    loginError: document.getElementById('login-error'),
    demoUsers: document.querySelectorAll('.demo-user'),

    // Sidebar & Navigation
    sidebar: document.getElementById('sidebar'),
    sidebarOverlay: document.getElementById('sidebar-overlay'),
    mainMenu: document.getElementById('main-menu'),
    menuToggle: document.getElementById('toggle-sidebar'),

    // Topbar
    pageTitle: document.getElementById('page-title'),
    userName: document.getElementById('user-name'),
    userEmail: document.getElementById('user-email'),

    // Content Areas
    statusRegion: document.getElementById('status-region'),
    contentRegion: document.getElementById('content-region'),

    // Actions
    logoutButton: document.getElementById('logout-button'),
};

/**
 * API Helper - Make requests dengan error handling
 */
async function api(path, options = {}) {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
    };

    if (state.token) {
        headers.Authorization = `Bearer ${state.token}`;
    }

    try {
        const response = await fetch(`${apiBase}${path}`, {
            ...options,
            headers,
        });

        const body = await response.json().catch(() => ({}));

        // Handle unauthorized
        if (response.status === 401) {
            clearSession();
            showLogin('Sesi berakhir. Silakan masuk kembali.');
            return null;
        }

        if (!response.ok) {
            const error = new Error(body.message || `Error: ${response.status}`);
            error.status = response.status;
            error.body = body;
            throw error;
        }

        return body;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * Session Management
 */
function setSession(token, user) {
    state.token = token;
    state.user = user;
    localStorage.setItem(tokenKey, token);
}

function clearSession() {
    state.token = null;
    state.user = null;
    localStorage.removeItem(tokenKey);
}

function isAuthenticated() {
    return !!state.token && !!state.user;
}

/**
 * View Management
 */
function showLogin(message = '') {
    els.shellScreen.hidden = true;
    els.loginScreen.hidden = false;
    els.loginSubmit.disabled = false;

    if (message) {
        showLoginError(message);
    }
}

async function showShell() {
    els.loginScreen.hidden = true;
    els.shellScreen.hidden = false;
    els.loginScreen.style.display = 'none';
    els.shellScreen.style.display = '';

    if (els.sidebar) {
        els.sidebar.style.display = state.sidebarOpen ? '' : 'none';
    }
    if (els.sidebarOverlay) {
        els.sidebarOverlay.style.display = 'none';
    }

    // Update user info
    if (state.user) {
        els.userName.textContent = state.user.name;
        els.userEmail.textContent = state.user.email;
        initializeModules();
    }

    // Rebuild menu and render default view
    renderMenu();
    loadView(state.activeView);
}

function initializeModules() {
    // Initialize Ketua Lingkungan Stasi module
    if (state.user?.role === 'ketua_lingkungan_stasi' && !state.modules.ketuaLingkunganStasi) {
        state.modules.ketuaLingkunganStasi = new KetuaLingkunganStasiModule();
    }
    
    // Initialize Stasi module
    if (state.user?.role === 'stasi' && !state.modules.stasi) {
        state.modules.stasi = new StasiModule();
    }

    // Initialize Ketua Lingkungan Paroki module
    if (state.user?.role === 'ketua_lingkungan_paroki' && !state.modules.ketuaLingkunganParoki) {
        state.modules.ketuaLingkunganParoki = new KetuaLingkunganParokiModule();
    }

    // Initialize Paroki module
    if (state.user?.role === 'paroki' && !state.modules.paroki) {
        state.modules.paroki = new ParokiModule();
    }
}

function showLoginError(message) {
    els.loginError.textContent = message;
    els.loginError.hidden = false;
    els.loginError.classList.remove('d-none');
}

function clearLoginError() {
    els.loginError.textContent = '';
    els.loginError.hidden = true;
    els.loginError.classList.add('d-none');
}

function setPageTitle(viewId) {
    els.pageTitle.textContent = formatViewTitle(viewId);
}

function escapeHtml(value) {
    const str = String(value ?? '');
    return str.replace(/[&<>"']/g, (char) => {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char];
    });
}

/**
 * Menu Rendering
 */
function renderMenu() {
    if (!state.user) return;

    const menus = roleMenus[state.user.role] || [];
    els.mainMenu.innerHTML = menus
        .map((item) => createMenuItemHtml(item, state.activeView === item.id))
        .join('');

    // Add event listeners to menu items
    els.mainMenu.querySelectorAll('.menu-item').forEach((item) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const menuId = item.dataset.menu;
            handleMenuClick(menuId);
        });
    });
}

function handleMenuClick(menuId) {
    // Close sidebar on mobile
    if (window.innerWidth < 1024) {
        toggleSidebar(els.sidebar, els.sidebarOverlay, els.menuToggle, false);
    }

    // Update active state
    els.mainMenu.querySelectorAll('.menu-item').forEach((item) => {
        item.classList.toggle('active', item.dataset.menu === menuId);
    });

    // Load view
    loadView(menuId);
}

async function loadView(viewId) {
    state.activeView = viewId;
    setPageTitle(viewId);

    // Show loading state
    els.contentRegion.innerHTML = `
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3 small">Memuat konten...</p>
        </div>
    `;

    // Route to module or template
    setTimeout(() => {
        if (viewId === 'calon-penerima' && state.modules.ketuaLingkunganStasi) {
            state.modules.ketuaLingkunganStasi.init(state.token, apiBase);
        } else if (viewId === 'stasi-recap' && state.modules.stasi) {
            state.modules.stasi.init(state.token, apiBase);
        } else if (viewId === 'saw' && state.modules.ketuaLingkunganParoki) {
            state.modules.ketuaLingkunganParoki.init(state.token, apiBase);
        } else if (viewId === 'ranking' && state.modules.paroki) {
            state.modules.paroki.init(state.token, apiBase);
        } else {
            renderViewContent(viewId);
        }
    }, 500);
}

function renderViewContent(viewId) {
    let content = '';

    const viewTemplates = {
        dashboard: `
            <div class="space-y-6">
                <h1 class="text-3xl font-bold text-neutral-900">Selamat datang, ${escapeHtml(state.user?.name || 'User')}!</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-neutral-700">Total Calon</h3>
                            <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646zM3 20h18a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-neutral-900">245</p>
                        <p class="text-sm text-neutral-500">+12 minggu ini</p>
                    </div>

                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-neutral-700">Disetujui</h3>
                            <svg class="w-8 h-8 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-neutral-900">156</p>
                        <p class="text-sm text-neutral-500">64% dari total</p>
                    </div>

                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-neutral-700">Ditinjau</h3>
                            <svg class="w-8 h-8 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-neutral-900">67</p>
                        <p class="text-sm text-neutral-500">27% dari total</p>
                    </div>

                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-neutral-700">Ditolak</h3>
                            <svg class="w-8 h-8 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-neutral-900">22</p>
                        <p class="text-sm text-neutral-500">9% dari total</p>
                    </div>
                </div>

                <div class="card">
                    <h2 class="text-xl font-semibold text-neutral-900 mb-4">Aktivitas Terbaru</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-3 border-b border-neutral-200">
                            <div>
                                <p class="font-medium text-neutral-900">Calon baru ditambahkan</p>
                                <p class="text-sm text-neutral-500">Oleh ${escapeHtml(state.user?.name || 'User')}</p>
                            </div>
                            <span class="text-xs text-neutral-500">5 menit lalu</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-neutral-200">
                            <div>
                                <p class="font-medium text-neutral-900">Perubahan status</p>
                                <p class="text-sm text-neutral-500">Calon 001 disetujui</p>
                            </div>
                            <span class="text-xs text-neutral-500">15 menit lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        `,
        'admin-master': `
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-neutral-900">Master Data</h1>
                    <button class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Data
                    </button>
                </div>
                <div class="card">
                    <p class="text-neutral-600">Master data akan ditampilkan di sini</p>
                </div>
            </div>
        `,
        candidates: `
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-neutral-900">Calon Penerima</h1>
                    <button class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Input Calon
                    </button>
                </div>
                <div class="card">
                    <p class="text-neutral-600">Daftar calon penerima akan ditampilkan di sini</p>
                </div>
            </div>
        `,
        'activity-log': `
            <div class="space-y-6">
                <h1 class="text-3xl font-bold text-neutral-900">Log Aktivitas</h1>
                <div class="card">
                    <p class="text-neutral-600">Log aktivitas sistem akan ditampilkan di sini</p>
                </div>
            </div>
        `,
    };

    content = viewTemplates[viewId] || `
        <div class="text-center py-12">
            <h1 class="text-3xl font-bold text-neutral-900 mb-2">Page Not Found</h1>
            <p class="text-neutral-600">Halaman ${escapeHtml(viewId)} belum diimplementasikan</p>
        </div>
    `;

    els.contentRegion.innerHTML = content;
}

/**
 * Event Listeners - Login Form
 */
els.loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearLoginError();

    const email = els.loginEmail.value.trim();
    const password = els.loginPassword.value;

    if (!email || !password) {
        showLoginError('Email dan password harus diisi');
        return;
    }

    els.loginSubmit.disabled = true;
    const originalText = els.loginSubmit.textContent;
    els.loginSubmit.textContent = 'Memproses...';

    try {
        const response = await api('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });

        if (response?.data?.token && response?.data?.user) {
            setSession(response.data.token, response.data.user);
            showShell();
        } else {
            showLoginError('Login gagal. Periksa kembali kredensial Anda.');
        }
    } catch (error) {
        const message = error.body?.message || 'Login gagal. Silakan coba lagi.';
        showLoginError(message);
    } finally {
        els.loginSubmit.disabled = false;
        els.loginSubmit.textContent = originalText;
    }
});

/**
 * Event Listeners - Demo Users
 */
els.demoUsers.forEach((btn) => {
    btn.addEventListener('click', () => {
        const email = btn.dataset.email;
        els.loginEmail.value = email;
        els.loginPassword.value = 'password';
        els.loginForm.dispatchEvent(new Event('submit'));
    });
});

/**
 * Event Listeners - Navigation
 */
els.menuToggle.addEventListener('click', () => {
    toggleSidebar(els.sidebar, els.sidebarOverlay, els.menuToggle);
});

els.sidebarOverlay?.addEventListener('click', () => {
    toggleSidebar(els.sidebar, els.sidebarOverlay, els.menuToggle, false);
});

els.logoutButton?.addEventListener('click', async () => {
    const confirmed = await showConfirmModal(
        'Konfirmasi Logout',
        'Apakah Anda yakin ingin keluar dari sistem?',
        'Keluar',
        'Batal'
    );

    if (confirmed) {
        try {
            await api('/auth/logout', { method: 'POST' });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            clearSession();
            showLogin('Anda telah keluar dari sistem.');
        }
    }
});

/**
 * Responsive sidebar handling
 */
window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        els.sidebar?.classList.remove('-translate-x-full');
        els.sidebarOverlay?.classList.add('hidden');
    }
});

/**
 * Initialize App
 */
function initializeApp() {
    if (initializeApp.initialized) {
        return;
    }
    initializeApp.initialized = true;

    if (isAuthenticated()) {
        showShell();
    } else {
        showLogin();
    }
}

// Start app
document.addEventListener('DOMContentLoaded', initializeApp);

// Fallback for already-loaded DOM
if (document.readyState !== 'loading') {
    initializeApp();
}
