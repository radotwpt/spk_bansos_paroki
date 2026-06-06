/**
 * Ketua Lingkungan Paroki Module
 * Dashboard, Reporting, SAW weights + execute ranking
 */

import CrudManager from '../crud-helpers.js';

export class KetuaLingkunganParokiModule {
    constructor() {
        this.state = {
            token: null,
            apiBase: null,
            view: 'dashboard', // dashboard, ranking-list, saw-details, activity-logs, reporting
            periods: [],
            selectedPeriod: null,
            dashboardData: null,
            rankingList: [],
            selectedCandidate: null,
            sawDetails: null,
            activityLogs: [],
            reportingSummary: null,
            weights: {
                C1: 0.40,  // Cost criterion
                C2: 0.30,
                C3: 0.15,
                C4: 0.15,
            },
            weights_description: {
                C1: 'Penghasilan Bulanan (Cost)',
                C2: 'Jumlah Tanggungan',
                C3: 'Status Kesehatan',
                C4: 'Kondisi Tempat Tinggal',
            },
            previewResults: null,
            finalResults: null,
            showExecuteConfirm: false,
            pagination: {
                page: 1,
                limit: 20,
                total: 0,
                lastPage: 0,
            },
        };
    }

    async init(token, apiBase) {
        this.state.token = token;
        this.state.apiBase = apiBase;
        await this.loadPeriods();
        await this.loadDashboard();
        this.render();
    }

    async loadPeriods() {
        try {
            const response = await fetch(`${this.state.apiBase}/ranking/periods`, {
                headers: {
                    'Authorization': `Bearer ${this.state.token}`,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) throw new Error('Gagal memuat periode');
            const result = await response.json();
            this.state.periods = result.data || [];
            
            // Auto-select first period if available
            if (this.state.periods.length > 0 && !this.state.selectedPeriod) {
                this.state.selectedPeriod = this.state.periods[0].id;
            }
        } catch (error) {
            this.showError('Gagal memuat daftar periode');
        }
    }

    async loadDashboard() {
        if (!this.state.selectedPeriod) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/lingkungan-paroki/dashboard`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.ok) {
                const result = await response.json();
                this.state.dashboardData = result.data;
            }
        } catch (error) {
            console.error('Gagal memuat dashboard:', error);
        }
    }

    async loadRankingList(page = 1) {
        if (!this.state.selectedPeriod) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/lingkungan-paroki/ranking-list?period_id=${this.state.selectedPeriod}&page=${page}&limit=${this.state.pagination.limit}`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.ok) {
                const result = await response.json();
                this.state.rankingList = result.data.data || [];
                this.state.pagination = {
                    page: result.data.current_page,
                    limit: result.data.per_page,
                    total: result.data.total,
                    lastPage: result.data.last_page,
                };
            }
        } catch (error) {
            console.error('Gagal memuat ranking list:', error);
            this.showError('Gagal memuat daftar ranking');
        }
    }

    async loadActivityLogs(page = 1) {
        if (!this.state.selectedPeriod) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/lingkungan-paroki/activity-logs?period_id=${this.state.selectedPeriod}&page=${page}&limit=50`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.ok) {
                const result = await response.json();
                this.state.activityLogs = result.data.data || [];
            }
        } catch (error) {
            console.error('Gagal memuat activity logs:', error);
        }
    }

    async loadReportingSummary() {
        if (!this.state.selectedPeriod) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/lingkungan-paroki/reporting-summary?period_id=${this.state.selectedPeriod}`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.ok) {
                const result = await response.json();
                this.state.reportingSummary = result.data;
            }
        } catch (error) {
            console.error('Gagal memuat reporting summary:', error);
        }
    }

    async loadSawDetails(candidateId) {
        try {
            const response = await fetch(
                `${this.state.apiBase}/lingkungan-paroki/saw-details/${candidateId}`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.ok) {
                const result = await response.json();
                this.state.sawDetails = result.data;
                this.state.view = 'saw-details';
                this.render();
            }
        } catch (error) {
            this.showError('Gagal memuat detail SAW');
        }
    }

    render() {
        const container = document.getElementById('content-region');
        if (!container) return;

        let html = this.renderNavigation();
        
        if (this.state.view === 'dashboard') {
            html += this.renderDashboard();
        } else if (this.state.view === 'ranking-list') {
            html += this.renderRankingList();
        } else if (this.state.view === 'saw-details') {
            html += this.renderSawDetails();
        } else if (this.state.view === 'activity-logs') {
            html += this.renderActivityLogs();
        } else if (this.state.view === 'reporting') {
            html += this.renderReporting();
        } else if (this.state.view === 'saw-config') {
            html += this.renderSawConfig();
        }

        container.innerHTML = html;
        this.attachEventListeners();
    }

    renderNavigation() {
        return `
            <div class="mb-6 border-b border-neutral-200">
                <div class="flex gap-2 overflow-x-auto pb-4">
                    <button data-view="dashboard" class="px-4 py-2 ${this.state.view === 'dashboard' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-neutral-600 hover:text-neutral-900'} transition">
                        📊 Dashboard
                    </button>
                    <button data-view="ranking-list" class="px-4 py-2 ${this.state.view === 'ranking-list' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-neutral-600 hover:text-neutral-900'} transition">
                        📋 Daftar Ranking
                    </button>
                    <button data-view="reporting" class="px-4 py-2 ${this.state.view === 'reporting' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-neutral-600 hover:text-neutral-900'} transition">
                        📈 Laporan
                    </button>
                    <button data-view="activity-logs" class="px-4 py-2 ${this.state.view === 'activity-logs' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-neutral-600 hover:text-neutral-900'} transition">
                        📝 Log Aktivitas
                    </button>
                    <button data-view="saw-config" class="px-4 py-2 ${this.state.view === 'saw-config' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-neutral-600 hover:text-neutral-900'} transition">
                        ⚙️ SAW Config
                    </button>
                </div>
            </div>
        `;
    }

    renderDashboard() {
        if (!this.state.dashboardData) {
            return `
                <div class="p-4 text-center text-neutral-600">
                    <p>Memuat dashboard...</p>
                </div>
            `;
        }

        const { statistics, top_candidates, score_distribution } = this.state.dashboardData;

        return `
            <div class="space-y-6">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Dashboard</h1>
                    <p class="text-sm text-neutral-600">Ringkasan status perankingan bansos</p>
                </div>

                <!-- Period Selection -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <label class="block text-sm font-medium text-neutral-900 mb-2">Periode Bansos</label>
                    <select id="period-select" class="w-full md:w-64 px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Periode --</option>
                        ${this.state.periods.map(p => `
                            <option value="${p.id}" ${this.state.selectedPeriod == p.id ? 'selected' : ''}>
                                ${CrudManager.escapeHtml(p.nama_periode || p.name)} (${p.tahun})
                            </option>
                        `).join('')}
                    </select>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-1">Total Calon</div>
                        <div class="text-3xl font-bold text-neutral-900">${statistics.total_candidates}</div>
                    </div>
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-1">Sudah Diranking</div>
                        <div class="text-3xl font-bold text-green-600">${statistics.ranked_candidates}</div>
                    </div>
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-1">Belum Diranking</div>
                        <div class="text-3xl font-bold text-orange-600">${statistics.pending_candidates}</div>
                    </div>
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-1">Progress</div>
                        <div class="text-3xl font-bold text-blue-600">${statistics.ranking_progress}%</div>
                    </div>
                </div>

                <!-- Top Candidates -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <h3 class="font-semibold text-neutral-900 mb-4">Top 5 Calon Penerima</h3>
                        <div class="space-y-3">
                            ${top_candidates.map((item, idx) => `
                                <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-lg">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center font-semibold text-blue-700 text-sm">
                                        ${item.rank}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-neutral-900 truncate">${CrudManager.escapeHtml(item.nama)}</div>
                                        <div class="text-xs text-neutral-600">${CrudManager.escapeHtml(item.nik)}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-neutral-900">${item.score}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Score Distribution -->
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <h3 class="font-semibold text-neutral-900 mb-4">Distribusi Skor</h3>
                        <div class="space-y-2">
                            ${score_distribution.map(item => `
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-neutral-600 w-16">${item.score_range}</span>
                                    <div class="flex-1 h-8 bg-neutral-100 rounded-lg overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: ${(item.count / statistics.total_candidates * 100)}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-neutral-900 w-12 text-right">${item.count}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderRankingList() {
        return `
            <div class="space-y-6">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Daftar Ranking</h1>
                    <p class="text-sm text-neutral-600">Lihat hasil perankingan calon penerima</p>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-neutral-50 border-b border-neutral-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-neutral-900">Rank</th>
                                    <th class="px-4 py-3 text-left font-semibold text-neutral-900">Nama Lengkap</th>
                                    <th class="px-4 py-3 text-left font-semibold text-neutral-900">NIK</th>
                                    <th class="px-4 py-3 text-right font-semibold text-neutral-900">Skor SAW</th>
                                    <th class="px-4 py-3 text-center font-semibold text-neutral-900">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${this.state.rankingList.map((item, idx) => `
                                    <tr class="border-b border-neutral-200 hover:bg-neutral-50 transition">
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                                                ${item.rank || '-'}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-neutral-900">${CrudManager.escapeHtml(item.nama_lengkap)}</td>
                                        <td class="px-4 py-3 text-neutral-600">${CrudManager.escapeHtml(item.nik)}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-neutral-900">${item.score || '-'}</td>
                                        <td class="px-4 py-3 text-center">
                                            <button data-action="view-saw" data-id="${item.id}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-neutral-200 flex justify-between items-center">
                        <div class="text-sm text-neutral-600">
                            Showing ${this.state.rankingList.length} of ${this.state.pagination.total} records
                        </div>
                        <div class="flex gap-2">
                            <button data-action="prev-page" ${this.state.pagination.page <= 1 ? 'disabled' : ''} class="px-3 py-1 border border-neutral-300 rounded-lg hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                ← Previous
                            </button>
                            <span class="px-3 py-1 text-sm text-neutral-600">
                                Page ${this.state.pagination.page} of ${this.state.pagination.lastPage}
                            </span>
                            <button data-action="next-page" ${this.state.pagination.page >= this.state.pagination.lastPage ? 'disabled' : ''} class="px-3 py-1 border border-neutral-300 rounded-lg hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Next →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderSawDetails() {
        if (!this.state.sawDetails) {
            return `<div class="p-4 text-center text-neutral-600">Memuat detail SAW...</div>`;
        }

        const { candidate, saw_result } = this.state.sawDetails;

        return `
            <div class="space-y-6">
                <!-- Back Button -->
                <button data-view="ranking-list" class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Kembali ke Daftar Ranking
                </button>

                <!-- Candidate Info -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informasi Calon Penerima</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-neutral-600 mb-1">Nama Lengkap</div>
                            <div class="font-medium text-neutral-900">${CrudManager.escapeHtml(candidate.nama)}</div>
                        </div>
                        <div>
                            <div class="text-sm text-neutral-600 mb-1">NIK</div>
                            <div class="font-medium text-neutral-900">${CrudManager.escapeHtml(candidate.nik)}</div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-sm text-neutral-600 mb-1">Alamat</div>
                            <div class="font-medium text-neutral-900">${CrudManager.escapeHtml(candidate.alamat)}</div>
                        </div>
                    </div>
                </div>

                <!-- SAW Result -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Hasil Perankingan SAW</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-sm text-neutral-600 mb-1">Rank</div>
                            <div class="text-3xl font-bold text-blue-600">#${saw_result.rank}</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-sm text-neutral-600 mb-1">Skor Akhir</div>
                            <div class="text-3xl font-bold text-green-600">${saw_result.score}</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-sm text-neutral-600 mb-1">Status</div>
                            <div class="font-semibold text-purple-600">Ranked</div>
                        </div>
                    </div>

                    <!-- Calculation Details -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-2">Nilai Mentah (Raw Values)</h3>
                            <div class="space-y-1 text-sm">
                                ${Object.entries(saw_result.raw_values || {}).map(([key, val]) => `
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600">${key}:</span>
                                        <span class="font-medium text-neutral-900">${val}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-2">Nilai Normalisasi</h3>
                            <div class="space-y-1 text-sm">
                                ${Object.entries(saw_result.normalized_values || {}).map(([key, val]) => `
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600">${key}:</span>
                                        <span class="font-medium text-neutral-900">${(val).toFixed(4)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-2">Bobot yang Digunakan</h3>
                            <div class="space-y-1 text-sm">
                                ${Object.entries(saw_result.weights_used || {}).map(([key, val]) => `
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600">${key}:</span>
                                        <span class="font-medium text-neutral-900">${(val).toFixed(4)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderActivityLogs() {
        return `
            <div class="space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Log Aktivitas</h1>
                    <p class="text-sm text-neutral-600">Catatan semua aktivitas perankingan</p>
                </div>

                <div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
                    <div class="divide-y divide-neutral-200">
                        ${this.state.activityLogs.length > 0 ? this.state.activityLogs.map(log => `
                            <div class="p-4 hover:bg-neutral-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-medium text-neutral-900">${CrudManager.escapeHtml(log.action)}</div>
                                    <div class="text-xs text-neutral-500">${new Date(log.created_at).toLocaleString('id-ID')}</div>
                                </div>
                                <div class="text-sm text-neutral-600">${CrudManager.escapeHtml(log.description || '-')}</div>
                                <div class="text-xs text-neutral-500 mt-2">Oleh: ${CrudManager.escapeHtml(log.user?.name || 'System')}</div>
                            </div>
                        `).join('') : `
                            <div class="p-4 text-center text-neutral-600">
                                Belum ada aktivitas
                            </div>
                        `}
                    </div>
                </div>
            </div>
        `;
    }

    renderReporting() {
        if (!this.state.reportingSummary) {
            return `<div class="p-4 text-center text-neutral-600">Memuat laporan...</div>`;
        }

        const { summary, score_categories } = this.state.reportingSummary;

        return `
            <div class="space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Laporan Perankingan</h1>
                    <p class="text-sm text-neutral-600">Ringkasan dan analisis hasil ranking</p>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-2">Total Calon</div>
                        <div class="text-3xl font-bold text-neutral-900">${summary.total_candidates}</div>
                    </div>
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-2">Sudah Diranking</div>
                        <div class="text-3xl font-bold text-green-600">${summary.ranked_candidates}</div>
                    </div>
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-2">Belum Diranking</div>
                        <div class="text-3xl font-bold text-orange-600">${summary.pending_candidates}</div>
                    </div>
                    <div class="bg-white rounded-lg border border-neutral-200 p-4">
                        <div class="text-sm text-neutral-600 mb-2">Rata-rata Skor</div>
                        <div class="text-3xl font-bold text-blue-600">${summary.average_score}</div>
                    </div>
                </div>

                <!-- Score Categories -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Kategori Skor</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-neutral-900">Excellent (≥80)</span>
                                <span class="text-sm font-semibold text-green-600">${score_categories.excellent}</span>
                            </div>
                            <div class="w-full h-4 bg-neutral-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500" style="width: ${(score_categories.excellent / summary.ranked_candidates * 100)}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-neutral-900">Very Good (60-79)</span>
                                <span class="text-sm font-semibold text-blue-600">${score_categories.very_good}</span>
                            </div>
                            <div class="w-full h-4 bg-neutral-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500" style="width: ${(score_categories.very_good / summary.ranked_candidates * 100)}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-neutral-900">Good (40-59)</span>
                                <span class="text-sm font-semibold text-yellow-600">${score_categories.good}</span>
                            </div>
                            <div class="w-full h-4 bg-neutral-100 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500" style="width: ${(score_categories.good / summary.ranked_candidates * 100)}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-neutral-900">Fair (<40)</span>
                                <span class="text-sm font-semibold text-red-600">${score_categories.fair}</span>
                            </div>
                            <div class="w-full h-4 bg-neutral-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500" style="width: ${(score_categories.fair / summary.ranked_candidates * 100)}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <div class="flex gap-2">
                        <button id="btn-export-csv" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            📥 Export CSV
                        </button>
                        <button id="btn-export-pdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            📄 Export PDF
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    renderSawConfig() {
        return `
            <div class="space-y-4">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Konfigurasi SAW</h1>
                    <p class="text-sm text-neutral-600">Kelola bobot kriteria dan jalankan proses ranking</p>
                </div>

                <!-- Period Selection -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <label class="block text-sm font-medium text-neutral-900 mb-2">Pilih Periode Bansos</label>
                    <select id="period-select" class="w-full md:w-64 px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Periode --</option>
                        ${this.state.periods.map(p => `
                            <option value="${p.id}" ${this.state.selectedPeriod == p.id ? 'selected' : ''}>
                                ${CrudManager.escapeHtml(p.nama_periode)} (${p.tahun})
                            </option>
                        `).join('')}
                    </select>
                </div>

                ${this.state.selectedPeriod ? `
                    <!-- SAW Configuration -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Weights Configuration -->
                        <div class="bg-white rounded-lg border border-neutral-200 p-4">
                            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Konfigurasi Bobot Kriteria</h2>
                            <form id="weights-form" class="space-y-4">
                                ${Object.entries(this.state.weights).map(([key, value]) => `
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-900 mb-1">
                                            ${key}: ${CrudManager.escapeHtml(this.state.weights_description[key])}
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="${key}" 
                                                   min="0" max="1" step="0.01" 
                                                   value="${value.toFixed(2)}"
                                                   class="flex-1 px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                                            <span class="text-sm text-neutral-600 w-12">${(value * 100).toFixed(0)}%</span>
                                        </div>
                                    </div>
                                `).join('')}
                                
                                <div class="pt-2 border-t border-neutral-200">
                                    <p class="text-sm text-neutral-600 mb-2">Total Bobot: <span id="total-weight" class="font-semibold">100%</span></p>
                                    <div class="flex gap-2">
                                        <button type="button" id="btn-reset-weights" class="flex-1 px-3 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                                            Reset Default
                                        </button>
                                        <button type="button" id="btn-save-weights" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            Simpan Bobot
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Execute -->
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg border border-neutral-200 p-4">
                                <h3 class="font-semibold text-neutral-900 mb-3">Jalankan Proses Ranking</h3>
                                <p class="text-sm text-neutral-600 mb-4">
                                    Setelah bobot dikonfigurasi, klik tombol di bawah untuk menjalankan proses ranking berdasarkan kriteria SAW.
                                </p>
                                <button id="btn-execute" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                    ▶ Jalankan Ranking
                                </button>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    attachEventListeners() {
        // View navigation
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const view = e.target.closest('[data-view]').dataset.view;
                this.state.view = view;
                
                // Load data for the view
                if (view === 'ranking-list') {
                    await this.loadRankingList(1);
                } else if (view === 'activity-logs') {
                    await this.loadActivityLogs();
                } else if (view === 'reporting') {
                    await this.loadReportingSummary();
                }
                
                this.render();
            });
        });

        // Period selection
        document.getElementById('period-select')?.addEventListener('change', async (e) => {
            this.state.selectedPeriod = e.target.value;
            if (this.state.selectedPeriod) {
                await this.loadDashboard();
                await this.loadWeights();
            }
            this.render();
        });

        // Ranking list actions
        document.querySelectorAll('[data-action="view-saw"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const candidateId = e.target.closest('[data-action]').dataset.id;
                this.loadSawDetails(candidateId);
            });
        });

        // Pagination
        document.getElementById('btn-prev-page')?.addEventListener('click', () => {
            if (this.state.pagination.page > 1) {
                this.loadRankingList(this.state.pagination.page - 1);
            }
        });

        document.getElementById('btn-next-page')?.addEventListener('click', () => {
            if (this.state.pagination.page < this.state.pagination.lastPage) {
                this.loadRankingList(this.state.pagination.page + 1);
            }
        });

        // Weights
        const form = document.getElementById('weights-form');
        if (form) {
            form.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('change', () => {
                    this.calculateTotalWeight();
                });
            });
        }

        document.getElementById('btn-save-weights')?.addEventListener('click', () => {
            this.saveWeights();
        });

        document.getElementById('btn-reset-weights')?.addEventListener('click', () => {
            this.state.weights = {
                C1: 0.40,
                C2: 0.30,
                C3: 0.15,
                C4: 0.15,
            };
            this.render();
        });

        document.getElementById('btn-execute')?.addEventListener('click', () => {
            this.executeRanking();
        });

        // Export
        document.getElementById('btn-export-csv')?.addEventListener('click', () => {
            this.exportCSV();
        });

        document.getElementById('btn-export-pdf')?.addEventListener('click', () => {
            this.exportPDF();
        });
    }

    calculateTotalWeight() {
        const form = document.getElementById('weights-form');
        if (!form) return;

        let total = 0;
        const inputs = form.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalEl = document.getElementById('total-weight');
        if (totalEl) {
            const percentage = (total * 100).toFixed(0);
            totalEl.textContent = `${percentage}%`;
            totalEl.style.color = Math.abs(total - 1.0) < 0.01 ? 'green' : 'red';
        }
    }

    async saveWeights() {
        const form = document.getElementById('weights-form');
        if (!form) return;

        const weights = {};
        form.querySelectorAll('input[type="number"]').forEach(input => {
            weights[input.name] = parseFloat(input.value);
        });

        const total = Object.values(weights).reduce((a, b) => a + b, 0);
        if (Math.abs(total - 1.0) > 0.01) {
            this.showError('Total bobot harus sama dengan 1.0 (100%)');
            return;
        }

        try {
            const response = await fetch(
                `${this.state.apiBase}/ranking/weights`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        period_id: parseInt(this.state.selectedPeriod),
                        weights,
                    }),
                }
            );

            if (!response.ok) throw new Error('Gagal menyimpan bobot');

            this.state.weights = weights;
            this.showSuccess('Bobot berhasil disimpan');
        } catch (error) {
            this.showError(error.message);
        }
    }

    async executeRanking() {
        const confirmed = await CrudManager.showConfirm(
            'Jalankan Proses Ranking',
            'Apakah Anda yakin ingin menjalankan proses ranking SAW?',
            'Ya, Jalankan',
            'Batal'
        );

        if (!confirmed) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/ranking/execute`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        period_id: parseInt(this.state.selectedPeriod),
                    }),
                }
            );

            if (!response.ok) throw new Error('Gagal menjalankan ranking');

            this.showSuccess('Proses ranking berhasil dijalankan');
            await this.loadDashboard();
            await this.loadRankingList();
            this.state.view = 'ranking-list';
            this.render();
        } catch (error) {
            this.showError(error.message);
        }
    }

    exportCSV() {
        // CSV export implementation
        this.showSuccess('Exporting CSV...');
    }

    exportPDF() {
        // PDF export implementation
        this.showSuccess('Exporting PDF...');
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
            this.state.periods = result.data || [];
        } catch (error) {
            this.showError('Gagal memuat daftar periode');
        }
    }

    async loadWeights() {
        if (!this.state.selectedPeriod) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/ranking/weights?period_id=${this.state.selectedPeriod}`,
                {
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.ok) {
                const result = await response.json();
                const criteria = result.data?.criteria || [];
                const criteriaMap = criteria.reduce((acc, row) => {
                    acc[row.key] = parseFloat(row.weight || 0);
                    return acc;
                }, {});
                this.state.weights = {
                    C1: criteriaMap.C1 ?? 0.40,
                    C2: criteriaMap.C2 ?? 0.30,
                    C3: criteriaMap.C3 ?? 0.15,
                    C4: criteriaMap.C4 ?? 0.15,
                };
            }
        } catch (error) {
            console.error('Gagal memuat weights:', error);
        }
    }

    render() {
        const container = document.getElementById('content-region');
        if (!container) return;

        let html = `
            <div class="space-y-4">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Proses SAW (Simple Additive Weighting)</h1>
                    <p class="text-sm text-neutral-600">Kelola bobot kriteria dan jalankan proses ranking</p>
                </div>

                <!-- Period Selection -->
                <div class="bg-white rounded-lg border border-neutral-200 p-4">
                    <label class="block text-sm font-medium text-neutral-900 mb-2">Pilih Periode Bansos</label>
                    <select id="period-select" class="w-full md:w-64 px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Periode --</option>
                        ${this.state.periods.map(p => `
                            <option value="${p.id}" ${String(this.state.selectedPeriod) === String(p.id) ? 'selected' : ''}>
                                ${CrudManager.escapeHtml(p.nama_periode || p.name || p.periode_name)} 
                                (${CrudManager.escapeHtml(p.tahun || p.year || '-')})
                            </option>
                        `).join('')}
                    </select>
                </div>

                ${this.state.selectedPeriod ? `
                    <!-- SAW Configuration -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Weights Configuration -->
                        <div class="bg-white rounded-lg border border-neutral-200 p-4">
                            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Konfigurasi Bobot Kriteria</h2>
                            <form id="weights-form" class="space-y-4">
                                ${Object.entries(this.state.weights).map(([key, value]) => `
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-900 mb-1">
                                            ${key}: ${CrudManager.escapeHtml(this.state.weights_description[key])}
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="${key}" 
                                                   min="0" max="1" step="0.01" 
                                                   value="${value.toFixed(2)}"
                                                   class="flex-1 px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:border-blue-500">
                                            <span class="text-sm text-neutral-600 w-12">${(value * 100).toFixed(0)}%</span>
                                        </div>
                                    </div>
                                `).join('')}
                                
                                <div class="pt-2 border-t border-neutral-200">
                                    <p class="text-sm text-neutral-600 mb-2">Total Bobot: <span id="total-weight" class="font-semibold">100%</span></p>
                                    <div class="flex gap-2">
                                        <button type="button" id="btn-reset-weights" class="flex-1 px-3 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                                            Reset Default
                                        </button>
                                        <button type="button" id="btn-save-weights" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            Simpan Bobot
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Preview Info -->
                        <div class="space-y-4">
                            <!-- Statistics -->
                            <div class="bg-white rounded-lg border border-neutral-200 p-4">
                                <h3 class="font-semibold text-neutral-900 mb-3">Statistik</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600">Periode Aktif:</span>
                                        <span class="font-semibold text-neutral-900">
                                            ${this.state.periods.find(p => p.id == this.state.selectedPeriod)?.nama_periode || 'N/A'}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600">Total Calon:</span>
                                        <span class="font-semibold text-neutral-900">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600">Status Proses:</span>
                                        <span class="font-semibold text-neutral-900 text-blue-600">Siap Dijalankan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Execute -->
                            <div class="bg-white rounded-lg border border-neutral-200 p-4">
                                <h3 class="font-semibold text-neutral-900 mb-3">Jalankan Proses Ranking</h3>
                                <p class="text-sm text-neutral-600 mb-4">
                                    Setelah bobot dikonfigurasi, klik tombol di bawah untuk menjalankan proses ranking berdasarkan kriteria SAW.
                                </p>
                                <button id="btn-execute" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                    ▶ Jalankan Ranking
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results -->
                    ${this.state.finalResults ? this.renderResults() : ''}
                ` : `
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                        <p class="text-neutral-600">Silakan pilih periode bansos untuk memulai konfigurasi bobot kriteria</p>
                    </div>
                `}
            </div>
        `;

        container.innerHTML = html;
        this.attachEventListeners();
    }

    renderResults() {
        if (!this.state.finalResults || this.state.finalResults.length === 0) return '';

        return `
            <div class="bg-white rounded-lg border border-neutral-200 p-4">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Hasil Ranking</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 border-b border-neutral-200">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-neutral-900">Rank</th>
                                <th class="px-4 py-2 text-left font-semibold text-neutral-900">Nama</th>
                                <th class="px-4 py-2 text-left font-semibold text-neutral-900">NIK</th>
                                <th class="px-4 py-2 text-right font-semibold text-neutral-900">Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.state.finalResults.map((item, idx) => `
                                <tr class="border-b border-neutral-200 hover:bg-neutral-50">
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">
                                            ${idx + 1}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">${CrudManager.escapeHtml(item.nama_lengkap || item.name)}</td>
                                    <td class="px-4 py-2">${CrudManager.escapeHtml(item.nik)}</td>
                                    <td class="px-4 py-2 text-right font-semibold">${(item.skor || item.score || 0).toFixed(4)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex gap-2">
                    <button id="btn-download-results" class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition">
                        📥 Unduh Hasil
                    </button>
                    <button id="btn-send-results" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        ✉️ Kirim ke Paroki
                    </button>
                </div>
            </div>
        `;
    }

    attachEventListeners() {
        // Period selection
        document.getElementById('period-select')?.addEventListener('change', async (e) => {
            this.state.selectedPeriod = e.target.value;
            if (this.state.selectedPeriod) {
                await this.loadWeights();
            }
            this.render();
        });

        // Weights tracking
        const form = document.getElementById('weights-form');
        if (form) {
            form.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('change', () => {
                    this.calculateTotalWeight();
                });
            });
        }

        // Save weights
        document.getElementById('btn-save-weights')?.addEventListener('click', () => {
            this.saveWeights();
        });

        // Reset weights
        document.getElementById('btn-reset-weights')?.addEventListener('click', () => {
            this.state.weights = {
                C1: 0.40,
                C2: 0.30,
                C3: 0.15,
                C4: 0.15,
            };
            this.render();
        });

        // Execute ranking
        document.getElementById('btn-execute')?.addEventListener('click', () => {
            this.executeRanking();
        });

        // Results actions
        document.getElementById('btn-send-results')?.addEventListener('click', () => {
            this.sendToParoki();
        });

        document.getElementById('btn-download-results')?.addEventListener('click', () => {
            this.downloadResults();
        });
    }

    calculateTotalWeight() {
        const form = document.getElementById('weights-form');
        if (!form) return;

        let total = 0;
        const inputs = form.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalEl = document.getElementById('total-weight');
        if (totalEl) {
            const percentage = (total * 100).toFixed(0);
            totalEl.textContent = `${percentage}%`;
            totalEl.style.color = Math.abs(total - 1.0) < 0.01 ? 'green' : 'red';
        }
    }

    async saveWeights() {
        const form = document.getElementById('weights-form');
        if (!form) return;

        // Collect weights
        const weights = {};
        form.querySelectorAll('input[type="number"]').forEach(input => {
            weights[input.name] = parseFloat(input.value);
        });

        // Validate total = 1.0
        const total = Object.values(weights).reduce((a, b) => a + b, 0);
        if (Math.abs(total - 1.0) > 0.01) {
            this.showError('Total bobot harus sama dengan 1.0 (100%)');
            return;
        }

        try {
            const response = await fetch(
                `${this.state.apiBase}/ranking/weights`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        period_id: parseInt(this.state.selectedPeriod),
                        weights,
                    }),
                }
            );

            if (!response.ok) throw new Error('Gagal menyimpan bobot');

            this.state.weights = weights;
            this.showSuccess('Bobot berhasil disimpan');
        } catch (error) {
            this.showError(error.message);
        }
    }

    async executeRanking() {
        const confirmed = await CrudManager.showConfirm(
            'Jalankan Proses Ranking',
            'Apakah Anda yakin ingin menjalankan proses ranking SAW? Proses ini tidak bisa dibatalkan.',
            'Ya, Jalankan',
            'Batal'
        );

        if (!confirmed) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/ranking/execute`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        period_id: parseInt(this.state.selectedPeriod),
                    }),
                }
            );

            if (!response.ok) throw new Error('Gagal menjalankan ranking');

            const result = await response.json();
            this.state.finalResults = result.data?.rows || [];
            this.showSuccess('Proses ranking berhasil dijalankan');
            this.render();
        } catch (error) {
            this.showError(error.message);
        }
    }

    async sendToParoki() {
        const confirmed = await CrudManager.showConfirm(
            'Kirim ke Paroki',
            'Apakah Anda yakin ingin mengirim hasil ranking ini ke Paroki?',
            'Ya, Kirim',
            'Batal'
        );

        if (!confirmed) return;

        try {
            const response = await fetch(
                `${this.state.apiBase}/ranking/send-to-paroki/${this.state.selectedPeriod}`,
                {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.state.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                }
            );

            if (!response.ok) throw new Error('Gagal mengirim ke Paroki');

            this.showSuccess('Hasil ranking berhasil dikirim ke Paroki');
        } catch (error) {
            this.showError(error.message);
        }
    }

    downloadResults() {
        if (!this.state.finalResults || this.state.finalResults.length === 0) {
            this.showError('Tidak ada hasil untuk diunduh');
            return;
        }

        // Convert to CSV
        const headers = ['Rank', 'Nama', 'NIK', 'Skor'];
        const rows = this.state.finalResults.map((item, idx) => [
            idx + 1,
            item.nama_lengkap || item.name,
            item.nik,
            (item.skor || item.score || 0).toFixed(4),
        ]);

        const csv = [headers, ...rows].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `ranking-${this.state.selectedPeriod}-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
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

export default KetuaLingkunganParokiModule;
