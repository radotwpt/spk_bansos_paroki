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
        { id: 'activity-log', label: '📋 Log Aktivitas' },
        { id: 'documents', label: 'Dokumen' },
    ],
    ketua_lingkungan_stasi: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'my-candidates', label: 'Calon Saya' },
        { id: 'candidate-form', label: '➕ Input Calon' },
        { id: 'activity-log', label: '📋 Log Aktivitas' },
    ],
    stasi: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'stasi-recap', label: 'Rekap Stasi' },
        { id: 'activity-log', label: '📋 Log Aktivitas' },
    ],
    ketua_lingkungan_paroki: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'saw', label: '⚙️ Proses SAW' },
        { id: 'activity-log', label: '📋 Log Aktivitas' },
    ],
    paroki: [
        { id: 'dashboard', label: 'Ringkasan' },
        { id: 'ranking', label: '🏆 Ranking' },
        { id: 'documents', label: '📋 Dokumen' },
        { id: 'activity-log', label: '📋 Log Aktivitas' },
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
    currentCandidates: [],
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
// Check period lock state and update UI indicator
async function updatePeriodLockDisplay(periodId) {
    const indicatorId = 'period-lock-indicator';
    const existing = document.getElementById(indicatorId);
    try {
        const res = await api(`/lingkungan-paroki/saw/weights/${periodId}`);
        const period = res.period ?? null;
        const isLocked = !!(period && period.is_locked);
        if (!document.getElementById('saw-period-id')) return;
        if (!existing) {
            const el = document.createElement('span');
            el.id = indicatorId;
            el.className = 'lock-indicator';
            document.getElementById('saw-period-id').parentNode.appendChild(el);
        }
        const el = document.getElementById(indicatorId);
        el.textContent = isLocked ? `🔒 Terkunci oleh ${period.locked_by ?? 'sistem'}` : '🔓 Buka';
        el.dataset.locked = isLocked ? '1' : '0';
        // disable weights button when locked
        const weightsBtn = document.getElementById('saw-weights');
        if (weightsBtn) weightsBtn.disabled = isLocked;
        const sendBtn = document.getElementById('saw-send');
        if (sendBtn) sendBtn.disabled = isLocked;
    } catch (err) {
        // ignore, indicator optional
    }
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

// UI helpers: toasts and modals
function ensureUiContainers() {
    if (!document.getElementById('ui-toast-container')) {
        const c = document.createElement('div');
        c.id = 'ui-toast-container';
        c.className = 'ui-toast-container';
        document.body.appendChild(c);
    }

    if (!document.getElementById('ui-modal-root')) {
        const m = document.createElement('div');
        m.id = 'ui-modal-root';
        m.className = 'ui-modal-root';
        document.body.appendChild(m);
    }
}

function showToast(type, message, timeout = 4000) {
    ensureUiContainers();
    const container = document.getElementById('ui-toast-container');
    const el = document.createElement('div');
    el.className = `ui-toast ${type}`;
    el.textContent = message;
    container.appendChild(el);
    setTimeout(() => {
        el.classList.add('dismiss');
        setTimeout(() => el.remove(), 300);
    }, timeout);
}

function showConfirm(message, title = 'Konfirmasi') {
    ensureUiContainers();
    const root = document.getElementById('ui-modal-root');
    return new Promise((resolve) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'modal-backdrop';
        wrapper.innerHTML = `
            <div class="modal-card">
                <h3>${escapeHtml(title)}</h3>
                <div class="modal-body">${escapeHtml(message)}</div>
                <div class="modal-actions">
                    <button class="ghost-button cancel">Batal</button>
                    <button class="primary-button confirm">OK</button>
                </div>
            </div>
        `;

        root.appendChild(wrapper);

        wrapper.querySelector('.cancel').addEventListener('click', () => {
            wrapper.remove();
            resolve(false);
        });

        wrapper.querySelector('.confirm').addEventListener('click', () => {
            wrapper.remove();
            resolve(true);
        });
    });
}

function showPrompt(label, defaultValue = '', title = 'Input') {
    ensureUiContainers();
    const root = document.getElementById('ui-modal-root');
    return new Promise((resolve) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'modal-backdrop';
        wrapper.innerHTML = `
            <div class="modal-card">
                <h3>${escapeHtml(title)}</h3>
                <div class="modal-body">
                    <label>${escapeHtml(label)}<input class="modal-input" value="${escapeHtml(defaultValue)}"></label>
                </div>
                <div class="modal-actions">
                    <button class="ghost-button cancel">Batal</button>
                    <button class="primary-button confirm">Kirim</button>
                </div>
            </div>
        `;

        root.appendChild(wrapper);

        const input = wrapper.querySelector('.modal-input');
        input.focus();

        wrapper.querySelector('.cancel').addEventListener('click', () => {
            wrapper.remove();
            resolve(null);
        });

        wrapper.querySelector('.confirm').addEventListener('click', () => {
            const v = input.value.trim();
            wrapper.remove();
            resolve(v);
        });
    });
}

function setButtonLoading(btn, loadingText = 'Memproses...') {
    if (!btn) return () => {};
    const prev = { disabled: btn.disabled, text: btn.innerHTML };
    btn.disabled = true;
    btn.innerHTML = `${escapeHtml(loadingText)}`;
    return () => {
        btn.disabled = prev.disabled;
        btn.innerHTML = prev.text;
    };
}

// showFormModal: build a modal form from MASTER_CONFIG-like fields
async function showFormModal(cfg, item = null, title = null) {
    ensureUiContainers();
    const root = document.getElementById('ui-modal-root');
    const wrapper = document.createElement('div');
    wrapper.className = 'modal-backdrop';

    title = title ?? (item ? `Ubah ${cfg.title}` : `Buat ${cfg.title}`);

    // prepare field HTML, loading select options when needed
    const fieldsHtmlParts = await Promise.all(cfg.fields.map(async (f) => {
        const value = item ? (item[f.name] ?? '') : '';
        if (f.type === 'select') {
            let optionsHtml = '';
            if (f.options) {
                optionsHtml = f.options.map(o => `<option value="${escapeHtml(o.value)}" ${String(value) === String(o.value) ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('');
            } else if (f.optionsEndpoint) {
                const opts = await loadSelectOptions(f.optionsEndpoint);
                optionsHtml = opts.map(o => `<option value="${o.id}" ${String(value) === String(o.id) ? 'selected' : ''}>${escapeHtml(o[f.optionLabel] ?? o.name ?? o.id)}</option>`).join('');
            }
            const fieldHtml = `<label data-field-name="${escapeHtml(f.name)}" class="form-field-label">${escapeHtml(f.label)}<select name="${escapeHtml(f.name)}" ${f.required ? 'required' : ''}>${optionsHtml}</select></label>`;
            return { html: fieldHtml, field: f };
        }

        if (f.type === 'textarea') {
            const fieldHtml = `<label data-field-name="${escapeHtml(f.name)}" class="form-field-label">${escapeHtml(f.label)}<textarea name="${escapeHtml(f.name)}" ${f.required ? 'required' : ''}>${escapeHtml(value)}</textarea></label>`;
            return { html: fieldHtml, field: f };
        }

        const fieldHtml = `<label data-field-name="${escapeHtml(f.name)}" class="form-field-label">${escapeHtml(f.label)}<input name="${escapeHtml(f.name)}" type="${escapeHtml(f.type)}" value="${escapeHtml(value)}" ${f.required ? 'required' : ''}>${f.note ? `<small>${escapeHtml(f.note)}</small>` : ''}</label>`;
        return { html: fieldHtml, field: f };
    }));

    const fieldParts = fieldsHtmlParts.filter(fp => fp !== null);

    wrapper.innerHTML = `
        <div class="modal-card modal-form-card">
            <h3>${escapeHtml(title)}</h3>
            <form id="modal-form" class="data-form">
                <div class="form-grid">${fieldParts.map(fp => fp.html).join('')}</div>
                <div class="modal-actions">
                    <button type="button" class="ghost-button cancel">Batal</button>
                    <button type="submit" class="primary-button submit">Simpan</button>
                </div>
            </form>
        </div>
    `;

    root.appendChild(wrapper);

    return await new Promise((resolve) => {
        const form = wrapper.querySelector('#modal-form');
        let formData = {};

        // update conditional visibility on change
        const updateConditionalFields = () => {
            formData = Object.fromEntries(new FormData(form).entries());
            fieldParts.forEach(fp => {
                const label = wrapper.querySelector(`label[data-field-name="${fp.field.name}"]`);
                if (label) {
                    if (fp.field.showWhen) {
                        label.style.display = fp.field.showWhen(formData) ? '' : 'none';
                    }
                }
            });
        };

        // attach change listener to role field for conditional visibility
        const roleField = form.querySelector('select[name="role"]');
        if (roleField) {
            roleField.addEventListener('change', updateConditionalFields);
        }

        // initial visibility update
        updateConditionalFields();

        wrapper.querySelector('.cancel').addEventListener('click', () => {
            wrapper.remove();
            resolve(null);
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(form).entries());
            // normalize numeric fields commonly used
            if (data.tahun) data.tahun = Number(data.tahun);
            wrapper.remove();
            resolve(data);
        });
    });
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
        'candidate-form': 'Input Calon Penerima',
        'activity-log': 'Log Aktivitas',
        'stasi-recap': 'Rekap Stasi',
        saw: 'Proses SAW & Perankingan',
        ranking: 'Ranking & Keputusan',
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
    } else if (view === 'activity-log') {
        renderActivityLog();
    } else if (view === 'saw') {
        renderSaw();
    } else if (view === 'ranking') {
        renderRanking();
    } else {
        renderPlaceholder(view);
    }
}

async function renderSaw() {
    setContent(`
        <section>
            <h3>Proses SAW</h3>
            <div class="form-inline">
                <label>Periode ID<input id="saw-period-id" type="number" min="1" value="1"></label>
                <button id="saw-run" class="primary-button">Jalankan SAW</button>
                <button id="saw-preview" class="ghost-button">Preview</button>
                <button id="saw-weights" class="ghost-button">Atur Bobot</button>
                <button id="saw-results" class="ghost-button">Lihat Hasil (Audit)</button>
                <button id="saw-send" class="ghost-button">Kirim ke Paroki</button>
            </div>
            <div id="saw-result" class="mt-4"></div>
        </section>
    `);
    // update lock indicator whenever period changes
    const periodInput = document.getElementById('saw-period-id');
    periodInput.addEventListener('input', () => updatePeriodLockDisplay(Number(periodInput.value || 0)));
    updatePeriodLockDisplay(Number(periodInput.value || 0));

    document.getElementById('saw-run').addEventListener('click', async (e) => {
        const periodId = Number(document.getElementById('saw-period-id').value || 0);
        if (!periodId) return setStatus('error', 'Periode tidak valid.');
        const btn = e.target;
        const restore = setButtonLoading(btn, 'Menjalankan...');
        try {
            const res = await api(`/lingkungan-paroki/proses-saw/${periodId}`, { method: 'POST' });
            const rows = res.data ?? [];
            const html = `
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Rank</th><th>ID</th><th>Score</th></tr></thead>
                        <tbody>
                            ${rows.map((r, i) => `<tr><td>${i+1}</td><td>${r.id}</td><td>${r.score}</td></tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('saw-result').innerHTML = html;
            showToast('success', 'Perankingan SAW selesai.');
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });

    document.getElementById('saw-preview').addEventListener('click', async () => {
        const periodId = Number(document.getElementById('saw-period-id').value || 0);
        if (!periodId) return setStatus('error', 'Periode tidak valid.');
        const btn = document.getElementById('saw-preview');
        const restore = setButtonLoading(btn, 'Mempersiapkan preview...');
        try {
            const res = await api(`/lingkungan-paroki/saw/preview/${periodId}`);
            const rows = res.preview ?? [];
            const html = `
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Rank</th><th>ID</th><th>Score</th></tr></thead>
                        <tbody>
                            ${rows.map((r, i) => `<tr><td>${i+1}</td><td>${r.id}</td><td>${r.score}</td></tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('saw-result').innerHTML = html;
            showToast('info', 'Preview perankingan siap. Preview tidak menyimpan hasil.');
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });

    document.getElementById('saw-weights').addEventListener('click', async () => {
        const periodId = Number(document.getElementById('saw-period-id').value || 0) || null;
        try {
            const res = await api(`/lingkungan-paroki/saw/weights/${periodId ?? ''}`);
            const criteria = res.criteria ?? [];
            // build modal form
            const formHtml = `
                <form id="saw-weights-form">
                    <div class="form-grid">
                        ${criteria.map(c => `
                            <label>${escapeHtml(c.label)}<input name="${escapeHtml(c.key)}" type="number" step="0.0001" min="0" max="1" value="${escapeHtml(String(c.weight))}"></label>
                        `).join('')}
                    </div>
                    <div class="modal-actions"><button type="button" class="ghost-button cancel">Batal</button><button type="submit" class="primary-button">Simpan</button></div>
                </form>
            `;
            ensureUiContainers();
            const root = document.getElementById('ui-modal-root');
            const wrapper = document.createElement('div');
            wrapper.className = 'modal-backdrop';
            wrapper.innerHTML = `<div class="modal-card"><h3>Atur Bobot (Periode: ${escapeHtml(res.period?.nama_periode ?? 'global')})</h3>${formHtml}</div>`;
            root.appendChild(wrapper);

            wrapper.querySelector('.cancel').addEventListener('click', () => wrapper.remove());
            const submitBtn = wrapper.querySelector('button[type=submit]');
            if (res.period && res.period.is_locked) {
                if (submitBtn) submitBtn.disabled = true;
                const warn = document.createElement('div'); warn.className = 'state-card warning'; warn.textContent = 'Periode terkunci — bobot tidak dapat diubah.'; wrapper.querySelector('.modal-card').insertAdjacentElement('afterbegin', warn);
            }

            wrapper.querySelector('#saw-weights-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target).entries());
                // sum check
                const weights = {};
                Object.keys(data).forEach(k => weights[k] = Number(data[k]));
                const total = Object.values(weights).reduce((a,b) => a + b, 0);
                if (Math.abs(total - 1.0) > 0.0001) return alert('Total bobot harus berjumlah 1.0');
                try {
                    await api(`/lingkungan-paroki/saw/weights/${periodId ?? ''}`, { method: 'POST', body: JSON.stringify({ weights }) });
                    showToast('success', 'Bobot disimpan');
                    wrapper.remove();
                } catch (err) {
                    showToast('error', formatApiError(err));
                }
            });

        } catch (err) {
            showToast('error', formatApiError(err));
        }
    });

    document.getElementById('saw-results').addEventListener('click', async () => {
        const periodId = Number(document.getElementById('saw-period-id').value || 0);
        if (!periodId) return setStatus('error', 'Periode tidak valid.');
        try {
            const res = await api(`/lingkungan-paroki/saw/results/${periodId}`);
            const rows = res.data ?? [];
            const html = `
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Rank</th><th>Nama</th><th>Score</th><th>Weights</th><th>By</th><th>Time</th></tr></thead>
                        <tbody>
                            ${rows.map(r => {
                                const by = (r.createdBy && r.createdBy.name) || (r.created_by && r.created_by.name) || 'System';
                                const weights = r.weights_used ?? r.weightsUsed ?? {};
                                return `<tr>
                                    <td>${escapeHtml(String(r.rank ?? '-'))}</td>
                                    <td>${escapeHtml(r.calon?.nama_lengkap ?? '-')}</td>
                                    <td>${escapeHtml(String(r.score ?? '0.0000'))}</td>
                                    <td>${escapeHtml(JSON.stringify(weights))}</td>
                                    <td>${escapeHtml(by)}</td>
                                    <td>${escapeHtml(r.created_at ?? '')}</td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('saw-result').innerHTML = html;
        } catch (err) {
            showToast('error', formatApiError(err));
        }
    });

    document.getElementById('saw-send').addEventListener('click', async () => {
        const periodId = Number(document.getElementById('saw-period-id').value || 0);
        if (!periodId) return setStatus('error', 'Periode tidak valid.');
        const ok = await showConfirm('Kirim hasil ranking ke Paroki?');
        if (!ok) return;
        try {
            await api(`/lingkungan-paroki/kirim-ke-paroki/${periodId}`, { method: 'POST' });
            showToast('success', 'Ranking berhasil dikirim ke paroki.');
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        }
    });
}

async function renderRanking() {
    setContent(`
        <section>
            <h3>Ranking Paroki</h3>
            <div class="form-inline">
                <label>Periode ID<input id="rank-period-id" type="number" min="1" value="1"></label>
                <button id="rank-load" class="primary-button">Muat Ranking</button>
            </div>
            <div id="rank-result" class="mt-4"></div>
        </section>
    `);

    document.getElementById('rank-load').addEventListener('click', async (e) => {
        const periodId = Number(document.getElementById('rank-period-id').value || 0);
        if (!periodId) return setStatus('error', 'Periode tidak valid.');
        const btn = e.target;
        const restore = setButtonLoading(btn, 'Memuat...');
        try {
            const res = await api(`/paroki/ranking-data/${periodId}`);
            const rows = res.data ?? [];
            const html = `
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Rank</th><th>Nama</th><th>NIK</th><th>Score</th><th>Aksi</th></tr></thead>
                        <tbody>
                            ${rows.map((r) => `<tr>
                                <td>${escapeHtml(String(r.rank_global ?? '-'))}</td>
                                <td>${escapeHtml(r.nama_lengkap ?? '-')}</td>
                                <td>${escapeHtml(r.nik ?? '-')}</td>
                                <td>${escapeHtml(String(r.saw_score ?? '0.0000'))}</td>
                                <td>
                                    ${r.is_penerima_sah ? '<span class="status-pill">Penerima</span>' : `<button class="primary-button finalize-btn" data-id="${r.id}">Finalisasi</button>`}
                                </td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('rank-result').innerHTML = html;

            document.querySelectorAll('.finalize-btn').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const nominal = await showPrompt('Masukkan nominal bantuan (angka):', '0', 'Finalisasi Penerima');
                    if (!nominal) return;
                    const restoreBtn = setButtonLoading(btn, 'Menyimpan...');
                    try {
                        await api(`/paroki/penerima/${id}/keputusan`, { method: 'POST', body: JSON.stringify({ nominal: Number(nominal) }) });
                        showToast('success', 'Keputusan final disimpan.');
                        document.getElementById('rank-load').click();
                    } catch (err) {
                        showToast('error', formatApiError(err));
                    } finally {
                        restoreBtn();
                    }
                });
            });

        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });
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
            { key: 'is_locked', label: 'Terkunci' },
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
            { name: 'password', label: 'Password', type: 'password', required: false, note: 'Kosongkan jika tidak ingin mengubah password' },
            { name: 'role', label: 'Role', type: 'select', options: [
                { value: 'super_admin', label: 'Super Admin' },
                { value: 'paroki', label: 'Paroki' },
                { value: 'ketua_lingkungan_paroki', label: 'Ketua Lingkungan Paroki' },
                { value: 'stasi', label: 'Stasi' },
                { value: 'ketua_lingkungan_stasi', label: 'Ketua Lingkungan Stasi' },
            ], required: true },
            { name: 'stasi_id', label: 'Stasi', type: 'select', optionsEndpoint: '/master/stasis', optionLabel: 'nama_stasi', required: false, showWhen: (formData) => ['stasi', 'ketua_lingkungan_stasi'].includes(formData.role) },
            { name: 'lingkungan_paroki_id', label: 'Lingkungan Paroki', type: 'select', optionsEndpoint: '/master/lingkungan-parokis', optionLabel: 'nama_lingkungan_paroki', required: false, showWhen: (formData) => formData.role === 'ketua_lingkungan_paroki' },
            { name: 'lingkungan_stasi_id', label: 'Lingkungan Stasi', type: 'select', optionsEndpoint: '/master/lingkungan-stasis', optionLabel: 'nama_lingkungan_stasi', required: false, showWhen: (formData) => formData.role === 'ketua_lingkungan_stasi' },
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
        if (col.key === 'is_locked') {
            return `<td><span class="status-pill ${item[col.key] ? 'locked' : 'open'}">${escapeHtml(item[col.key] ? 'Terkunci' : 'Aktif')}</span></td>`;
        }

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

        container.querySelectorAll('.view-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                renderCandidateDetail(id);
            });
        });

        container.querySelectorAll('.delete-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const confirmed = await showConfirm('Hapus item ini?');
                if (!confirmed) return;
                try {
                    await api(`/master/${cfg.endpoint}/${id}`, { method: 'DELETE' });
                    setStatus('success', 'Data berhasil dihapus.');
                    showToast('success', 'Data berhasil dihapus.');
                    renderMasterList(resource);
                } catch (err) {
                    setStatus('error', formatApiError(err));
                    showToast('error', formatApiError(err));
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

    // show modal form and get data
    const isEdit = !!(item && item.id);
    const modalData = await showFormModal(cfg, item, `${isEdit ? 'Ubah' : 'Buat'} ${cfg.title}`);

    if (modalData === null) {
        // user cancelled
        renderMasterList(resource);
        return;
    }

    setStatus('loading', 'Menyimpan...');
    try {
        if (isEdit) {
            await api(`/master/${cfg.endpoint}/${item.id}`, { method: 'PUT', body: JSON.stringify(modalData) });
            setStatus('success', `${cfg.title} berhasil diperbarui.`);
            showToast('success', `${cfg.title} berhasil diperbarui.`);
        } else {
            await api(`/master/${cfg.endpoint}`, { method: 'POST', body: JSON.stringify(modalData) });
            setStatus('success', `${cfg.title} berhasil dibuat.`);
            showToast('success', `${cfg.title} berhasil dibuat.`);
        }

        renderMasterList(resource);
    } catch (err) {
        setStatus('error', formatApiError(err));
        showToast('error', formatApiError(err));
        renderMasterList(resource);
    }
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

    // Load dashboard statistics
    loadingCard('Memuat dashboard...');
    
    loadDashboardStats(user.role).then(stats => {
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
                ${renderDashboardStats(user.role, stats)}
            </div>
        `);
    }).catch(err => {
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
    });
}

async function loadDashboardStats(role) {
    // Load stats based on role
    const stats = {};
    
    try {
        if (role === 'super_admin') {
            // Load total candidates, users, periods
            const candidates = await api('/lingkungan-stasi/calon-penerima?per_page=1').catch(() => ({}));
            const users = await api('/master/users?per_page=1').catch(() => ({}));
            stats.totalCandidates = candidates.total ?? 0;
            stats.totalUsers = users.total ?? 0;
        } else if (role === 'ketua_lingkungan_stasi') {
            const response = await api('/lingkungan-stasi/calon-penerima?per_page=1000');
            const candidates = response.data ?? [];
            stats.draftCount = candidates.filter(c => c.status_alur === 'draft').length;
            stats.submittedCount = candidates.filter(c => c.status_alur === 'diajukan_ke_stasi').length;
            stats.totalCount = candidates.length;
        } else if (role === 'stasi') {
            const response = await api('/stasi/calon-penerima-rekap?per_page=1000');
            const candidates = response.data ?? [];
            stats.pendingCount = candidates.filter(c => c.status_alur === 'diajukan_ke_stasi').length;
            stats.approvedCount = candidates.filter(c => c.status_alur === 'disetujui_stasi').length;
            stats.rejectedCount = candidates.filter(c => c.status_alur === 'ditolak').length;
        } else if (role === 'paroki') {
            // Load decisions for recent period
            stats.pendingDecisions = 0;
            stats.finalized = 0;
        }
    } catch (e) {
        // Stats load failed - will use default dashboard
    }
    
    return stats;
}

function renderDashboardStats(role, stats) {
    const statCards = {
        super_admin: [
            { label: 'Total Calon Penerima', value: stats.totalCandidates ?? '-', icon: '👥' },
            { label: 'Total User', value: stats.totalUsers ?? '-', icon: '👤' },
            { label: 'Monitoring', value: 'Aktif', icon: '📊' },
        ],
        ketua_lingkungan_stasi: [
            { label: 'Draft', value: stats.draftCount ?? 0, icon: '📝' },
            { label: 'Diajukan', value: stats.submittedCount ?? 0, icon: '📤' },
            { label: 'Total Data', value: stats.totalCount ?? 0, icon: '📊' },
        ],
        stasi: [
            { label: 'Menunggu Approval', value: stats.pendingCount ?? 0, icon: '⏳' },
            { label: 'Disetujui', value: stats.approvedCount ?? 0, icon: '✅' },
            { label: 'Ditolak', value: stats.rejectedCount ?? 0, icon: '❌' },
        ],
        ketua_lingkungan_paroki: [
            { label: 'Proses SAW', value: 'Siap', icon: '⚙️' },
            { label: 'Ranking', value: 'Aktif', icon: '🏆' },
            { label: 'Audit', value: 'Real-time', icon: '📋' },
        ],
        paroki: [
            { label: 'Menunggu Keputusan', value: stats.pendingDecisions ?? 0, icon: '⏳' },
            { label: 'Finalisasi', value: stats.finalized ?? 0, icon: '✅' },
            { label: 'Arsip', value: 'Tersedia', icon: '📦' },
        ],
    };

    const cards = statCards[role] ?? statCards.ketua_lingkungan_stasi;

    return `
        <section class="stats-grid">
            ${cards.map(card => `
                <article class="stat-card">
                    <span class="stat-icon">${card.icon}</span>
                    <div class="stat-content">
                        <p class="stat-label">${escapeHtml(card.label)}</p>
                        <strong class="stat-value">${escapeHtml(String(card.value))}</strong>
                    </div>
                </article>
            `).join('')}
        </section>
        <section class="quick-grid">
            ${roleDashboard(role)}
        </section>
    `;
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
        // cache for detail views
        state.currentCandidates = rows;

        if (!rows.length) {
            setContent(emptyCard(emptyMessage));
            return;
        }

        setContent(`
            <div class="candidate-list-container">
                <div class="list-header">
                    <div class="list-controls">
                        <input id="candidate-search" type="text" placeholder="Cari nama atau NIK..." class="search-input">
                        <select id="candidate-status-filter" class="filter-select">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="diajukan_ke_stasi">Diajukan</option>
                            <option value="disetujui_stasi">Disetujui Stasi</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                </div>
                <div id="candidate-list" class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIK</th>
                                <th>Status</th>
                                <th>Pendapatan</th>
                                <th>Skor</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="candidate-tbody">
                            ${rows.map((item) => `
                                <tr data-status="${item.status_alur}" data-name="${(item.nama_lengkap ?? '').toLowerCase()}" data-nik="${item.nik}">
                                    <td><strong>${escapeHtml(item.nama_lengkap ?? '-')}</strong></td>
                                    <td>${escapeHtml(item.nik ?? '-')}</td>
                                    <td><span class="status-pill status-${item.status_alur}">${escapeHtml(item.status_alur ?? '-')}</span></td>
                                    <td>Rp ${(item.pendapatan_keluarga ?? 0).toLocaleString('id-ID')}</td>
                                    <td><span class="score-badge">${(item.saw_score ?? 0).toFixed(4)}</span></td>
                                    <td><button class="ghost-button view-btn" data-id="${item.id}">Lihat</button></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `);

        // Attach search and filter handlers
        const searchInput = document.getElementById('candidate-search');
        const statusFilter = document.getElementById('candidate-status-filter');
        const tbody = document.getElementById('candidate-tbody');

        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;

            tbody.querySelectorAll('tr').forEach(row => {
                const name = row.dataset.name;
                const nik = row.dataset.nik;
                const status = row.dataset.status;

                const matchesSearch = name.includes(searchText) || nik.includes(searchText);
                const matchesStatus = !statusValue || status === statusValue;

                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);

        // attach view handlers for candidate detail
        document.getElementById('content-region').querySelectorAll('.view-btn').forEach((btn) => {
            btn.addEventListener('click', () => renderCandidateDetail(btn.dataset.id));
        });
    } catch (error) {
        setContent(errorCard(error.message));
    }
}

function renderCandidateForm() {
    setContent(`
        <form id="candidate-form" class="data-form">
            <div id="form-error-container"></div>
            <div class="form-grid">
                <label>Periode ID<input name="bansos_period_id" type="number" min="1" value="1" required></label>
                <label>NIK<input name="nik" maxlength="16" minlength="16" inputmode="numeric" placeholder="16 digit NIK" required></label>
                <label>Nama Lengkap<input name="nama_lengkap" required></label>
                <label>Alamat<input name="alamat_kristen"></label>
                <label>Pendapatan Keluarga<input name="pendapatan_keluarga" type="number" min="0" required></label>
                <label>Jumlah Tanggungan<input name="jumlah_tanggungan" type="number" min="0" value="0" required></label>
                <label>Status Tempat Tinggal
                    <select name="status_tempat_tinggal" required>
                        <option value="">-- Pilih --</option>
                        <option value="milik_sendiri">Milik Sendiri</option>
                        <option value="sewa">Sewa</option>
                        <option value="numpang">Numpang</option>
                    </select>
                </label>
                <label>Status Hubungan
                    <select name="status_hubungan" required>
                        <option value="">-- Pilih --</option>
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
        const errorContainer = document.getElementById('form-error-container');
        errorContainer.innerHTML = '';

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
            errorContainer.innerHTML = '';
            setStatus('success', 'Calon penerima berhasil disimpan sebagai draft.');
            showToast('success', 'Calon penerima berhasil disimpan sebagai draft.');
        } catch (error) {
            setStatus('', '');
            
            // Display field-level validation errors
            const errors = error.body?.errors;
            if (errors && typeof errors === 'object') {
                const errorHtml = Object.entries(errors)
                    .map(([field, messages]) => {
                        const msgs = Array.isArray(messages) ? messages : [messages];
                        return `<div class="form-error-item"><strong>${escapeHtml(field)}:</strong> ${escapeHtml(msgs.join(', '))}</div>`;
                    })
                    .join('');
                
                errorContainer.innerHTML = `<div class="form-error-box">${errorHtml}</div>`;
            }
            
            setStatus('error', formatApiError(error));
            showToast('error', formatApiError(error));
        }
    });
}

async function renderCandidateDetail(id) {
    loadingCard('Memuat detail calon...');

    // try to find cached item
    let item = state.currentCandidates.find(i => String(i.id) === String(id));

    if (!item) {
        // try to reload from current view's endpoint
        const mapping = {
            'my-candidates': '/lingkungan-stasi/calon-penerima',
            'stasi-recap': '/stasi/calon-penerima-rekap',
        };

        const path = mapping[state.activeView] ?? '/lingkungan-stasi/calon-penerima';
        try {
            const resp = await api(path);
            const rows = resp.data ?? [];
            state.currentCandidates = rows;
            item = rows.find(r => String(r.id) === String(id));
        } catch (e) {
            setContent(errorCard('Gagal memuat data calon.'));
            return;
        }
    }

    if (!item) {
        setContent(errorCard('Calon penerima tidak ditemukan.'));
        return;
    }

    const actions = [];

    if (state.user.role === 'ketua_lingkungan_stasi' && item.status_alur === 'draft') {
        actions.push('<button id="btn-edit" class="primary-button">Edit</button>');
        actions.push('<button id="btn-delete" class="ghost-button danger">Hapus</button>');
        actions.push('<button id="btn-submit" class="primary-button">Ajukan ke Stasi</button>');
    }

    if (state.user.role === 'stasi' && item.status_alur === 'diajukan_ke_stasi') {
        actions.push('<button id="btn-approve" class="primary-button">Setujui</button>');
        actions.push('<button id="btn-reject" class="ghost-button danger">Tolak</button>');
    }

    setContent(`
        <section class="detail-shell">
            <h3>${escapeHtml(item.nama_lengkap)} <small>${escapeHtml(item.nik)}</small></h3>
            <div class="detail-grid">
                <div><strong>Periode</strong><div>${escapeHtml(String(item.bansos_period_id ?? '-'))}</div></div>
                <div><strong>Alamat</strong><div>${escapeHtml(item.alamat_kristen ?? '-')}</div></div>
                <div><strong>Pendapatan</strong><div>${escapeHtml(String(item.pendapatan_keluarga ?? '0'))}</div></div>
                <div><strong>Jumlah Tanggungan</strong><div>${escapeHtml(String(item.jumlah_tanggungan ?? '0'))}</div></div>
                <div><strong>Status Tempat Tinggal</strong><div>${escapeHtml(item.status_tempat_tinggal ?? '-')}</div></div>
                <div><strong>Status Hubungan</strong><div>${escapeHtml(item.status_hubungan ?? '-')}</div></div>
                <div><strong>Status Alur</strong><div><span class="status-pill">${escapeHtml(item.status_alur)}</span></div></div>
                <div><strong>Skor SAW</strong><div>${escapeHtml(String(item.saw_score ?? '0.0000'))}</div></div>
            </div>

            <div class="detail-actions">${actions.join(' ')}</div>

            <h4>Timeline</h4>
            <div id="candidate-timeline">Memuat timeline...</div>
        </section>
    `);

    // attach action handlers
    const detailContainer = document.getElementById('content-region');

    const editBtn = document.getElementById('btn-edit');
    if (editBtn) editBtn.addEventListener('click', () => renderCandidateEditForm(item));

    const deleteBtn = document.getElementById('btn-delete');
    if (deleteBtn) deleteBtn.addEventListener('click', async () => {
        const ok = await showConfirm('Hapus calon penerima ini?');
        if (!ok) return;
        const restore = setButtonLoading(deleteBtn, 'Menghapus...');
        try {
            await api(`/lingkungan-stasi/calon-penerima/${item.id}`, { method: 'DELETE' });
            setStatus('success', 'Calon penerima berhasil dihapus.');
            showToast('success', 'Calon penerima berhasil dihapus.');
            renderView(state.activeView);
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });

    const submitBtn = document.getElementById('btn-submit');
    if (submitBtn) submitBtn.addEventListener('click', async () => {
        const ok = await showConfirm('Ajukan calon penerima ke stasi?');
        if (!ok) return;
        const restore = setButtonLoading(submitBtn, 'Mengajukan...');
        try {
            await api(`/lingkungan-stasi/calon-penerima/${item.id}/ajukan`, { method: 'POST' });
            setStatus('success', 'Calon penerima berhasil diajukan.');
            showToast('success', 'Calon penerima berhasil diajukan.');
            renderView(state.activeView);
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });

    const approveBtn = document.getElementById('btn-approve');
    if (approveBtn) approveBtn.addEventListener('click', async () => {
        const ok = await showConfirm('Setujui calon penerima?');
        if (!ok) return;
        const restore = setButtonLoading(approveBtn, 'Menyetujui...');
        try {
            await api(`/stasi/calon-penerima/${item.id}/approve`, { method: 'POST' });
            setStatus('success', 'Calon penerima disetujui.');
            showToast('success', 'Calon penerima disetujui.');
            renderView(state.activeView);
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });

    const rejectBtn = document.getElementById('btn-reject');
    if (rejectBtn) rejectBtn.addEventListener('click', async () => {
        const reason = await showPrompt('Masukkan alasan penolakan:');
        if (!reason) return;
        const restore = setButtonLoading(rejectBtn, 'Mengirim...');
        try {
            await api(`/stasi/calon-penerima/${item.id}/reject`, { method: 'POST', body: JSON.stringify({ reason }) });
            setStatus('success', 'Calon penerima ditolak.');
            showToast('success', 'Calon penerima ditolak.');
            renderView(state.activeView);
        } catch (err) {
            setStatus('error', formatApiError(err));
            showToast('error', formatApiError(err));
        } finally {
            restore();
        }
    });

    // load timeline
    try {
        const res = await api(`/logs/calon-penerima/${item.id}`);
        const logs = res.data ?? [];
        const timelineHtml = logs.map(log => `
            <div class="timeline-item">
                <div class="ti-header"><strong>${escapeHtml(log.action)}</strong> <small>${escapeHtml(log.created_at ?? '')}</small></div>
                <div class="ti-body">${escapeHtml((log.user?.name ?? 'System'))} - ${escapeHtml(JSON.stringify(log.meta ?? {}))}</div>
            </div>
        `).join('');

        document.getElementById('candidate-timeline').innerHTML = timelineHtml || '<div class="state-card empty">Belum ada aktivitas.</div>';
    } catch (e) {
        document.getElementById('candidate-timeline').innerHTML = errorCard('Gagal memuat timeline.');
    }
}

function renderCandidateEditForm(item) {
    setContent(`
        <form id="candidate-edit-form" class="data-form">
            <h3>Ubah Calon Penerima</h3>
            <div class="form-grid">
                <label>Nama Lengkap<input name="nama_lengkap" value="${escapeHtml(item.nama_lengkap ?? '')}" required></label>
                <label>Alamat<input name="alamat_kristen" value="${escapeHtml(item.alamat_kristen ?? '')}"></label>
                <label>Pendapatan Keluarga<input name="pendapatan_keluarga" type="number" min="0" value="${escapeHtml(String(item.pendapatan_keluarga ?? '0'))}" required></label>
                <label>Jumlah Tanggungan<input name="jumlah_tanggungan" type="number" min="0" value="${escapeHtml(String(item.jumlah_tanggungan ?? '0'))}" required></label>
                <label>Status Tempat Tinggal
                    <select name="status_tempat_tinggal" required>
                        <option value="milik_sendiri" ${item.status_tempat_tinggal === 'milik_sendiri' ? 'selected' : ''}>Milik Sendiri</option>
                        <option value="sewa" ${item.status_tempat_tinggal === 'sewa' ? 'selected' : ''}>Sewa</option>
                        <option value="numpang" ${item.status_tempat_tinggal === 'numpang' ? 'selected' : ''}>Numpang</option>
                    </select>
                </label>
                <label>Status Hubungan
                    <select name="status_hubungan" required>
                        <option value="lajang" ${item.status_hubungan === 'lajang' ? 'selected' : ''}>Lajang</option>
                        <option value="menikah" ${item.status_hubungan === 'menikah' ? 'selected' : ''}>Menikah</option>
                        <option value="cerai" ${item.status_hubungan === 'cerai' ? 'selected' : ''}>Cerai</option>
                    </select>
                </label>
            </div>
            <label>Urgensi Tambahan<textarea name="urgensi_tambahan_tekstual" rows="4">${escapeHtml(item.urgensi_tambahan_tekstual ?? '')}</textarea></label>
            <div class="form-actions">
                <button type="submit" class="primary-button">Simpan</button>
                <button type="button" id="cancel-edit" class="ghost-button">Batal</button>
            </div>
        </form>
    `);

    document.getElementById('cancel-edit').addEventListener('click', () => renderCandidateDetail(item.id));

    document.getElementById('candidate-edit-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        setStatus('loading', 'Menyimpan perubahan...');
        const data = Object.fromEntries(new FormData(e.target).entries());
        data.pendapatan_keluarga = Number(data.pendapatan_keluarga);
        data.jumlah_tanggungan = Number(data.jumlah_tanggungan);

        try {
            await api(`/lingkungan-stasi/calon-penerima/${item.id}`, { method: 'PUT', body: JSON.stringify(data) });
            setStatus('success', 'Perubahan calon penerima disimpan.');
            renderView(state.activeView);
        } catch (err) {
            setStatus('error', formatApiError(err));
        }
    });
}

async function renderTemplates() {
    loadingCard('Memuat dokumen...');

    try {
        const response = await api('/paroki/templates');
        const templates = response.data ?? [];

        if (!templates.length) {
            setContent(`
                <div class="empty-state">
                    <p class="eyebrow">Belum ada template</p>
                    <p>Tidak ada dokumen template yang tersedia saat ini.</p>
                    ${state.user.role === 'paroki' ? '<button class="primary-button" id="btn-create-template">Buat Template Baru</button>' : ''}
                </div>
            `);
            return;
        }

        // Get generated letters
        const lettersRes = await api('/paroki/surat').catch(() => ({ data: [] }));
        const letters = lettersRes.data ?? [];

        setContent(`
            <div class="documents-container">
                <section class="doc-section">
                    <h3>📋 Template Dokumen</h3>
                    <div class="template-grid">
                        ${templates.map((template) => `
                            <article class="doc-card">
                                <div class="doc-header">
                                    <h4>${escapeHtml(template.name)}</h4>
                                    <span class="doc-type">${escapeHtml(template.type ?? 'Template')}</span>
                                </div>
                                <p class="doc-slug">${escapeHtml(template.slug)}</p>
                                <div class="doc-actions">
                                    <button class="ghost-button view-template-btn" data-id="${template.id}">Lihat</button>
                                    <button class="primary-button gen-letter-btn" data-id="${template.id}">Generate</button>
                                </div>
                            </article>
                        `).join('')}
                    </div>
                </section>

                ${letters.length > 0 ? `
                    <section class="doc-section">
                        <h3>📤 Surat yang Dibuat</h3>
                        <div class="letters-list">
                            ${letters.map((letter) => `
                                <div class="letter-item">
                                    <div>
                                        <strong>${escapeHtml(letter.title ?? 'Tanpa Judul')}</strong>
                                        <small>${escapeHtml(letter.created_at ?? '')}</small>
                                    </div>
                                    <button class="ghost-button view-letter-btn" data-id="${letter.id}">Lihat</button>
                                </div>
                            `).join('')}
                        </div>
                    </section>
                ` : ''}
            </div>
        `);

        // Attach handlers
        document.querySelectorAll('.view-template-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const templateId = btn.dataset.id;
                const template = templates.find(t => t.id === Number(templateId));
                showTemplatePreview(template);
            });
        });

        document.querySelectorAll('.gen-letter-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const templateId = btn.dataset.id;
                const template = templates.find(t => t.id === Number(templateId));
                renderGenerateLetterForm(templateId);
            });
        });

        document.querySelectorAll('.view-letter-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const letterId = btn.dataset.id;
                const letter = letters.find(l => l.id === Number(letterId));
                showLetterPreview(letter);
            });
        });

    } catch (error) {
        setContent(errorCard('Gagal memuat dokumen: ' + error.message));
    }
}

function showTemplatePreview(template) {
    ensureUiContainers();
    const root = document.getElementById('ui-modal-root');
    const wrapper = document.createElement('div');
    wrapper.className = 'modal-backdrop';
    wrapper.innerHTML = `
        <div class="modal-card modal-wide">
            <h3>${escapeHtml(template.name)}</h3>
            <div class="modal-body template-preview">
                ${template.content ? `<div class="content-preview">${template.content}</div>` : '<p>Tidak ada konten</p>'}
            </div>
            <div class="modal-actions">
                <button class="ghost-button close-btn">Tutup</button>
            </div>
        </div>
    `;
    root.appendChild(wrapper);
    wrapper.querySelector('.close-btn').addEventListener('click', () => wrapper.remove());
}

function showLetterPreview(letter) {
    ensureUiContainers();
    const root = document.getElementById('ui-modal-root');
    const wrapper = document.createElement('div');
    wrapper.className = 'modal-backdrop';
    wrapper.innerHTML = `
        <div class="modal-card modal-wide">
            <h3>${escapeHtml(letter.title ?? 'Surat')}</h3>
            <small>${escapeHtml(letter.created_at ?? '')}</small>
            <div class="modal-body letter-preview">
                ${letter.content ? `<div class="content-preview">${letter.content}</div>` : '<p>Tidak ada konten</p>'}
            </div>
            <div class="modal-actions">
                ${letter.file_path ? `<a href="${escapeHtml(letter.file_path)}" class="primary-button" download>Download</a>` : ''}
                <button class="ghost-button close-btn">Tutup</button>
            </div>
        </div>
    `;
    root.appendChild(wrapper);
    wrapper.querySelector('.close-btn').addEventListener('click', () => wrapper.remove());
}

function renderGenerateLetterForm(templateId) {
    setStatus('loading', 'Menyiapkan form...');
    ensureUiContainers();
    const root = document.getElementById('ui-modal-root');
    const wrapper = document.createElement('div');
    wrapper.className = 'modal-backdrop';
    wrapper.innerHTML = `
        <div class="modal-card">
            <h3>Generate Surat</h3>
            <form id="generate-letter-form" class="data-form">
                <label>Judul Surat
                    <input name="title" type="text" required>
                </label>
                <label>Calon Penerima (opsional)
                    <input name="calon_penerima_id" type="number" placeholder="Kosongkan untuk tidak spesifik ke calon">
                </label>
                <div class="modal-actions">
                    <button type="button" class="ghost-button cancel-btn">Batal</button>
                    <button type="submit" class="primary-button">Generate</button>
                </div>
            </form>
        </div>
    `;
    root.appendChild(wrapper);
    setStatus('', '');

    wrapper.querySelector('.cancel-btn').addEventListener('click', () => wrapper.remove());
    wrapper.querySelector('#generate-letter-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        data.document_template_id = Number(templateId);
        if (data.calon_penerima_id) data.calon_penerima_id = Number(data.calon_penerima_id);

        const restoreBtn = setButtonLoading(wrapper.querySelector('button[type=submit]'), 'Menggenerate...');
        try {
            await api('/paroki/surat/generate', { method: 'POST', body: JSON.stringify(data) });
            showToast('success', 'Surat berhasil dibuat.');
            wrapper.remove();
            renderView('documents');
        } catch (err) {
            showToast('error', formatApiError(err));
        } finally {
            restoreBtn();
        }
    });
}

function renderPlaceholder(view) {
    // Activity Log viewer untuk semua role
    if (view === 'activity-log') {
        return renderActivityLog();
    }

    const messages = {
        'admin-master': 'Mengelola master data Stasi, Lingkungan, Periode, dan User.',
        candidates: 'Monitoring lintas calon penerima dari semua stasi dan lingkungan.',
        offline: 'Aplikasi PWA offline tersedia dengan background sync.',
    };

    setContent(`
        <div class="state-card empty">
            ${escapeHtml(messages[view] ?? 'Modul ini sedang disiapkan.')}
        </div>
    `);
}

async function renderActivityLog() {
    loadingCard('Memuat log aktivitas...');
    
    // Get activity logs - can filter by calon_penerima_id if needed
    try {
        // For now, show system-wide logs (future: add pagination and filters)
        const response = await api('/logs/calon-penerima/1').catch(() => ({ data: [] }));
        const logs = response.data ?? [];

        if (!logs.length) {
            setContent(emptyCard('Tidak ada log aktivitas.'));
            return;
        }

        setContent(`
            <div class="activity-log-container">
                <div class="log-controls">
                    <input id="log-search" type="text" placeholder="Cari log..." class="search-input">
                </div>
                <div class="log-timeline">
                    ${logs.map((log, idx) => {
                        const user = log.user?.name ?? 'System';
                        const meta = typeof log.meta === 'string' ? log.meta : JSON.stringify(log.meta ?? {});
                        return `
                            <div class="log-entry">
                                <div class="log-dot"></div>
                                <div class="log-content">
                                    <div class="log-header">
                                        <strong>${escapeHtml(log.action)}</strong>
                                        <small>${escapeHtml(log.created_at ?? '')}</small>
                                    </div>
                                    <div class="log-meta">
                                        <span class="log-user">👤 ${escapeHtml(user)}</span>
                                        ${log.model_type ? `<span class="log-model">${escapeHtml(log.model_type)} #${log.model_id}</span>` : ''}
                                    </div>
                                    ${meta && meta !== '{}' ? `<details class="log-details"><summary>Detail</summary><code>${escapeHtml(meta)}</code></details>` : ''}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `);

        const searchInput = document.getElementById('log-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.log-entry').forEach(entry => {
                    const text = entry.textContent.toLowerCase();
                    entry.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    } catch (error) {
        setContent(errorCard('Gagal memuat log: ' + error.message));
    }
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
