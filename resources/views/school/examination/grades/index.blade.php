@extends('layouts.school')

@section('title', 'Grading Scale')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.examination.grades.fetch') }}',
        defaultSort: 'range_start',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), gradeManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'grade-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Configured Metrics" :value="$stats['total_grades']" icon="fas fa-graduation-cap" color="indigo" alpine-text="stats.total_grades" />
            <x-stat-card label="Evaluation Mode" value="Standard" icon="fas fa-check-circle" color="emerald" />
            <x-stat-card label="Status" value="Active" icon="fas fa-shield-alt" color="amber" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Grading Scale" description="Configure academic grading metrics, percentage ranges, and evaluation standards." icon="fas fa-graduation-cap">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Define Grade
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Performance Index</h2>
                        <x-table.search placeholder="Search grades..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="grade" label="Grade Symbol" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="range_start" label="Percentage Range" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100 font-black tracking-tighter">
                                            {{ $row['grade'] }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-600">
                                            {{ $row['range_start'] }}%
                                        </span>
                                        <div class="w-8 h-px bg-gray-200 dark:bg-gray-700"></div>
                                        <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 text-xs font-bold rounded-lg border border-indigo-100 dark:border-indigo-800/50">
                                            {{ $row['range_end'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-emerald-100">Mapped</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100 font-black tracking-tighter" x-text="row.grade"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-600" x-text="row.range_start + '%'"></span>
                                        <div class="w-8 h-px bg-gray-200 dark:bg-gray-700"></div>
                                        <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 text-xs font-bold rounded-lg border border-indigo-100 dark:border-indigo-800/50" x-text="row.range_end + '%'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-emerald-100">Mapped</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-graduation-cap" message="No grading scales found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="grade-modal" alpineTitle="editMode ? 'Modify Performance Metric' : 'Establish New Grade'" maxWidth="xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Grade Symbol <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="grade" x-model="formData.grade" @input="clearError('grade')"
                                placeholder="e.g., A+, Distinction, Merit"
                                class="modal-input-premium font-black uppercase pr-10"
                                :class="errors.grade ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-emerald-500 transition-colors">
                                <i class="fas fa-font text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.grade">
                            <p class="modal-error-message" x-text="errors.grade[0]"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Min Percentage (%) <span class="text-red-600 font-bold">*</span></label>
                            <div class="relative group">
                                <input type="number" name="range_start" x-model="formData.range_start" @input="clearError('range_start')"
                                    placeholder="0"
                                    class="modal-input-premium font-bold pr-10"
                                    :class="errors.range_start ? 'border-red-500' : 'border-slate-200'">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-emerald-500 transition-colors">
                                    <i class="fas fa-percentage text-xs"></i>
                                </div>
                            </div>
                            <template x-if="errors.range_start">
                                <p class="modal-error-message" x-text="errors.range_start[0]"></p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Max Percentage (%) <span class="text-red-600 font-bold">*</span></label>
                            <div class="relative group">
                                <input type="number" name="range_end" x-model="formData.range_end" @input="clearError('range_end')"
                                    placeholder="100"
                                    class="modal-input-premium font-bold pr-10"
                                    :class="errors.range_end ? 'border-red-500' : 'border-slate-200'">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-emerald-500 transition-colors">
                                    <i class="fas fa-percentage text-xs"></i>
                                </div>
                            </div>
                            <template x-if="errors.range_end">
                                <p class="modal-error-message" x-text="errors.range_end[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="bg-[#f8fafc] dark:bg-gray-700/50 p-4 rounded-xl border border-slate-200 dark:border-gray-600">
                        <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Administrative Guidance</p>
                        <p class="text-xs text-slate-600 dark:text-gray-400 leading-relaxed font-medium">Define percentage boundaries carefully. The system uses these ranges to automatically assign grades on report cards.</p>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'grade-modal')" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-violet-600 shadow-indigo-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Establish Grade'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function gradeManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    gradeId: null,
                    formData: {
                        grade: '',
                        range_start: '',
                        range_end: ''
                    },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.gradeId = null;
                        this.errors = {};
                        this.formData = { grade: '', range_start: '', range_end: '' };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'grade-modal');
                    },

                    openEditModal(row) {
                        this.editMode = true;
                        this.gradeId = row.id;
                        this.errors = {};
                        this.formData = {
                            grade: row.grade || '',
                            range_start: row.range_start || '',
                            range_end: row.range_end || ''
                        };
                        this.$dispatch('open-modal', 'grade-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/examination/grades/${this.gradeId}`
                            : '{{ route('school.examination.grades.store') }}';

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    ...this.formData,
                                    _method: this.editMode ? 'PUT' : 'POST'
                                })
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'grade-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Operation failed');
                            }
                        } catch (error) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(row) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Grading Metric',
                                message: `Are you sure you want to delete "${row.grade}"? This action cannot be undone.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/examination/grades/${row.id}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ _method: 'DELETE' })
                                        });

                                        const result = await response.json();

                                        if (response.ok) {
                                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'Deleted successfully' });
                                            if (typeof self.refreshTable === 'function') self.refreshTable();
                                        } else {
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Delete failed' });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Delete failed' });
                                    }
                                }
                            }
                        }));
                    },
                }
            }
        </script>
    @endpush
@endsection
