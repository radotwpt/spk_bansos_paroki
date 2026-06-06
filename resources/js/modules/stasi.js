/**
 * Stasi Module
 * Review calon penerima + approve/reject
 */

import CrudManager from '../crud-helpers.js';

export class StasiModule {
    constructor() {
        this.crud = new CrudManager('/stasi/calon-penerima-rekap');
        this.state = {
            token: null,
            apiBase: null,
            selectedCandidate: null,
            showDetailModal: false,
        };
    }

    async init(token, apiBase) {
        this.state.token = token;
        this.state.apiBase = apiBase;
        await this.loadCandidates();
    }

    async loadCandidates() {
        try {
            await this.crud.fetchItems(this.state.token, this.state.apiBase);
            this.render();
        } catch (error) {
            this.showError('Gagal memuat recap calon penerima');
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
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Rekap Calon Penerima</h1>
                    <p class="text-sm text-neutral-600">Total: ${this.crud.state.filteredItems.length} data</p>
                </div>

                <!-- Search & Filter -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <div class="flex gap-4 flex-wrap">
                        <input type="text" id="search-input" placeholder="Cari nama, NIK, status..." 
                               class="flex-1 min-w-[200px] px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                               value="${CrudManager.escapeHtml(this.crud.state.searchQuery)}">
                        
                        <select id="filter-status" class="px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="diajukan_ke_stasi">Diajukan ke Stasi</option>
                            <option value="disetujui_stasi">Disetujui Stasi</option>
                            <option value="ditolak_stasi">Ditolak Stasi</option>
                        </select>

                        <button id="btn-reset" class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
                    ${currentItems.length > 0 ? `
                        <table class="w-full" id="candidates-table">
                            ${CrudManager.renderTableHeader([
                                { key: 'nama_lengkap', label: 'Nama Lengkap' },
                                { key: 'nik', label: 'NIK' },
                                { key: 'lingkungan_stasi_name', label: 'Lingkungan Stasi' },
                                { key: 'status', label: 'Status', format: (val) => {
                                    const statusMap = {
                                        'diajukan_ke_stasi': 'Diajukan ke Stasi',
                                        'disetujui_stasi': 'Disetujui Stasi',
                                        'ditolak_stasi': 'Ditolak Stasi',
                                    };
                                    const colors = {
                                        'diajukan_ke_stasi': 'bg-blue-100 text-blue-800',
                                        'disetujui_stasi': 'bg-green-100 text-green-800',
                                        'ditolak_stasi': 'bg-red-100 text-red-800',
                                    };
                                    return `<span class="px-2 py-1 rounded text-xs font-medium ${colors[val]}">${statusMap[val] || val}</span>`;
                                }},
                            ])}
                            <tbody>
                                ${currentItems.map(item => CrudManager.renderTableRow(item, [
                                    { key: 'nama_lengkap', label: 'Nama' },
                                    { key: 'nik', label: 'NIK' },
                                    { key: 'lingkungan_stasi_name', label: 'Lingkungan' },
                                    { key: 'status', label: 'Status' },
                                ], [
                                    { key: 'view', label: 'Lihat', className: 'bg-blue-100 text-blue-700 hover:bg-blue-200' },
                                    ...(item.status === 'diajukan_ke_stasi' ? [
                                        { key: 'approve', label: 'Setujui', className: 'bg-green-100 text-green-700 hover:bg-green-200' },
                                        { key: 'reject', label: 'Tolak', className: 'bg-red-100 text-red-700 hover:bg-red-200' },
                                    ] : []),
                                ])).join('')}
                            </tbody>
                        </table>
                        ${CrudManager.renderPagination(this.crud.state.currentPage, totalPages)}
                    ` : `
                        <div class="p-12 text-center">
                            <p class="text-neutral-600">Tidak ada calon penerima untuk direview</p>
                        </div>
                    `}
                </div>

                <!-- Detail Modal -->
                ${this.renderDetailModal()}
            </div>
        `;

        container.innerHTML = html;
        this.attachEventListeners();
    }

    renderDetailModal() {
        if (!this.state.showDetailModal || !this.state.selectedCandidate) return '';

        const item = this.state.selectedCandidate;
        return `
            <div class="fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center" id="detail-modal">
                <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-neutral-50 border-b border-neutral-200 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-neutral-900">Detail Calon Penerima</h2>
                        <button id="close-modal" class="text-neutral-500 hover:text-neutral-700">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Personal Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-neutral-600">Nama Lengkap</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.nama_lengkap)}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">NIK</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.nik)}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">Email</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.email || '-')}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">Telepon</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.telepon || '-')}</p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="text-sm text-neutral-600">Alamat</label>
                            <p class="text-neutral-900">${CrudManager.escapeHtml(item.alamat || '-')}</p>
                        </div>

                        <!-- Metadata -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-200">
                            <div>
                                <label class="text-sm text-neutral-600">Lingkungan Stasi</label>
                                <p class="font-semibold text-neutral-900">${CrudManager.escapeHtml(item.lingkungan_stasi_name || '-')}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">Status</label>
                                <p class="font-semibold text-neutral-900">
                                    ${item.status === 'diajukan_ke_stasi' ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Diajukan ke Stasi</span>' : 
                                      item.status === 'disetujui_stasi' ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Disetujui</span>' :
                                      '<span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Ditolak</span>'}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">Dibuat</label>
                                <p class="text-neutral-900">${CrudManager.formatDateTime(item.created_at)}</p>
                            </div>
                            <div>
                                <label class="text-sm text-neutral-600">Diperbarui</label>
                                <p class="text-neutral-900">${CrudManager.formatDateTime(item.updated_at)}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 justify-end pt-4 border-t border-neutral-200">
                            <button id="close-btn" class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                                Tutup
                            </button>
                            ${item.status === 'diajukan_ke_stasi' ? `
                                <button id="reject-btn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    Tolak
                                </button>
                                <button id="approve-btn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                    Setujui
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    attachEventListeners() {
        // Search
        document.getElementById('search-input')?.addEventListener('input', (e) => {
            this.crud.setSearch(e.target.value);
            this.render();
        });

        // Filter
        document.getElementById('filter-status')?.addEventListener('change', (e) => {
            this.crud.setFilter('status', e.target.value);
            this.render();
        });

        // Reset
        document.getElementById('btn-reset')?.addEventListener('click', () => {
            this.crud.state.searchQuery = '';
            this.crud.state.filters = {};
            this.crud.applyFilters();
            this.render();
        });

        // Table actions
        document.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.action;
                const id = parseInt(e.currentTarget.dataset.id);
                this.handleTableAction(action, id);
            });
        });

        // Pagination
        document.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.crud.goToPage(parseInt(e.target.dataset.page));
                this.render();
            });
        });

        // Modal
        document.getElementById('close-modal')?.addEventListener('click', () => this.closeModal());
        document.getElementById('close-btn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('detail-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'detail-modal') this.closeModal();
        });

        // Modal actions
        document.getElementById('approve-btn')?.addEventListener('click', () => this.approveCandidate());
        document.getElementById('reject-btn')?.addEventListener('click', () => this.rejectCandidate());
    }

    async handleTableAction(action, id) {
        const item = this.crud.state.items.find(i => i.id === id);

        switch (action) {
            case 'view':
                this.state.selectedCandidate = item;
                this.state.showDetailModal = true;
                this.render();
                break;
            case 'approve':
                this.state.selectedCandidate = item;
                this.state.showDetailModal = true;
                this.render();
                break;
            case 'reject':
                this.state.selectedCandidate = item;
                this.state.showDetailModal = true;
                this.render();
                break;
        }
    }

    async approveCandidate() {
        const item = this.state.selectedCandidate;
        if (!item) return;

        const confirmed = await CrudManager.showConfirm(
            'Konfirmasi Persetujuan',
            `Apakah Anda yakin ingin menyetujui calon "${item.nama_lengkap}"?`,
            'Ya, Setujui',
            'Batal'
        );

        if (confirmed) {
            try {
                const response = await fetch(
                    `${this.state.apiBase}/stasi/calon-penerima/${item.id}/approve`,
                    {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.state.token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    }
                );

                if (!response.ok) throw new Error('Gagal menyetujui calon');

                await this.loadCandidates();
                this.showSuccess('Calon berhasil disetujui');
                this.closeModal();
            } catch (error) {
                this.showError(error.message);
            }
        }
    }

    async rejectCandidate() {
        const item = this.state.selectedCandidate;
        if (!item) return;

        const confirmed = await CrudManager.showConfirm(
            'Konfirmasi Penolakan',
            `Apakah Anda yakin ingin menolak calon "${item.nama_lengkap}"?`,
            'Ya, Tolak',
            'Batal'
        );

        if (confirmed) {
            const reason = window.prompt('Masukkan alasan penolakan:', '')?.trim();
            if (!reason) {
                this.showError('Alasan penolakan wajib diisi.');
                return;
            }

            try {
                const response = await fetch(
                    `${this.state.apiBase}/stasi/calon-penerima/${item.id}/reject`,
                    {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.state.token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ reason }),
                    }
                );

                if (!response.ok) {
                    const body = await response.json().catch(() => ({}));
                    throw new Error(body.message || 'Gagal menolak calon');
                }

                await this.loadCandidates();
                this.showSuccess('Calon berhasil ditolak');
                this.closeModal();
            } catch (error) {
                this.showError(error.message);
            }
        }
    }

    closeModal() {
        this.state.showDetailModal = false;
        this.state.selectedCandidate = null;
        this.render();
    }

    showError(message) {
        console.error(message);
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

export default StasiModule;
