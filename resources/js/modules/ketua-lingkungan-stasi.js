/**
 * Ketua Lingkungan Stasi Module
 * CRUD untuk calon penerima + submit to Stasi
 */

import CrudManager from '../crud-helpers.js';

export class KetuaLingkunganStasiModule {
    constructor() {
        this.crud = new CrudManager('/lingkungan-stasi/calon-penerima');
        this.state = {
            token: null,
            apiBase: null,
            editingId: null,
            showForm: false,
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
            this.showError('Gagal memuat data calon penerima');
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
                        <h1 class="text-2xl font-bold text-neutral-900">Calon Penerima</h1>
                        <p class="text-sm text-neutral-600">Total: ${this.crud.state.filteredItems.length} data</p>
                    </div>
                    <button id="btn-tambah" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        + Tambah Calon
                    </button>
                </div>

                <!-- Search & Filter -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <div class="flex gap-4 flex-wrap">
                        <input type="text" id="search-input" placeholder="Cari nama, NIK, email..." 
                               class="flex-1 min-w-[200px] px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                               value="${CrudManager.escapeHtml(this.crud.state.searchQuery)}">
                        
                        <select id="filter-status" class="px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
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
                                { key: 'email', label: 'Email' },
                                { key: 'status', label: 'Status', format: (val) => {
                                    const statusMap = {
                                        'draft': 'Draft',
                                        'diajukan_ke_stasi': 'Diajukan ke Stasi',
                                        'disetujui_stasi': 'Disetujui Stasi',
                                        'ditolak_stasi': 'Ditolak Stasi',
                                    };
                                    const colors = {
                                        'draft': 'bg-gray-100 text-gray-800',
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
                                    { key: 'email', label: 'Email' },
                                    { key: 'status', label: 'Status' },
                                ], [
                                    { key: 'edit', label: 'Edit', className: 'bg-blue-100 text-blue-700 hover:bg-blue-200' },
                                    { key: 'submit', label: 'Submit', className: 'bg-green-100 text-green-700 hover:bg-green-200' },
                                    { key: 'delete', label: 'Hapus', className: 'bg-red-100 text-red-700 hover:bg-red-200' },
                                ])).join('')}
                            </tbody>
                        </table>
                        ${CrudManager.renderPagination(this.crud.state.currentPage, totalPages)}
                    ` : `
                        <div class="p-12 text-center">
                            <p class="text-neutral-600">Tidak ada data calon penerima</p>
                        </div>
                    `}
                </div>

                <!-- Form Modal -->
                ${this.renderForm()}
            </div>
        `;

        container.innerHTML = html;
        this.attachEventListeners();
    }

    renderForm() {
        if (!this.state.showForm) return '';

        const isEdit = this.state.editingId !== null;
        const item = isEdit ? this.crud.state.items.find(i => i.id === this.state.editingId) : {};

        return `
            <div class="fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center" id="form-modal">
                <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-neutral-50 border-b border-neutral-200 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-neutral-900">
                            ${isEdit ? 'Edit Calon Penerima' : 'Tambah Calon Penerima'}
                        </h2>
                        <button id="close-form" class="text-neutral-500 hover:text-neutral-700">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <form id="candidate-form" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-900 mb-1">Nama Lengkap *</label>
                                <input type="text" name="nama_lengkap" required
                                       class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                                       value="${CrudManager.escapeHtml(item.nama_lengkap || '')}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-900 mb-1">NIK *</label>
                                <input type="text" name="nik" required
                                       class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                                       value="${CrudManager.escapeHtml(item.nik || '')}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-900 mb-1">Email</label>
                                <input type="email" name="email"
                                       class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                                       value="${CrudManager.escapeHtml(item.email || '')}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-900 mb-1">Telepon</label>
                                <input type="tel" name="telepon"
                                       class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500"
                                       value="${CrudManager.escapeHtml(item.telepon || '')}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-neutral-900 mb-1">Alamat *</label>
                                <textarea name="alamat" required rows="3"
                                          class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">${CrudManager.escapeHtml(item.alamat || '')}</textarea>
                            </div>
                        </div>

                        <div class="flex gap-3 justify-end pt-4 border-t border-neutral-200">
                            <button type="button" id="cancel-form" class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                ${isEdit ? 'Perbarui' : 'Simpan'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    attachEventListeners() {
        // Add button
        document.getElementById('btn-tambah')?.addEventListener('click', () => {
            this.state.editingId = null;
            this.state.showForm = true;
            this.render();
        });

        // Close form
        document.getElementById('close-form')?.addEventListener('click', () => {
            this.state.showForm = false;
            this.render();
        });

        document.getElementById('cancel-form')?.addEventListener('click', () => {
            this.state.showForm = false;
            this.render();
        });

        // Form submit
        document.getElementById('candidate-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleFormSubmit();
        });

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

        // Close modal on backdrop
        document.getElementById('form-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'form-modal') {
                this.state.showForm = false;
                this.render();
            }
        });
    }

    async handleFormSubmit() {
        const form = document.getElementById('candidate-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            if (this.state.editingId) {
                await this.crud.updateItem(this.state.editingId, data, this.state.token, this.state.apiBase);
                this.showSuccess('Calon penerima berhasil diperbarui');
            } else {
                await this.crud.createItem(data, this.state.token, this.state.apiBase);
                this.showSuccess('Calon penerima berhasil ditambahkan');
            }
            this.state.showForm = false;
            this.render();
        } catch (error) {
            this.showError(error.message);
        }
    }

    async handleTableAction(action, id) {
        const item = this.crud.state.items.find(i => i.id === id);

        switch (action) {
            case 'edit':
                this.state.editingId = id;
                this.state.showForm = true;
                this.render();
                break;

            case 'submit':
                if (item.status !== 'draft') {
                    this.showError('Hanya calon dengan status draft yang bisa disubmit');
                    return;
                }
                const confirmed = await CrudManager.showConfirm(
                    'Konfirmasi Submit',
                    `Apakah Anda yakin ingin mengirim calon "${item.nama_lengkap}" ke Stasi?`,
                    'Ya, Submit',
                    'Batal'
                );
                if (confirmed) {
                    await this.submitToStasi(id);
                }
                break;

            case 'delete':
                const confirmDelete = await CrudManager.showConfirm(
                    'Konfirmasi Hapus',
                    `Apakah Anda yakin ingin menghapus "${item.nama_lengkap}"?`,
                    'Ya, Hapus',
                    'Batal'
                );
                if (confirmDelete) {
                    await this.handleDelete(id);
                }
                break;
        }
    }

    async submitToStasi(id) {
        try {
            const response = await fetch(`${this.state.apiBase}/lingkungan-stasi/calon-penerima/${id}/ajukan`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.state.token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) throw new Error('Gagal mengirim ke Stasi');

            const result = await response.json();
            const index = this.crud.state.items.findIndex(i => i.id === id);
            if (index >= 0) {
                this.crud.state.items[index] = result.data || result;
            }
            this.crud.applyFilters();
            this.showSuccess('Calon berhasil dikirim ke Stasi');
            this.render();
        } catch (error) {
            this.showError(error.message);
        }
    }

    async handleDelete(id) {
        try {
            await this.crud.deleteItem(id, this.state.token, this.state.apiBase);
            this.showSuccess('Calon berhasil dihapus');
            this.render();
        } catch (error) {
            this.showError(error.message);
        }
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

export default KetuaLingkunganStasiModule;
