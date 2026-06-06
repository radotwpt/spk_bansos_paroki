/**
 * CRUD Helper Functions - Reusable across all modules
 * Provides pagination, search, filter, confirm dialogs, and table rendering
 */

export class CrudManager {
    constructor(apiPath, state = {}) {
        this.apiPath = apiPath;
        this.state = {
            items: [],
            filteredItems: [],
            currentPage: 1,
            pageSize: 10,
            searchQuery: '',
            sortField: 'id',
            sortOrder: 'asc',
            filters: {},
            loading: false,
            ...state,
        };
    }

    // ==================== DATA MANAGEMENT ====================
    async fetchItems(token, apiBase) {
        this.state.loading = true;
        try {
            const response = await fetch(`${apiBase}${this.apiPath}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) throw new Error(`API error: ${response.status}`);

            const body = await response.json();
            this.state.items = body.data || body;
            this.applyFilters();
            return this.state.items;
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        } finally {
            this.state.loading = false;
        }
    }

    async createItem(data, token, apiBase) {
        try {
            const response = await fetch(`${apiBase}${this.apiPath}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(errorBody.message || `Create failed: ${response.status}`);
            }

            const result = await response.json();
            this.state.items.unshift(result.data || result);
            this.applyFilters();
            return result.data || result;
        } catch (error) {
            console.error('Create error:', error);
            throw error;
        }
    }

    async updateItem(id, data, token, apiBase) {
        try {
            const response = await fetch(`${apiBase}${this.apiPath}/${id}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(errorBody.message || `Update failed: ${response.status}`);
            }

            const result = await response.json();
            const index = this.state.items.findIndex(item => item.id === id);
            if (index >= 0) {
                this.state.items[index] = result.data || result;
            }
            this.applyFilters();
            return result.data || result;
        } catch (error) {
            console.error('Update error:', error);
            throw error;
        }
    }

    async deleteItem(id, token, apiBase) {
        try {
            const response = await fetch(`${apiBase}${this.apiPath}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(errorBody.message || `Delete failed: ${response.status}`);
            }

            this.state.items = this.state.items.filter(item => item.id !== id);
            this.applyFilters();
            return true;
        } catch (error) {
            console.error('Delete error:', error);
            throw error;
        }
    }

    // ==================== FILTERING & SEARCH ====================
    applyFilters() {
        let filtered = [...this.state.items];

        // Apply search
        if (this.state.searchQuery) {
            const query = this.state.searchQuery.toLowerCase();
            filtered = filtered.filter(item =>
                Object.values(item).some(val =>
                    String(val).toLowerCase().includes(query)
                )
            );
        }

        // Apply custom filters
        Object.keys(this.state.filters).forEach(key => {
            const value = this.state.filters[key];
            if (value !== null && value !== undefined && value !== '') {
                filtered = filtered.filter(item => item[key] === value);
            }
        });

        // Sort
        filtered.sort((a, b) => {
            let aVal = a[this.state.sortField];
            let bVal = b[this.state.sortField];

            if (typeof aVal === 'string') {
                aVal = aVal.toLowerCase();
                bVal = bVal.toLowerCase();
            }

            if (this.state.sortOrder === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });

        this.state.filteredItems = filtered;
        this.state.currentPage = 1;
    }

    setSearch(query) {
        this.state.searchQuery = query;
        this.applyFilters();
    }

    setFilter(key, value) {
        this.state.filters[key] = value;
        this.applyFilters();
    }

    setSort(field) {
        if (this.state.sortField === field) {
            this.state.sortOrder = this.state.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.state.sortField = field;
            this.state.sortOrder = 'asc';
        }
        this.applyFilters();
    }

    // ==================== PAGINATION ====================
    getPaginatedItems() {
        const start = (this.state.currentPage - 1) * this.state.pageSize;
        const end = start + this.state.pageSize;
        return this.state.filteredItems.slice(start, end);
    }

    getTotalPages() {
        return Math.ceil(this.state.filteredItems.length / this.state.pageSize);
    }

    goToPage(page) {
        const totalPages = this.getTotalPages();
        if (page >= 1 && page <= totalPages) {
            this.state.currentPage = page;
        }
    }

    // ==================== UI HELPERS ====================
    static escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return String(text ?? '').replace(/[&<>"']/g, m => map[m]);
    }

    static formatDate(date) {
        if (!date) return '-';
        if (typeof date === 'string') date = new Date(date);
        return new Intl.DateTimeFormat('id-ID').format(date);
    }

    static formatDateTime(date) {
        if (!date) return '-';
        if (typeof date === 'string') date = new Date(date);
        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'short',
            timeStyle: 'short',
        }).format(date);
    }

    static async showConfirm(title, message, confirmText = 'Konfirmasi', cancelText = 'Batal') {
        return new Promise((resolve) => {
            const backdrop = document.createElement('div');
            backdrop.className = 'fixed inset-0 z-50 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center';

            const modal = document.createElement('div');
            modal.className = 'bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 border border-neutral-200';

            modal.innerHTML = `
                <div class="p-6 md:p-8">
                    <h3 class="text-lg font-semibold text-neutral-900 mb-2">${CrudManager.escapeHtml(title)}</h3>
                    <div class="text-neutral-600 text-sm mb-6">${CrudManager.escapeHtml(message)}</div>
                    <div class="flex gap-3 justify-end">
                        <button class="btn-ghost px-4 py-2 rounded hover:bg-neutral-100 transition" id="cancel-btn">
                            ${CrudManager.escapeHtml(cancelText)}
                        </button>
                        <button class="btn-danger px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition" id="confirm-btn">
                            ${CrudManager.escapeHtml(confirmText)}
                        </button>
                    </div>
                </div>
            `;

            backdrop.appendChild(modal);
            document.body.appendChild(backdrop);

            const cancelBtn = modal.querySelector('#cancel-btn');
            const confirmBtn = modal.querySelector('#confirm-btn');

            cancelBtn.addEventListener('click', () => {
                backdrop.remove();
                resolve(false);
            });

            confirmBtn.addEventListener('click', () => {
                backdrop.remove();
                resolve(true);
            });

            // Close on backdrop click
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) {
                    backdrop.remove();
                    resolve(false);
                }
            });
        });
    }

    static renderTableHeader(columns, onSort = null) {
        return `
            <thead class="bg-neutral-50 border-b border-neutral-200">
                <tr>
                    ${columns.map(col => `
                        <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-900" ${onSort ? `style="cursor:pointer"` : ''} data-sort="${col.key}">
                            <div class="flex items-center gap-2">
                                ${CrudManager.escapeHtml(col.label)}
                                ${col.sortable !== false ? '<i class="bi bi-arrow-down-up text-xs text-neutral-400"></i>' : ''}
                            </div>
                        </th>
                    `).join('')}
                    <th class="px-4 py-3 text-center text-sm font-semibold text-neutral-900">Aksi</th>
                </tr>
            </thead>
        `;
    }

    static renderTableRow(item, columns, actions = []) {
        const cells = columns.map(col => {
            let value = item[col.key];
            let escapeValue = true;
            if (col.format) {
                value = col.format(value, item);
                escapeValue = false;
            }
            const cellValue = escapeValue ? CrudManager.escapeHtml(value) : value;
            return `<td class="px-4 py-3 text-sm text-neutral-700">${cellValue}</td>`;
        }).join('');

        const actionButtons = actions.map(action => `
            <button class="text-xs px-2 py-1 rounded mr-1 transition ${action.className || 'bg-blue-100 text-blue-700 hover:bg-blue-200'}" 
                    data-action="${action.key}" data-id="${item.id}" title="${action.label}">
                ${action.label}
            </button>
        `).join('');

        return `
            <tr class="border-b border-neutral-200 hover:bg-neutral-50 transition">
                ${cells}
                <td class="px-4 py-3 text-center">
                    ${actionButtons}
                </td>
            </tr>
        `;
    }

    static renderPagination(current, total) {
        if (total <= 1) return '';

        let html = '<div class="flex items-center justify-between mt-4">';
        html += `<p class="text-sm text-neutral-600">Halaman ${current} dari ${total}</p>`;
        html += '<div class="flex gap-2">';

        // Previous
        if (current > 1) {
            html += `<button class="px-3 py-1 border rounded hover:bg-neutral-50 transition" data-page="${current - 1}">← Sebelumnya</button>`;
        }

        // Page numbers
        let startPage = Math.max(1, current - 2);
        let endPage = Math.min(total, current + 2);

        if (startPage > 1) html += '<span class="px-2 py-1">...</span>';

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === current;
            html += `<button class="px-3 py-1 border rounded transition ${isActive ? 'bg-blue-600 text-white' : 'hover:bg-neutral-50'}" data-page="${i}">${i}</button>`;
        }

        if (endPage < total) html += '<span class="px-2 py-1">...</span>';

        // Next
        if (current < total) {
            html += `<button class="px-3 py-1 border rounded hover:bg-neutral-50 transition" data-page="${current + 1}">Berikutnya →</button>`;
        }

        html += '</div></div>';
        return html;
    }
}

export default CrudManager;
