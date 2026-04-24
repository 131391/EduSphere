@extends('layouts.school')

@section('title', 'Examination Subjects')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.examination.subjects.fetch') }}',
        defaultSort: 'class_name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach }
        }
    }), subjectManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'exam-subject-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Total Assignments" :value="$stats['total_assignments']" icon="fas fa-book-reader" color="indigo" alpine-text="stats.total_assignments" />
            <x-stat-card label="Classes Covered" :value="$stats['classes_covered']" icon="fas fa-chalkboard" color="emerald" alpine-text="stats.classes_covered" />
            <x-stat-card label="Academic Cycle" value="2026-2027" icon="fas fa-calendar-alt" color="amber" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Subject Assignment" description="Assign subjects to classes and define institutional assessment parameters." icon="fas fa-book-reader">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Assign New Subject
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Active Assignments</h2>
                        <x-table.search placeholder="Search by class or subject..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="All Classes"
                            :options="$classes->pluck('name', 'id')->toArray()"
                        />
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filter Tags -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <template x-if="value">
                            <div class="flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                <span x-text="key.replace('_', ' ') + ': ' + getFilterLabel(key, value)"></span>
                                <button @click="removeFilter(key)" class="ml-1 hover:text-indigo-900 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </template>
                    <button @click="clearAllFilters()" class="text-[10px] font-bold text-red-600 hover:text-red-700 uppercase tracking-widest ml-2 transition-colors">
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="class_name" label="Target Class" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="subject_name" label="Assigned Subject" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="full_marks" label="Full Marks" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-gray-700 flex items-center justify-center text-slate-400">
                                            <i class="fas fa-chalkboard text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['class_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-400 flex items-center justify-center">
                                            <i class="fas fa-book text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $row['subject_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-black border border-emerald-100 shadow-sm">
                                        {{ $row['full_marks'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Assignment"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-gray-700 flex items-center justify-center text-slate-400">
                                            <i class="fas fa-chalkboard text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.class_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-400 flex items-center justify-center">
                                            <i class="fas fa-book text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400" x-text="row.subject_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-black border border-emerald-100 shadow-sm" x-text="row.full_marks"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Assignment"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-book-open" message="No subject assignments found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add Subject Modal -->
        <x-modal name="exam-subject-modal" alpineTitle="'Assign New Subject Mapping'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Target Class -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.class_id" @change="clearError('class_id')"
                                class="modal-input-premium appearance-none pr-10"
                                :class="errors.class_id ? 'border-red-500' : 'border-slate-200'">
                                <option value="">Choose Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.class_id">
                            <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                        </template>
                    </div>

                    <!-- Subject Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Subject Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.subject_id" @change="clearError('subject_id')"
                                class="modal-input-premium appearance-none pr-10"
                                :class="errors.subject_id ? 'border-red-500' : 'border-slate-200'">
                                <option value="">Choose Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.subject_id">
                            <p class="modal-error-message" x-text="errors.subject_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Full Marks -->
                <div class="space-y-2 mb-8">
                    <label class="modal-label-premium">Assessment Full Marks <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="number" name="full_marks" x-model="formData.full_marks" @input="clearError('full_marks')"
                            placeholder="e.g., 100, 50, 25"
                            class="modal-input-premium font-bold pr-10"
                            :class="errors.full_marks ? 'border-red-500' : 'border-slate-200'">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-indigo-500 pointer-events-none">
                            <i class="fas fa-star text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.full_marks">
                        <p class="modal-error-message" x-text="errors.full_marks[0]"></p>
                    </template>
                </div>

                <!-- Infographic Block -->
                <div class="mb-4 flex items-start gap-4 bg-indigo-50 dark:bg-gray-700/50 border border-indigo-100 dark:border-gray-600 p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white dark:bg-gray-800 rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-lightbulb text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 dark:text-gray-100 leading-tight">Configuration Scope</span>
                        <p class="text-[10px] text-slate-500 dark:text-gray-400 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Mapping subjects to classes allows teachers to enter assessment marks. Parameters can be adjusted in the assessments portal later.
                        </p>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'exam-subject-modal')" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting || {{ count($subjects) == 0 ? 'true' : 'false' }}"
                        class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-violet-600 shadow-indigo-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="submitting ? 'Allocating...' : 'Assign Subject'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function subjectManagement() {
                return {
                    submitting: false,
                    errors: {},
                    formData: {
                        class_id: '',
                        subject_id: '',
                        full_marks: 100
                    },

                    clearError(field) {
                        if (this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    resetForm() {
                        this.errors = {};
                        this.formData = { class_id: '', subject_id: '', full_marks: 100 };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'exam-subject-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const response = await fetch('{{ route('school.examination.subjects.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.formData)
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'exam-subject-modal');
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
                                title: 'Remove Assignment',
                                message: `Are you sure you want to remove "${row.subject_name}" assignment from "${row.class_name}"? This action cannot be undone if marks are already recorded.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/examination/subjects/${row.id}`, {
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
                                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'Removed successfully' });
                                            if (typeof self.refreshTable === 'function') self.refreshTable();
                                        } else {
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Removal failed' });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Removal failed' });
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
