/**
 * ajaxDataTable — Reusable Alpine.js component for AJAX-driven data tables.
 *
 * All interactions (search, filter, sort, pagination, export) are handled
 * via AJAX POST requests. No query params are ever written to the URL.
 *
 * Usage in Blade:
 *   <div x-data="ajaxDataTable({
 *       fetchUrl: '{{ route("admin.schools.index") }}',
 *       defaultSort: 'id',
 *       defaultDirection: 'desc',
 *       defaultPerPage: 15,
 *       defaultFilters: { status: '', subscription_status: '' },
 *       filterLabels: { status: { active: 'Active', inactive: 'Inactive' } },
 *   })" x-init="fetchData()">
 *       ...
 *   </div>
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('ajaxDataTable', (config = {}) => ({
        // ─── Configuration ──────────────────────────────────────────────
        fetchUrl: config.fetchUrl || window.location.href,
        exportUrl: config.exportUrl || config.fetchUrl || window.location.href,

        // ─── State ──────────────────────────────────────────────────────
        rows: config.initialRows || [],
        initialRows: config.initialRows || [],
        loading: config.initialRows ? false : true,
        search: '',
        filters: config.defaultFilters ? { ...config.defaultFilters } : {},
        sort: config.defaultSort || 'id',
        direction: config.defaultDirection || 'desc',
        perPage: config.defaultPerPage || 15,
        page: config.initialPagination ? config.initialPagination.current_page : 1,
        pagination: config.initialPagination || {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: null,
            to: null,
        },
        stats: config.initialStats || {},
        filterLabels: config.filterLabels || {},
        exporting: false,
        hydrated: false,

        // Track whether we're still showing the initial server-rendered content.
        // If the server pre-loaded rows (even an empty array), we're already past
        // the "first load" phase — this avoids a race where the empty-state template
        // doesn't render on pages that hydrate with zero rows.
        initialLoad: !config.initialRows,

        // Debounce timer
        _searchTimer: null,
        // Minimum height to prevent layout shift during loading
        _minHeight: 0,
        // Delay before showing spinner (avoids flash on fast responses)
        _loadingTimer: null,
        showSpinner: false,

        // ─── Lifecycle ──────────────────────────────────────────────────
        init() {
            // Only fetch if no initial data was provided by the server
            if (!config.initialRows) {
                this._finishHydration();
                this.fetchData();
            } else {
                // To allow proper cascade and UI rendering when using pre-loaded data
                this.loading = false;
                this.initialLoad = false;
                this._finishHydration();
            }
        },

        _finishHydration() {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    this.hydrated = true;
                });
            });
        },

        // ─── Core Fetch ─────────────────────────────────────────────────
        async fetchData() {
            // Pin the current height so the wrapper doesn't collapse mid-fetch.
            // Skip this when the list is already empty — the CSS baseline on
            // .ajax-table-wrapper already reserves enough room for the empty
            // state + spinner, and pinning a shorter value here would cause a
            // visible jerk when the spinner overlay (min-h-[200px]) appears.
            const tableWrapper = this.$el?.querySelector('.ajax-table-wrapper');
            if (tableWrapper && this.rows.length > 0) {
                this._minHeight = tableWrapper.offsetHeight;
                tableWrapper.style.minHeight = this._minHeight + 'px';
            }

            this.loading = true;
            // Delay showing the spinner to avoid flash on fast responses
            clearTimeout(this._loadingTimer);
            this._loadingTimer = setTimeout(() => {
                if (this.loading) this.showSpinner = true;
            }, 150);

            try {
                const response = await axios.post(this.fetchUrl, this._buildPayload(), {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const result = response.data;
                this.rows = result.data || [];
                this.pagination = result.pagination || this.pagination;
                this.stats = result.stats || this.stats;
            } catch (error) {
                console.error('DataTable fetch error:', error);
                this.rows = [];
                if (window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Failed to load data. Please try again.', type: 'error' }
                    }));
                }
            } finally {
                this.loading = false;
                this.initialLoad = false;
                clearTimeout(this._loadingTimer);
                this.showSpinner = false;
                // Release the fixed height after a short delay to allow new content to render
                if (tableWrapper) {
                    requestAnimationFrame(() => {
                        tableWrapper.style.minHeight = '';
                    });
                }
            }
        },

        // ─── Search ─────────────────────────────────────────────────────
        handleSearch() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => {
                this.page = 1;
                this.fetchData();
            }, 500);
        },

        clearSearch() {
            this.search = '';
            this.page = 1;
            this.fetchData();
        },

        // ─── Sorting ────────────────────────────────────────────────────
        applySort(column) {
            if (this.sort === column) {
                this.direction = this.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort = column;
                this.direction = 'asc';
            }
            this.page = 1;
            this.fetchData();
        },

        getSortIcon(column) {
            if (this.sort !== column) return 'text-gray-300';
            return 'text-blue-600';
        },

        // ─── Filters ────────────────────────────────────────────────────
        applyFilter(key, value) {
            this.filters[key] = value;
            this.page = 1;
            this.fetchData();
        },

        removeFilter(key) {
            this.filters[key] = '';
            this.page = 1;
            this.fetchData();
        },

        clearAllFilters() {
            Object.keys(this.filters).forEach(key => {
                this.filters[key] = '';
            });
            this.search = '';
            this.page = 1;
            this.fetchData();
        },

        hasActiveFilters() {
            return Object.values(this.filters).some(v => v !== '' && v !== null && v !== undefined);
        },

        getFilterLabel(key, value) {
            const labels = this.filterLabels[key];
            const displayKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            if (labels && labels[value]) {
                return `${displayKey}: ${labels[value]}`;
            }
            return `${displayKey}: ${value}`;
        },

        // ─── Pagination ─────────────────────────────────────────────────
        changePage(newPage) {
            if (newPage < 1 || newPage > this.pagination.last_page) return;
            this.page = newPage;
            this.fetchData();
        },

        changePerPage(size) {
            this.perPage = parseInt(size);
            this.page = 1;
            this.fetchData();
        },

        get paginationPages() {
            const pages = [];
            const total = this.pagination.last_page;
            const current = this.pagination.current_page;

            if (total <= 7) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                pages.push(1);
                if (current > 3) pages.push('...');

                const start = Math.max(2, current - 1);
                const end = Math.min(total - 1, current + 1);
                for (let i = start; i <= end; i++) pages.push(i);

                if (current < total - 2) pages.push('...');
                pages.push(total);
            }
            return pages;
        },

        get showingText() {
            if (!this.pagination.from) return 'No results';
            return `Showing ${this.pagination.from} to ${this.pagination.to} of ${this.pagination.total} results`;
        },

        // ─── Export ──────────────────────────────────────────────────────
        async exportData(format = 'csv') {
            this.exporting = true;

            try {
                const payload = this._buildPayload();
                payload.export = format;

                const response = await axios.post(this.exportUrl, payload, {
                    headers: {
                        'Accept': 'text/csv',
                        'Content-Type': 'application/json',
                    },
                    responseType: 'blob',
                });

                // Extract filename from Content-Disposition header or use default
                const disposition = response.headers['content-disposition'];
                let filename = `export_${new Date().toISOString().slice(0,10)}.csv`;
                if (disposition) {
                    const match = disposition.match(/filename="?(.+?)"?$/);
                    if (match) filename = match[1];
                }

                // Trigger download
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: 'Export downloaded successfully.', type: 'success' }
                }));
            } catch (error) {
                console.error('Export error:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: 'Export failed. Please try again.', type: 'error' }
                }));
            } finally {
                this.exporting = false;
            }
        },

        // ─── Form Error Helpers ───────────────────────────────────────────
        // Replaces the errors object reference so Alpine detects the change
        // and x-if / x-show directives re-evaluate immediately.
        clearError(field) {
            if (this.errors && this.errors[field]) {
                const e = { ...this.errors };
                delete e[field];
                this.errors = e;
            }
        },

        // ─── Refresh (alias for external callers) ────────────────────────
        refreshTable() {
            return this.fetchData();
        },

        // ─── Quick Action (POST/DELETE + refresh) ─────────────────────────
        quickAction(url, label = 'Action', method = 'POST') {
            const self = this;
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: label,
                    message: `Are you sure you want to perform: ${label}? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const isDelete = method.toUpperCase() === 'DELETE';
                            const response = await axios({
                                method: isDelete ? 'delete' : method.toLowerCase(),
                                url: url,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                },
                            });

                            const result = response.data;
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'success', title: result.message || `${label} successful` });
                            }
                            self.fetchData();
                        } catch (error) {
                            const msg = error.response?.data?.message || `${label} failed. Please try again.`;
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: msg });
                            }
                            console.error('Quick action error:', error);
                        }
                    }
                }
            }));
        },

        // ─── Internal ────────────────────────────────────────────────────
        _buildPayload() {
            const payload = {
                search: this.search,
                sort: this.sort,
                direction: this.direction,
                per_page: this.perPage,
                page: this.page,
            };

            // Merge non-empty filters
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key] !== '' && this.filters[key] !== null && this.filters[key] !== undefined) {
                    payload[key] = this.filters[key];
                }
            });

            return payload;
        },
    }));
});
