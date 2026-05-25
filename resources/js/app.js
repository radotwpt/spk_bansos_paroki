import './bootstrap';

const appRoot = document.getElementById('app');
const apiBase = appRoot?.dataset.apiBase ?? '/api/v1';
const tokenKey = 'spk_bansos_token';

const roleLabels = {
    super_admin: 'Super Admin',
    paroki: 'Paroki',
    ketua_lingkungan_paroki: 'Ketua Lingkungan Paroki',
    stasi: 'Stasi',
    ketua_lingkungan_stasi: 'Ketua Lingkungan Stasi',
};

const roleMenus = {
    super_admin: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'admin-master', label: 'Master Data' },
        { id: 'candidates', label: 'Calon Penerima' },
        { id: 'documents', label: 'Dokumen' },
    ],
    ketua_lingkungan_stasi: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'my-candidates', label: 'Calon Saya' },
        { id: 'candidate-form', label: 'Input Calon' },
        { id: 'offline', label: 'Offline' },
    ],
    stasi: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'stasi-recap', label: 'Rekap Stasi' },
    ],
    ketua_lingkungan_paroki: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'saw', label: 'Proses SAW' },
    ],
    paroki: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'ranking', label: 'Ranking' },
        { id: 'documents', label: 'Surat' },
    ],
};

const state = {
    token: localStorage.getItem(tokenKey),
    user: null,
    activeView: 'dashboard',
    menuOpen: false,
    master: {
        resource: 'stasis',
        per_page: 10,
        q: '',
        page: 1,
    },
};

const els = {
    loginScreen: document.getElementById('login-screen'),
    shellScreen: document.getElementById('shell-screen'),
    loginForm: document.getElementById('login-form'),
    loginEmail: document.getElementById('login-email'),
    loginPassword: document.getElementById('login-password'),
    loginSubmit: document.getElementById('login-submit'),
    loginError: document.getElementById('login-error'),
    demoUsers: document.querySelectorAll('.demo-user'),
    mainMenu: document.getElementById('main-menu'),
    pageTitle: document.getElementById('page-title'),
    sidebarRole: document.getElementById('sidebar-role'),
    userName: document.getElementById('user-name'),
    userEmail: document.getElementById('user-email'),
    logoutButton: document.getElementById('logout-button'),
    menuToggle: document.getElementById('menu-toggle'),
    statusRegion: document.getElementById('status-region'),
    contentRegion: document.getElementById('content-region'),
};

async function api(path, options = {}) {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
    };

    if (state.token) {
        headers.Authorization = `Bearer ${state.token}`;
    }

    const response = await fetch(`${apiBase}${path}`, {
        ...options,
        headers,
    });

    const body = await response.json().catch(() => ({}));

    if (response.status === 401) {
        clearSession();
        showLogin('Sesi berakhir. Silakan masuk kembali.');
    }

    if (!response.ok) {
        const error = new Error(body.message || 'Request gagal.');
        error.status = response.status;
        error.body = body;
        throw error;
    }

    return body;
}

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

function showLogin(message = '') {
    els.shellScreen.hidden = true;
    els.loginScreen.hidden = false;
    els.loginSubmit.disabled = false;

    if (message) {
        showLoginError(message);
    }
}

function showShell() {
    els.loginScreen.hidden = true;
    els.shellScreen.hidden = false;
    document.body.classList.toggle('menu-open', state.menuOpen);
}

function showLoginError(message) {
    els.loginError.textContent = message;
    els.loginError.hidden = false;
}

function clearLoginError() {
    els.loginError.textContent = '';
    els.loginError.hidden = true;
}

function setStatus(type, message) {
    if (!message) {
        els.statusRegion.innerHTML = '';
        return;
    }

    els.statusRegion.innerHTML = `<div class="status-message ${type}">${escapeHtml(message)}</div>`;
}

function setContent(html) {
    els.contentRegion.innerHTML = html;
}

function loadingCard(message = 'Memuat data...') {
    setContent(`<div class="state-card loading">${escapeHtml(message)}</div>`);
}

function emptyCard(message = 'Belum ada data.') {
    return `<div class="state-card empty">${escapeHtml(message)}</div>`;
}

function errorCard(message = 'Data gagal dimuat.') {
    return `<div class="state-card error">${escapeHtml(message)}</div>`;
}

function renderShell() {
    const user = state.user;
    const roleLabel = roleLabels[user.role] ?? user.role;

    els.sidebarRole.textContent = roleLabel;
    els.userName.textContent = user.name;
    els.userEmail.textContent = user.email;

    renderMenu();
    showShell();
    renderView(state.activeView);
}

function renderMenu() {
    const menu = roleMenus[state.user.role] ?? roleMenus.ketua_lingkungan_stasi;

    els.mainMenu.innerHTML = menu.map((item) => {
        const activeClass = item.id === state.activeView ? 'active' : '';
        return `<button class="${activeClass}" type="button" data-view="${item.id}">${escapeHtml(item.label)}</button>`;
    }).join('');

    els.mainMenu.querySelectorAll('button').forEach((button) => {
        button.addEventListener('click', () => {
            state.activeView = button.dataset.view;
            state.menuOpen = false;
            document.body.classList.remove('menu-open');
            renderMenu();
            renderView(state.activeView);
        });
    });
}

function renderView(view) {
    setStatus('', '');
    const titles = {
        dashboard: 'Ringkasan',
        'admin-master': 'Master Data',
        candidates: 'Calon Penerima',
        documents: 'Dokumen',
        'my-candidates': 'Calon Saya',
        'candidate-form': 'Input Calon',
        offline: 'Offline',
        'stasi-recap': 'Rekap Stasi',
        saw: 'Proses SAW',
        ranking: 'Ranking',
    };

    els.pageTitle.textContent = titles[view] ?? 'Ringkasan';

    if (view === 'dashboard') {
        renderDashboard();
    } else if (view === 'my-candidates') {
        renderCandidateList('/lingkungan-stasi/calon-penerima', 'Belum ada calon penerima di lingkungan ini.');
    } else if (view === 'stasi-recap') {
        renderCandidateList('/stasi/calon-penerima-rekap', 'Belum ada calon penerima untuk stasi ini.');
    } else if (view === 'candidate-form') {
        renderCandidateForm();
    } else if (view === 'documents') {
        renderTemplates();
    } else if (view === 'admin-master') {
        renderAdminMaster();
    } else {
        renderPlaceholder(view);
    }
}

async function renderAdminMaster() {
    // master resource CRUD UI
    const resources = [
        { key: 'stasis', label: 'Stasi' },
        { key: 'lingkungan-stasis', label: 'Lingkungan Stasi' },
        { key: 'lingkungan-parokis', label: 'Lingkungan Paroki' },
        { key: 'bansos-periods', label: 'Periode Bansos' },
        { key: 'users', label: 'User' },
    ];

    setContent(`
        <div class="master-shell">
            <nav class="master-nav">
                ${resources.map(r => `<button class="master-nav-btn" data-resource="${r.key}">${escapeHtml(r.label)}</button>`).join('')}
            </nav>
            <div id="master-controls"></div>
            <div id="master-content"></div>
        </div>
    `);

    // attach nav handlers
    document.querySelectorAll('.master-nav-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const resource = btn.dataset.resource;
            state.master.resource = resource;
            state.master.page = 1;
            renderMasterResource(resource);
        });
    });

    // initial render
    renderMasterResource(state.master.resource);
}

const MASTER_CONFIG = {
    'stasis': {
        endpoint: 'stasis',
        title: 'Stasi',
        columns: [
            { key: 'nama_stasi', label: 'Nama' },
            { key: 'kode_stasi', label: 'Kode' },
            { key: 'alamat', label: 'Alamat' },
        ],
        fields: [
            { name: 'nama_stasi', label: 'Nama', type: 'text', required: true },
            { name: 'kode_stasi', label: 'Kode', type: 'text', required: true },
            { name: 'alamat', label: 'Alamat', type: 'textarea', required: false },
        ],
    },
    'lingkungan-stasis': {
        endpoint: 'lingkungan-stasis',
        title: 'Lingkungan Stasi',
        columns: [
            { key: 'nama_lingkungan_stasi', label: 'Nama' },
            { key: 'kode_lingkungan', label: 'Kode' },
            { key: 'stasi_id', label: 'Stasi' },
        ],
        fields: [
            { name: 'stasi_id', label: 'Stasi', type: 'select', optionsEndpoint: '/master/stasis', optionLabel: 'nama_stasi', required: true },
            { name: 'nama_lingkungan_stasi', label: 'Nama', type: 'text', required: true },
            { name: 'kode_lingkungan', label: 'Kode', type: 'text', required: true },
        ],
    },
    'lingkungan-parokis': {
        endpoint: 'lingkungan-parokis',
        title: 'Lingkungan Paroki',
        columns: [
            { key: 'nama_lingkungan_paroki', label: 'Nama' },
            { key: 'kode_wilayah', label: 'Kode' },
        ],
        fields: [
            { name: 'nama_lingkungan_paroki', label: 'Nama', type: 'text', required: true },
            { name: 'kode_wilayah', label: 'Kode', type: 'text', required: true },
        ],
    },
    'bansos-periods': {
        endpoint: 'bansos-periods',
        title: 'Periode Bansos',
        columns: [
            { key: 'nama_periode', label: 'Nama Periode' },
            { key: 'tahun', label: 'Tahun' },
            { key: 'status_periode', label: 'Status' },
        ],
        fields: [
            { name: 'nama_periode', label: 'Nama Periode', type: 'text', required: true },
            { name: 'tahun', label: 'Tahun', type: 'number', required: true },
            { name: 'status_periode', label: 'Status', type: 'select', options: [
                { value: 'aktif', label: 'aktif' },
                { value: 'proses_perankingan', label: 'proses_perankingan' },
                { value: 'selesai', label: 'selesai' },
                { value: 'arsip', label: 'arsip' },
            ], required: true },
        ],
    },
    'users': {
        endpoint: 'users',
        title: 'User',
        columns: [
            { key: 'name', label: 'Nama' },
            { key: 'email', label: 'Email' },
            { key: 'role', label: 'Role' },
        ],
        fields: [
            { name: 'name', label: 'Nama', type: 'text', required: true },
            { name: 'email', label: 'Email', type: 'email', required: true },
            { name: 'password', label: 'Password', type: 'password', required: false },
            { name: 'role', label: 'Role', type: 'select', options: [
                { value: 'super_admin', label: 'Super Admin' },
                { value: 'paroki', label: 'Paroki' },
                { value: 'ketua_lingkungan_paroki', label: 'Ketua Lingkungan Paroki' },
                { value: 'stasi', label: 'Stasi' },
                { value: 'ketua_lingkungan_stasi', label: 'Ketua Lingkungan Stasi' },
            ], required: true },
            { name: 'stasi_id', label: 'Stasi', type: 'select', optionsEndpoint: '/master/stasis', optionLabel: 'nama_stasi', required: false },
            { name: 'lingkungan_paroki_id', label: 'Lingkungan Paroki', type: 'select', optionsEndpoint: '/master/lingkungan-parokis', optionLabel: 'nama_lingkungan_paroki', required: false },
            { name: 'lingkungan_stasi_id', label: 'Lingkungan Stasi', type: 'select', optionsEndpoint: '/master/lingkungan-stasis', optionLabel: 'nama_lingkungan_stasi', required: false },
        ],
    },
};

async function renderMasterResource(resource) {
    const cfg = MASTER_CONFIG[resource];
    if (!cfg) return setContent(errorCard('Resource tidak ditemukan.'));

    document.getElementById('master-controls').innerHTML = `
        <div class="master-controls-bar">
            <div>
                <input id="master-search" placeholder="Cari..." value="${escapeHtml(state.master.q)}">
                <select id="master-perpage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <button id="master-search-btn" class="ghost-button">Cari</button>
            </div>
            <div>
                <button id="master-create-btn" class="primary-button">Buat ${escapeHtml(cfg.title)}</button>
            </div>
        </div>
    `;

    document.getElementById('master-perpage').value = String(state.master.per_page);
    document.getElementById('master-search-btn').addEventListener('click', () => {
        const q = document.getElementById('master-search').value.trim();
        state.master.q = q;
        state.master.page = 1;
        renderMasterList(resource);
    });

    document.getElementById('master-perpage').addEventListener('change', (e) => {
        state.master.per_page = Number(e.target.value);
        state.master.page = 1;
        renderMasterList(resource);
    });

    document.getElementById('master-create-btn').addEventListener('click', () => renderMasterForm(resource, null));

    // initial list
    renderMasterList(resource);
}

async function fetchMasterPage(resource) {
    const cfg = MASTER_CONFIG[resource];
    const per_page = state.master.per_page || 10;
    const page = state.master.page || 1;
    const q = state.master.q || '';

    let path = `/master/${cfg.endpoint}?per_page=${per_page}&page=${page}`;
    if (q) path += `&q=${encodeURIComponent(q)}`;

    const res = await api(path);
    return res.data ?? {};
}

function renderTableRowFor(cfg, item) {
    const cells = cfg.columns.map(col => {
        if (col.key.endsWith('_id')) {
            const val = item[col.key];
            return `<td>${escapeHtml(val ?? '-')}</td>`;
        }
        return `<td>${escapeHtml(item[col.key] ?? '-')}</td>`;
    }).join('');

    return `
        <tr>
            ${cells}
            <td>
                <button class="ghost-button edit-btn" data-id="${item.id}">Edit</button>
                <button class="ghost-button danger delete-btn" data-id="${item.id}">Hapus</button>
            </td>
        </tr>
    `;
}

async function renderMasterList(resource) {
    const cfg = MASTER_CONFIG[resource];
    const container = document.getElementById('master-content');
    container.innerHTML = `<div class="state-card loading">Memuat ${escapeHtml(cfg.title)}...</div>`;

    try {
        const pageData = await fetchMasterPage(resource);
        const items = Array.isArray(pageData.data) ? pageData.data : (Array.isArray(pageData) ? pageData : []);

        if (!items.length) {
            container.innerHTML = `<div class="state-card empty">Belum ada ${escapeHtml(cfg.title)}.</div>`;
            return;
        }

        const table = `
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            ${cfg.columns.map(c => `<th>${escapeHtml(c.label)}</th>`).join('')}
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(i => renderTableRowFor(cfg, i)).join('')}
                    </tbody>
                </table>
            </div>
        `;

        const pagination = `
            <div class="pagination-bar">
                <button id="master-prev" class="ghost-button">Prev</button>
                <span>Halaman ${pageData.current_page} / ${pageData.last_page}</span>
                <button id="master-next" class="ghost-button">Next</button>
            </div>
        `;

        container.innerHTML = table + pagination;

        document.getElementById('master-prev').addEventListener('click', () => {
            if (pageData.current_page > 1) {
                state.master.page = pageData.current_page - 1;
                renderMasterList(resource);
            }
        });

        document.getElementById('master-next').addEventListener('click', () => {
            if (pageData.current_page < pageData.last_page) {
                state.master.page = pageData.current_page + 1;
                renderMasterList(resource);
            }
        });

        // attach edit/delete handlers
        container.querySelectorAll('.edit-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                // fetch single item
                const resp = await api(`/master/${cfg.endpoint}/${id}`);
                const item = resp.data ?? null;
                renderMasterForm(resource, item);
            });
        });

        container.querySelectorAll('.delete-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                if (!confirm('Hapus item ini?')) return;
                try {
                    await api(`/master/${cfg.endpoint}/${id}`, { method: 'DELETE' });
                    setStatus('success', 'Data berhasil dihapus.');
                    renderMasterList(resource);
                } catch (err) {
                    setStatus('error', formatApiError(err));
                }
            });
        });

    } catch (err) {
        container.innerHTML = errorCard(err.message);
    }
}

async function loadSelectOptions(endpoint) {
    try {
        const res = await api(endpoint + '?per_page=1000');
        const page = res.data ?? {};
        const items = Array.isArray(page.data) ? page.data : (Array.isArray(page) ? page : []);
        return items;
    } catch (e) {
        return [];
    }
}

async function renderMasterForm(resource, item = null) {
    const cfg = MASTER_CONFIG[resource];
    const container = document.getElementById('master-content');

    const isEdit = !!(item && item.id);
    const formId = 'master-form';

    // build form fields
    const fieldsHtml = await Promise.all(cfg.fields.map(async (f) => {
        const value = item ? (item[f.name] ?? '') : '';
        if (f.type === 'select') {
            let optionsHtml = '';
            if (f.options) {
                optionsHtml = f.options.map(o => `<option value="${escapeHtml(o.value)}" ${String(value) === String(o.value) ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('');
            } else if (f.optionsEndpoint) {
                const opts = await loadSelectOptions(f.optionsEndpoint.replace('/master', '/master')); // keep path
                optionsHtml = opts.map(o => `<option value="${o.id}" ${String(value) === String(o.id) ? 'selected' : ''}>${escapeHtml(o[f.optionLabel] ?? o.name ?? o.id)}</option>`).join('');
            }

            return `<label>${escapeHtml(f.label)}<select name="${escapeHtml(f.name)}">${optionsHtml}</select></label>`;
        }

        if (f.type === 'textarea') {
            return `<label>${escapeHtml(f.label)}<textarea name="${escapeHtml(f.name)}">${escapeHtml(value)}</textarea></label>`;
        }

        return `<label>${escapeHtml(f.label)}<input name="${escapeHtml(f.name)}" type="${escapeHtml(f.type)}" value="${escapeHtml(value)}"></label>`;
    }));

    container.innerHTML = `
        <form id="${formId}" class="data-form">
            <h3>${isEdit ? 'Ubah' : 'Buat'} ${escapeHtml(cfg.title)}</h3>
            <div class="form-grid">
                ${fieldsHtml.join('')}
            </div>
            <div class="form-actions">
                <button type="submit" class="primary-button">Simpan</button>
                <button type="button" id="master-cancel" class="ghost-button">Batal</button>
            </div>
        </form>
    `;

    document.getElementById('master-cancel').addEventListener('click', () => renderMasterList(resource));

    document.getElementById(formId).addEventListener('submit', async (e) => {
        e.preventDefault();
        setStatus('loading', 'Menyimpan...');

        const data = Object.fromEntries(new FormData(e.target).entries());

        // normalize numeric fields
        if (data.tahun) data.tahun = Number(data.tahun);

        try {
            if (isEdit) {
                await api(`/master/${cfg.endpoint}/${item.id}`, { method: 'PUT', body: JSON.stringify(data) });
                setStatus('success', `${cfg.title} berhasil diperbarui.`);
            } else {
                await api(`/master/${cfg.endpoint}`, { method: 'POST', body: JSON.stringify(data) });
                setStatus('success', `${cfg.title} berhasil dibuat.`);
            }

            renderMasterList(resource);
        } catch (err) {
            setStatus('error', formatApiError(err));
        }
    });
}

function renderDashboard() {
    const user = state.user;
    const roleLabel = roleLabels[user.role] ?? user.role;
    const relationRows = [
        ['Role', roleLabel],
        ['Stasi ID', user.stasi_id ?? '-'],
        ['Lingkungan Stasi ID', user.lingkungan_stasi_id ?? '-'],
        ['Lingkungan Paroki ID', user.lingkungan_paroki_id ?? '-'],
    ];

    setContent(`
        <div class="dashboard-grid">
            <section class="summary-panel">
                <p class="eyebrow">Akun aktif</p>
                <h3>${escapeHtml(user.name)}</h3>
                <p>${escapeHtml(user.email)}</p>
                <dl class="detail-list">
                    ${relationRows.map(([label, value]) => `
                        <div>
                            <dt>${escapeHtml(label)}</dt>
                            <dd>${escapeHtml(String(value))}</dd>
                        </div>
                    `).join('')}
                </dl>
            </section>
            ${roleDashboard(user.role)}
        </div>
    `);
}

function roleDashboard(role) {
    const cards = {
        super_admin: [
            ['Master data', 'Kelola struktur stasi, lingkungan, periode, dan user pada fase berikutnya.'],
            ['Monitoring', 'Pantau seluruh calon penerima lintas stasi dan paroki.'],
            ['Kontrol akses', 'Validasi role dan tenant sudah aktif di backend.'],
        ],
        ketua_lingkungan_stasi: [
            ['Pendataan', 'Input dan pantau calon penerima dari lingkungan stasi.'],
            ['Pengajuan', 'Kirim data draft ke stasi setelah siap diverifikasi.'],
            ['Offline', 'PWA tetap tersedia melalui halaman offline yang sudah ada.'],
        ],
        stasi: [
            ['Rekap', 'Tinjau calon penerima dari stasi yang terhubung ke akun ini.'],
            ['Validasi', 'Approve data yang siap diteruskan ke proses ranking.'],
            ['Surat', 'Template permohonan disiapkan pada fase dokumen.'],
        ],
        ketua_lingkungan_paroki: [
            ['SAW', 'Jalankan perankingan calon penerima per periode.'],
            ['Ranking', 'Kirim hasil ranking ke paroki setelah siap.'],
            ['Audit', 'Log aktivitas backend sudah tersedia sebagai fondasi.'],
        ],
        paroki: [
            ['Keputusan', 'Tetapkan penerima sah dan nominal bantuan.'],
            ['Ranking', 'Pantau hasil perankingan dari proses SAW.'],
            ['Surat', 'Kelola template dan arsip surat paroki.'],
        ],
    };

    return `
        <section class="quick-grid">
            ${(cards[role] ?? cards.ketua_lingkungan_stasi).map(([title, text]) => `
                <article class="info-card">
                    <h4>${escapeHtml(title)}</h4>
                    <p>${escapeHtml(text)}</p>
                </article>
            `).join('')}
        </section>
    `;
}

async function renderCandidateList(path, emptyMessage) {
    loadingCard();

    try {
        const response = await api(path);
        const rows = response.data ?? [];

        if (!rows.length) {
            setContent(emptyCard(emptyMessage));
            return;
        }

        setContent(`
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Status</th>
                            <th>Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map((item) => `
                            <tr>
                                <td>${escapeHtml(item.nama_lengkap ?? '-')}</td>
                                <td>${escapeHtml(item.nik ?? '-')}</td>
                                <td><span class="status-pill">${escapeHtml(item.status_alur ?? '-')}</span></td>
                                <td>${escapeHtml(String(item.saw_score ?? '0.0000'))}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `);
    } catch (error) {
        setContent(errorCard(error.message));
    }
}

function renderCandidateForm() {
    setContent(`
        <form id="candidate-form" class="data-form">
            <div class="form-grid">
                <label>Periode ID<input name="bansos_period_id" type="number" min="1" value="1" required></label>
                <label>NIK<input name="nik" maxlength="16" required></label>
                <label>Nama Lengkap<input name="nama_lengkap" required></label>
                <label>Alamat<input name="alamat_kristen"></label>
                <label>Pendapatan Keluarga<input name="pendapatan_keluarga" type="number" min="0" required></label>
                <label>Jumlah Tanggungan<input name="jumlah_tanggungan" type="number" min="0" value="0" required></label>
                <label>Status Tempat Tinggal
                    <select name="status_tempat_tinggal" required>
                        <option value="milik_sendiri">Milik Sendiri</option>
                        <option value="sewa">Sewa</option>
                        <option value="numpang">Numpang</option>
                    </select>
                </label>
                <label>Status Hubungan
                    <select name="status_hubungan" required>
                        <option value="lajang">Lajang</option>
                        <option value="menikah">Menikah</option>
                        <option value="cerai">Cerai</option>
                    </select>
                </label>
            </div>
            <label>Urgensi Tambahan<textarea name="urgensi_tambahan_tekstual" rows="4"></textarea></label>
            <button class="primary-button" type="submit">Simpan Draft</button>
        </form>
    `);

    document.getElementById('candidate-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        setStatus('loading', 'Menyimpan calon penerima...');

        const payload = Object.fromEntries(new FormData(event.target).entries());
        payload.bansos_period_id = Number(payload.bansos_period_id);
        payload.pendapatan_keluarga = Number(payload.pendapatan_keluarga);
        payload.jumlah_tanggungan = Number(payload.jumlah_tanggungan);

        try {
            await api('/lingkungan-stasi/calon-penerima', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            event.target.reset();
            setStatus('success', 'Calon penerima berhasil disimpan sebagai draft.');
        } catch (error) {
            setStatus('error', formatApiError(error));
        }
    });
}

async function renderTemplates() {
    loadingCard();

    try {
        const response = await api('/paroki/templates');
        const templates = response.data ?? [];

        if (!templates.length) {
            setContent(emptyCard('Belum ada template dokumen.'));
            return;
        }

        setContent(`
            <section class="quick-grid">
                ${templates.map((template) => `
                    <article class="info-card">
                        <h4>${escapeHtml(template.name)}</h4>
                        <p>${escapeHtml(template.slug)}</p>
                        <span class="status-pill">${escapeHtml(template.type ?? 'template')}</span>
                    </article>
                `).join('')}
            </section>
        `);
    } catch (error) {
        setContent(errorCard(error.message));
    }
}

function renderPlaceholder(view) {
    const messages = {
        'admin-master': 'Master data akan dibangun pada Fase 3.',
        candidates: 'Monitoring lintas calon penerima akan mengikuti workflow lengkap pada Fase 4.',
        offline: 'Halaman PWA/offline tersedia di /pwa dan akan disatukan penuh pada Fase 8.',
        saw: 'Proses SAW akan dimatangkan pada Fase 5.',
        ranking: 'Ranking dan keputusan final paroki akan dimatangkan pada Fase 6.',
    };

    setContent(`
        <div class="state-card empty">
            ${escapeHtml(messages[view] ?? 'Modul ini sedang disiapkan.')}
        </div>
    `);
}

async function login(email, password) {
    clearLoginError();
    els.loginSubmit.disabled = true;

    try {
        const response = await api('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });
        setSession(response.data.token, response.data.user);
        state.activeView = 'dashboard';
        renderShell();
    } catch (error) {
        showLoginError(formatApiError(error));
    } finally {
        els.loginSubmit.disabled = false;
    }
}

async function bootstrapSession() {
    if (!state.token) {
        showLogin();
        return;
    }

    try {
        const response = await api('/auth/me');
        state.user = response.data;
        renderShell();
    } catch {
        clearSession();
        showLogin();
    }
}

function formatApiError(error) {
    const errors = error.body?.errors;
    if (!errors) {
        return error.message;
    }

    const firstKey = Object.keys(errors)[0];
    const firstValue = errors[firstKey];

    return Array.isArray(firstValue) ? firstValue[0] : error.message;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

els.loginForm.addEventListener('submit', (event) => {
    event.preventDefault();
    login(els.loginEmail.value.trim(), els.loginPassword.value);
});

els.demoUsers.forEach((button) => {
    button.addEventListener('click', () => {
        els.loginEmail.value = button.dataset.email;
        els.loginPassword.value = 'password';
        clearLoginError();
    });
});

els.logoutButton.addEventListener('click', async () => {
    try {
        await api('/auth/logout', { method: 'POST' });
    } catch {
        // Session is cleared locally even when the server token is already invalid.
    }

    clearSession();
    showLogin();
});

els.menuToggle.addEventListener('click', () => {
    state.menuOpen = !state.menuOpen;
    document.body.classList.toggle('menu-open', state.menuOpen);
});

bootstrapSession();
