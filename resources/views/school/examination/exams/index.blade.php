@extends('layouts.school')

@section('title', 'Examination Schedule')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.examination.exams.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '', exam_type_id: '', status: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach },
            exam_type_id: { @foreach($examTypes as $t) '{{ $t->id }}': '{{ $t->name }}', @endforeach },
            status: { '1': 'Scheduled', '2': 'Ongoing', '3': 'Completed', '4': 'Cancelled' }
        }
    }), examManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'exam-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Sessions" :value="$stats['total']" icon="fas fa-calendar-alt" color="indigo" alpine-text="stats.total" />
            <x-stat-card label="Upcoming Exams" :value="$stats['scheduled']" icon="fas fa-clock" color="amber" alpine-text="stats.scheduled" />
            <x-stat-card label="Ongoing Tasks" :value="$stats['ongoing']" icon="fas fa-spinner" color="blue" alpine-text="stats.ongoing" />
            <x-stat-card label="Evaluated" :value="$stats['completed']" icon="fas fa-check-double" color="emerald" alpine-text="stats.completed" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Examination Schedule" description="Coordinate institutional assessments, academic years, and result tabulation sessions." icon="fas fa-calendar-check">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-calendar-plus mr-2 text-xs"></i>
                Schedule Exam
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Active Roster</h2>
                        <x-table.search placeholder="Search by month or type..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.status"
                            action="applyFilter('status', $event.target.value)"
                            placeholder="All Status"
                            :options="['1' => 'Scheduled', '2' => 'Ongoing', '3' => 'Completed', '4' => 'Cancelled']"
                        />
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
                            <x-table.sort-header column="created_at" label="Exam Particulars" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Target Audience</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <x-table.sort-header column="month" label="Assessment Window" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i class="fas fa-file-signature text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['exam_type'] }}</div>
                                            <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-tighter italic">Created: {{ $row['created_at'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">Class: {{ $row['class_name'] }}</span>
                                        <span class="text-[10px] text-gray-400 font-medium tracking-tight italic">{{ $row['academic_year'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-{{ $row['status_color'] }}-50 text-{{ $row['status_color'] }}-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-{{ $row['status_color'] }}-100">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-xs text-gray-600 dark:text-gray-400">
                                    {{ $row['month'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('school.examination.exams.tabulate', $row['id']) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors shadow-sm" title="Tabulation Sheet"><i class="fas fa-table text-xs"></i></a>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Schedule"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i class="fas fa-file-signature text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.exam_type"></div>
                                            <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-tighter italic" x-text="'Created: ' + row.created_at"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="'Class: ' + row.class_name"></span>
                                        <span class="text-[10px] text-gray-400 font-medium tracking-tight italic" x-text="row.academic_year"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase tracking-tight border"
                                        :class="{
                                            'bg-blue-50 text-blue-700 border-blue-100': row.status_color === 'blue',
                                            'bg-yellow-50 text-yellow-700 border-yellow-100': row.status_color === 'yellow',
                                            'bg-green-50 text-green-700 border-green-100': row.status_color === 'green',
                                            'bg-red-50 text-red-700 border-red-100': row.status_color === 'red'
                                        }"
                                        x-text="row.status">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-xs text-gray-600 dark:text-gray-400" x-text="row.month"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="'/school/examination/exams/' + row.id + '/tabulate'" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors shadow-sm" title="Tabulation Sheet"><i class="fas fa-table text-xs"></i></a>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Schedule"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-calendar-times" message="No exam schedules match your filters." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Schedule Exam Modal -->
        <x-modal name="exam-modal" alpineTitle="'Schedule Institutional Assessment'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Exam Category -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Exam Category <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.exam_type_id" @change="clearError('exam_type_id')"
                                class="modal-input-premium appearance-none pr-10"
                                :class="errors.exam_type_id ? 'border-red-500' : 'border-slate-200'">
                                <option value="">-- Select Type --</option>
                                @foreach($examTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300 font-bold">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.exam_type_id">
                            <p class="modal-error-message" x-text="errors.exam_type_id[0]"></p>
                        </template>
                    </div>

                    <!-- Target Class -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.class_id" @change="clearError('class_id')"
                                class="modal-input-premium appearance-none pr-10"
                                :class="errors.class_id ? 'border-red-500' : 'border-slate-200'">
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300 font-bold">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.class_id">
                            <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Month Selection -->
                <div class="space-y-2 mb-8">
                    <label class="modal-label-premium">Assessment Window (Month) <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="formData.month" @change="clearError('month')"
                            class="modal-input-premium appearance-none pr-10 shadow-sm"
                            :class="errors.month ? 'border-red-500' : 'border-slate-200'">
                            <option value="">-- Select Month --</option>
                            @foreach($months as $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.month">
                        <p class="modal-error-message" x-text="errors.month[0]"></p>
                    </template>
                </div>

                <!-- Guidance Notification -->
                <div class="mb-4 flex items-start gap-4 bg-indigo-50 dark:bg-gray-700/50 border border-indigo-100 dark:border-gray-600 p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white dark:bg-gray-800 rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 dark:text-gray-100 leading-tight">Timeline Locking Notification</span>
                        <p class="text-[10px] text-slate-500 dark:text-gray-400 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Scheduling creates a <span class="text-indigo-600 italic underline decoration-indigo-100">centralized session</span>. Individual subject marks can be entered in the assessment grid.
                        </p>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'exam-modal')" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-violet-600 shadow-indigo-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="submitting ? 'Allocating...' : 'Lock Schedule'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function examManagement() {
                return {
                    submitting: false,
                    errors: {},
                    formData: {
                        exam_type_id: '',
                        class_id: '',
                        month: ''
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    resetForm() {
                        this.errors = {};
                        this.formData = { exam_type_id: '', class_id: '', month: '' };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'exam-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const response = await fetch('{{ route('school.examination.exams.store') }}', {
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
                                this.$dispatch('close-modal', 'exam-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Scheduling failed');
                            }
                        } catch (error) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(exam) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Remove Schedule',
                                message: `Are you sure you want to remove the schedule for "${exam.exam_type} - ${exam.class_name}"? This action cannot be undone if marks are already recorded.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/examination/exams/${exam.id}`, {
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
