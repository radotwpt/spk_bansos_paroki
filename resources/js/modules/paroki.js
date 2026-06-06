/**
 * Paroki Module
 * Finalize penerima decision + generate surat edaran
 */

import CrudManager from '../crud-helpers.js';

export class ParokiModule {
    constructor() {
        this.crud = new CrudManager('/paroki/ranking-results');
        this.state = {
            token: null,
            apiBase: null,
            periods: [],
            templates: [],
            selectedPeriod: null,
            selectedCandidate: null,
            showDetailModal: false,
        };
    }

    async init(token, apiBase) {
        this.state.token = token;
        this.state.apiBase = apiBase;
        await this.loadPeriods();
        await this.loadTemplates();
        await this.loadResults();
    }

    async loadPeriods() {
        try {
            const response = await fetch(`${this.state.apiBase}/paroki/ranking-periods`, {
                headers: {
                    'Authorization': `Bearer ${this.state.token}`,
                    'Accept': 'application/json',
                },
            });
            if (!response.ok) throw new Error('Gagal memuat periode');
            const result = await response.json();
            this.state.periods = result.data || [];
            if (!this.state.selectedPeriod && this.state.periods.length > 0) {
                this.state.selectedPeriod = this.state.periods[0].id;
            }
        } catch (error) {
            this.showError(error.message);
        }
    }

    async loadTemplates() {
        try {
            const response = await fetch(`${this.state.apiBase}/templates?type=edaran_paroki&per_page=50`, {
                headers: {
                    'Authorization': `Bearer ${this.state.token}`,
                    'Accept': 'application/json',
                },
            });
            if (!response.ok) throw new Error('Gagal memuat template surat');
            const result = await response.json();
            this.state.templates = result.data?.data || result.data || [];
        } catch (error) {
            console.error(error);
        }
    }

    async loadResults() {
        if (!this.state.selectedPeriod) {
            this.crud.state.items = [];
            this.crud.applyFilters();
            this.render();
            return;
        }

        try {
            const response = await fetch(
                `${this.state.apiBase}/paroki/ranking/${this.state.selectedPeriod}`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );
            if (!response.ok) throw new Error('Gagal memuat hasil ranking');
            const result = await response.json();
            this.crud.state.items = result.data || [];
            this.crud.applyFilters();
            this.render();
        } catch (error) {
            this.showError('Gagal memuat hasil ranking');
        }
    }

    render() {
        const container = document.getElementById('content-region');
        if (!container) return;

        const totalPages = this.crud.getTotalPages();
        const currentItems = this.crud.getPaginatedItems();

        let html = `
            <div class="space-y-4">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900">Hasil Ranking Final</h1>
                        <p class="text-sm text-neutral-600">Finalisasi keputusan penerima bansos</p>
                    </div>
                    <button id="btn-generate-surat" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" ${this.state.templates.length === 0 ? 'disabled' : ''}>
                        📄 Buat Surat Edaran
                    </button>
                </div>

                <!-- Search & Filter -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <div class="flex gap-4 flex-wrap">
                        <select id="period-select" class="px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                            ${this.state.periods.map((period) => `
                                <option value="${period.id}" ${String(this.state.selectedPeriod) === String(period.id) ? 'selected' : ''}>
                                    ${CrudManager.escapeHtml(period.nama_periode || period.name || `Periode ${period.id}`)} (${CrudManager.escapeHtml(period.tahun || '-')})
                                </option>
                            `).join('')}
                        </select>
                        <input type="text" id="search-input" placeholder="Cari nama, NIK..." 
                               class="flex-1 min-w-[200px] px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                               value="${CrudManager.escapeHtml(this.crud.state.searchQuery)}">
                        
                        <select id="filter-decision" class="px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Semua Keputusan</option>
                            <option value="disetujui_paroki">Disetujui Paroki</option>
                            <option value="disetujui_stasi">Menunggu Finalisasi</option>
                            <option value="diranking_lingkungan_paroki">Diranking</option>
                        </select>

                        <button id="btn-reset" class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
                    ${currentItems.length > 0 ? `
                        <table class="w-full text-sm" id="results-table">
                            <thead class="bg-neutral-50 border-b border-neutral-200">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-neutral-900">Rank</th>
                                    <th class="px-4 py-2 text-left font-semibold text-neutral-900">Nama</th>
                                    <th class="px-4 py-2 text-left font-semibold text-neutral-900">NIK</th>
                                    <th class="px-4 py-2 text-right font-semibold text-neutral-900">Skor</th>
                                    <th class="px-4 py-2 text-center font-semibold text-neutral-900">Keputusan</th>
                                    <th class="px-4 py-2 text-center font-semibold text-neutral-900">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${currentItems.map((item, idx) => `
                                    <tr class="border-b border-neutral-200 hover:bg-neutral-50">
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">
                                                ${idx + 1}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 font-medium text-neutral-900">${CrudManager.escapeHtml(item.nama_lengkap || item.name)}</td>
                                        <td class="px-4 py-2">${CrudManager.escapeHtml(item.nik)}</td>
                                        <td class="px-4 py-2 text-right font-semibold">${(item.skor || item.score || 0).toFixed(4)}</td>
                                        <td class="px-4 py-2 text-center">
                                            ${this.renderDecisionBadge(item.status_alur || 'pending')}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button data-action="decide" data-id="${item.id}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                                ${item.status_alur === 'disetujui_paroki' ? 'Ubah Nominal' : 'Finalisasi'}
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                        ${CrudManager.renderPagination(this.crud.state.currentPage, totalPages)}
                    ` : `
                        <div class="p-12 text-center">
                            <p class="text-neutral-600">Tidak ada hasil ranking untuk difinalkan</p>
                        </div>
                    `}
                </div>

                <!-- Decision Modal -->
                ${this.renderDecisionModal()}
            </div>
        `;

        container.innerHTML = html;
        this.attachEventListeners();
    }

    renderDecisionBadge(status) {
        const badges = {
            'pending': '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-medium">Pending</span>',
            'diranking_lingkungan_paroki': '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">Diranking</span>',
            'disetujui_stasi': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">Menunggu Finalisasi</span>',
            'disetujui_paroki': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">✓ Disetujui Paroki</span>',
        };
        return badges[status] || badges['pending'];
    }

    renderDecisionModal() {
        if (!this.state.showDetailModal || !this.state.selectedCandidate) return '';

        const item = this.state.selectedCandidate;
        const currentNominal = item.nominal_bansos_disetujui || '';

        return `
            <div class="fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center" id="decision-modal">
                <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-neutral-50 border-b border-neutral-200 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-neutral-900">Keputusan Penerima</h2>
                        <button id="close-modal" class="text-neutral-500 hover:text-neutral-700">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Info -->
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm text-neutral-600">Nama Calon</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.nama_lengkap || item.name)}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">NIK</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.nik)}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">Skor Ranking</label>
                                <p class="font-semibold text-neutral-900">${(item.skor || item.score || 0).toFixed(4)}</p>
                            </div>
                        </div>

                        <!-- Decision Form -->
                        <form id="decision-form" class="space-y-4 pt-4 border-t border-neutral-200">
                            <div>
                                <label class="block text-sm font-medium text-neutral-900 mb-2">Nominal Bansos Disetujui *</label>
                                <input id="decision-nominal" type="number" min="0" step="1000" required
                                       class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                                       value="${CrudManager.escapeHtml(currentNominal)}">
                            </div>

                            <div class="flex gap-3 justify-end pt-4 border-t border-neutral-200">
                                <button type="button" id="close-btn" class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Simpan Keputusan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
    }

    attachEventListeners() {
        // Search
        document.getElementById('period-select')?.addEventListener('change', async (e) => {
            this.state.selectedPeriod = e.target.value;
            await this.loadResults();
        });

        document.getElementById('search-input')?.addEventListener('input', (e) => {
            this.crud.setSearch(e.target.value);
            this.render();
        });

        // Filter
        document.getElementById('filter-decision')?.addEventListener('change', (e) => {
            this.crud.setFilter('status_alur', e.target.value);
            this.render();
        });

        // Reset
        document.getElementById('btn-reset')?.addEventListener('click', () => {
            this.crud.state.searchQuery = '';
            this.crud.state.filters = {};
            this.crud.applyFilters();
            this.render();
        });

        // Generate surat edaran
        document.getElementById('btn-generate-surat')?.addEventListener('click', () => {
            this.generateSuratEdaran();
        });

        // Decision actions
        document.querySelectorAll('[data-action="decide"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id);
                const item = this.crud.state.items.find(i => i.id === id);
                if (item) {
                    this.state.selectedCandidate = item;
                    this.state.showDetailModal = true;
                    this.render();
                }
            });
        });

        // Modal
        document.getElementById('close-modal')?.addEventListener('click', () => this.closeModal());
        document.getElementById('close-btn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('decision-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'decision-modal') this.closeModal();
        });

        // Decision form submit
        document.getElementById('decision-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitDecision();
        });

        // Pagination
        document.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.crud.goToPage(parseInt(e.target.dataset.page));
                this.render();
            });
        });
    }

    async submitDecision() {
        const form = document.getElementById('decision-form');
        if (!form) return;

        const nominal = parseFloat(document.getElementById('decision-nominal')?.value || '0');
        const item = this.state.selectedCandidate;
        if (!nominal || nominal <= 0) {
            this.showError('Nominal bansos wajib diisi dan lebih besar dari 0.');
            return;
        }

        try {
            const response = await fetch(
                `${this.state.apiBase}/paroki/penerima/${item.id}/keputusan`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        nominal,
                    }),
                }
            );

            if (!response.ok) throw new Error('Gagal menyimpan keputusan');

            // Update local item
            const index = this.crud.state.items.findIndex(i => i.id === item.id);
            if (index >= 0) {
                this.crud.state.items[index] = {
                    ...this.crud.state.items[index],
                    status_alur: 'disetujui_paroki',
                    nominal_bansos_disetujui: nominal,
                };
            }

            this.showSuccess('Keputusan berhasil disimpan');
            this.closeModal();
        } catch (error) {
            this.showError(error.message);
        }
    }

    async generateSuratEdaran() {
        if (!this.state.selectedPeriod) {
            this.showError('Pilih periode terlebih dahulu.');
            return;
        }

        if (this.state.templates.length === 0) {
            this.showError('Template edaran paroki belum tersedia.');
            return;
        }

        const templateId = this.state.templates[0].id;

        const confirmed = await CrudManager.showConfirm(
            'Buat Surat Edaran',
            'Apakah Anda yakin ingin membuat surat edaran untuk periode ini?',
            'Ya, Buat',
            'Batal'
        );

        if (!confirmed) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/paroki/surat-edaran/generate`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        template_id: templateId,
                        period_id: parseInt(this.state.selectedPeriod),
                    }),
                }
            );

            if (!response.ok) throw new Error('Gagal membuat surat edaran');

            const result = await response.json();
            this.showSuccess('Surat edaran berhasil dibuat');

            // Optional: Download the generated file if available
            if (result.data?.file_url || result.file_url) {
                const fileUrl = result.data?.file_url || result.file_url;
                const a = document.createElement('a');
                a.href = fileUrl;
                a.download = `Surat-Edaran-${new Date().toISOString().split('T')[0]}.pdf`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        } catch (error) {
            this.showError(error.message);
        }
    }

    closeModal() {
        this.state.showDetailModal = false;
        this.state.selectedCandidate = null;
        this.render();
    }

    showError(message) {
        const statusRegion = document.getElementById('status-region');
        if (statusRegion) {
            statusRegion.innerHTML = `
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    <strong>Error:</strong> ${CrudManager.escapeHtml(message)}
                </div>
            `;
        }
    }

    showSuccess(message) {
        const statusRegion = document.getElementById('status-region');
        if (statusRegion) {
            statusRegion.innerHTML = `
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    ✓ ${CrudManager.escapeHtml(message)}
                </div>
            `;
            setTimeout(() => {
                statusRegion.innerHTML = '';
            }, 3000);
        }
    }
}

export default ParokiModule;
