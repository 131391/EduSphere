@extends('layouts.school')

@section('title', 'Fee Waivers')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.waivers.fetch') }}',
        defaultSort: 'id',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { student_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            student_id: { @foreach($students as $s) '{{ $s->id }}': '{{ $s->full_name }}', @endforeach }
        }
    }), waiverManagement())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Total Waivers" :value="$stats['total_waivers']" icon="fas fa-hand-holding-heart" color="emerald" alpine-text="stats.total_waivers" />
            <x-stat-card label="Institutional Grant" :value="'₹' . $stats['total_amount_waived']" icon="fas fa-gift" color="indigo" alpine-text="'₹' + stats.total_amount_waived" />
            <x-stat-card label="Beneficiary Count" :value="$stats['unique_students']" icon="fas fa-users" color="rose" alpine-text="stats.unique_students" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Fee Waivers" description="Manage institutional scholarships, financial concessions, and fee reduction programs for eligible students." icon="fas fa-hand-holding-heart">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Grant Waiver
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Concession Ledger</h2>
                        <x-table.search placeholder="Search by student name..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.student_id"
                            action="applyFilter('student_id', $event.target.value)"
                            placeholder="All Students"
                            :options="$students->mapWithKeys(fn($s) => [$s->id => $s->full_name . ' (' . $s->admission_no . ')'])->toArray()"
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
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student Details</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Concession</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Period / Session</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Justification</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 border border-emerald-100 dark:border-emerald-800 font-bold text-xs ring-2 ring-emerald-50 ring-offset-1">
                                            {{ substr($row['student_name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-tight">{{ $row['student_name'] }}</div>
                                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $row['admission_no'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="flex items-center">
                                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 tracking-tight">₹ {{ $row['waiver_amount'] }}</span>
                                            <span class="text-[9px] px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-md ml-2 font-bold">{{ $row['waiver_percentage'] }}</span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-medium italic mt-0.5">Off base fee of ₹{{ $row['actual_fee'] }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $row['fee_period'] }}</span>
                                        <span class="text-[10px] font-bold text-teal-600 uppercase tracking-widest mt-0.5 italic">Institutional Grant</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 italic max-w-xs truncate font-medium" title="{{ $row['reason'] }}">
                                        {{ $row['reason'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Waiver">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 border border-emerald-100 dark:border-emerald-800 font-bold text-xs ring-2 ring-emerald-50 ring-offset-1" x-text="row.student_name.charAt(0)"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-tight" x-text="row.student_name"></div>
                                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest" x-text="row.admission_no"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="flex items-center">
                                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 tracking-tight" x-text="'₹ ' + row.waiver_amount"></span>
                                            <span class="text-[9px] px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-md ml-2 font-bold" x-text="row.waiver_percentage"></span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-medium italic mt-0.5" x-text="'Off base fee of ₹' + row.actual_fee"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="row.fee_period"></span>
                                        <span class="text-[10px] font-bold text-teal-600 uppercase tracking-widest mt-0.5 italic">Institutional Grant</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 italic max-w-xs truncate font-medium" :title="row.reason" x-text="row.reason"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Waiver">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-hand-holding-heart" message="No active fee waivers found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Apply Waiver Modal -->
        <x-modal name="waiver-modal" alpineTitle="'Issue Student Fee Waiver'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <!-- Target Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Target Student <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.student_id" @change="clearError('student_id')"
                                class="modal-input-premium appearance-none pr-10"
                                :class="errors.student_id ? 'border-red-500' : 'border-slate-200'">
                                <option value="">Choose a student...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_no }})</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:rotate-180 transition-transform">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.student_id">
                            <p class="modal-error-message" x-text="errors.student_id[0]"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Academic Session</label>
                            <div class="relative group">
                                <select x-model="formData.academic_year_id" class="modal-input-premium appearance-none pr-10">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:rotate-180 transition-transform">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Billing Period</label>
                            <div class="relative group">
                                <input type="text" x-model="formData.fee_period" placeholder="e.g., Monthly" class="modal-input-premium pr-10 font-bold text-slate-800 dark:text-gray-100">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                    <i class="fas fa-clock text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Details -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-2xl space-y-6 shadow-inner">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Standard Fee Amount</label>
                                <div class="relative group">
                                    <input type="number" step="0.01" x-model.number="formData.actual_fee" @input="recalculate('actual')" class="modal-input-premium !bg-white dark:!bg-gray-700 !shadow-none pr-10 font-black text-slate-800 dark:text-gray-100">
                                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-xs font-mono">₹</div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Tenure (Months)</label>
                                <div class="relative group">
                                    <input type="number" x-model="formData.upto_months" class="modal-input-premium !bg-white dark:!bg-gray-700 !shadow-none font-bold text-slate-800 dark:text-gray-100">
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                <div class="w-full md:flex-1 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Reduction (Percentage)</label>
                                    <div class="relative group">
                                        <input type="number" step="0.01" x-model.number="formData.waiver_percentage" @input="recalculate('percent')" class="modal-input-premium !bg-white dark:!bg-gray-700 !shadow-none font-black text-indigo-600 dark:text-indigo-400 pr-8">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-xs">%</div>
                                    </div>
                                </div>
                                <div class="hidden md:block font-black text-slate-300 italic text-[10px] uppercase tracking-tighter">OR</div>
                                <div class="w-full md:flex-1 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Reduction (Fixed Amount)</label>
                                    <div class="relative group">
                                        <input type="number" step="0.01" x-model.number="formData.waiver_amount" @input="recalculate('amount')" class="modal-input-premium !bg-white dark:!bg-gray-700 !shadow-none pr-10 font-black text-emerald-600 dark:text-emerald-400">
                                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-300 font-bold text-xs font-mono">₹</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Justification -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Concession Justification <span class="text-red-600 font-bold">*</span></label>
                        <textarea x-model="formData.reason" @input="clearError('reason')" rows="3" placeholder="Define the eligibility criteria or scholarship details..." class="modal-input-premium border-slate-200 resize-none !h-auto" :class="errors.reason ? 'border-red-500' : ''"></textarea>
                        <template x-if="errors.reason">
                            <p class="modal-error-message" x-text="errors.reason[0]"></p>
                        </template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'waiver-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[180px] !from-emerald-600 !to-teal-600 shadow-emerald-200">
                        <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="submitting ? 'Processing...' : 'Issue Waiver'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function waiverManagement() {
                return {
                    submitting: false,
                    errors: {},
                    formData: {
                        student_id: '',
                        academic_year_id: '{{ $academicYear->id ?? '' }}',
                        fee_period: 'Monthly',
                        actual_fee: 0,
                        upto_months: 12,
                        waiver_percentage: 0,
                        waiver_amount: 0,
                        reason: ''
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    recalculate(source) {
                        const actual = parseFloat(this.formData.actual_fee) || 0;
                        if (source === 'percent' || source === 'actual') {
                            const percent = parseFloat(this.formData.waiver_percentage) || 0;
                            this.formData.waiver_amount = parseFloat((actual * percent / 100).toFixed(2));
                        } else if (source === 'amount') {
                            const amount = parseFloat(this.formData.waiver_amount) || 0;
                            this.formData.waiver_percentage = actual > 0 ? parseFloat((amount / actual * 100).toFixed(2)) : 0;
                        }
                    },

                    openAddModal() {
                        this.errors = {};
                        this.formData = {
                            student_id: '',
                            academic_year_id: '{{ $academicYear->id ?? '' }}',
                            fee_period: 'Monthly',
                            actual_fee: 0,
                            upto_months: 12,
                            waiver_percentage: 0,
                            waiver_amount: 0,
                            reason: ''
                        };
                        this.$dispatch('open-modal', 'waiver-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const response = await fetch('{{ route('school.waivers.store') }}', {
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
                                this.$dispatch('close-modal', 'waiver-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Failed to apply waiver');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    async confirmDelete(row) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Remove Fee Waiver',
                                message: `Are you sure you want to remove the waiver for "${row.student_name}"? This action cannot be undone and may restart full billing for the student.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/waivers/${row.id}`, {
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
