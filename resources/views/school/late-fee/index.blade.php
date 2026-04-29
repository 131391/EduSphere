@extends('layouts.school')

@section('title', 'Late Fee Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.late-fee.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), lateFeeManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'late-fee-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Late Fee Rules" :value="$stats['total']" icon="fas fa-clock" color="emerald" alpine-text="stats.total" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Late Fee Management" description="Configure automatic fines for late fee payments" icon="fas fa-clock">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Late Fee Rule
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Late Fee Rules</h2>
                        <x-table.search placeholder="Search rules..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="fine_date" label="Fine Date" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="late_fee_amount" label="Fine Amount" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Configured On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    {{-- Server-rendered rows --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-hourglass-end text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg text-gray-500">No late fee rules configured.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-sm">
                                            <span class="text-[10px] font-bold">{{ $row['fine_date'] }}</span>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">Day {{ $row['fine_date'] }} of Month</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1 rounded-lg inline-block border border-emerald-100 text-sm">
                                        <span class="text-xs mr-0.5">₹</span>{{ $row['late_fee_amount_formatted'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ $row['created_at'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Alpine-managed rows --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-sm">
                                            <span class="text-[10px] font-bold" x-text="row.fine_date"></span>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="'Day ' + row.fine_date + ' of Month'"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1 rounded-lg inline-block border border-emerald-100 text-sm">
                                        <span class="text-xs mr-0.5">₹</span><span x-text="row.late_fee_amount_formatted"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500" x-text="row.created_at"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-hourglass-end" message="No late fee rules configured." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Add/Edit Late Fee Modal -->
        <x-modal name="late-fee-modal" alpineTitle="editMode ? 'Edit Late Fee Rule' : 'Add Late Fee Rule'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Late Fine Amount <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="number" name="late_fee_amount" x-model="formData.late_fee_amount" @input="clearError('late_fee_amount')"
                                step="0.01" placeholder="0.00"
                                class="modal-input-premium font-bold pr-10"
                                :class="errors.late_fee_amount ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm transition-colors group-focus-within:text-emerald-500">₹</div>
                        </div>
                        <template x-if="errors.late_fee_amount">
                            <p class="modal-error-message" x-text="errors.late_fee_amount[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Select Late Fine Date <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="fine_date" x-model="formData.fine_date"
                                @change="clearError('fine_date')"
                                class="no-select2 modal-input-premium appearance-none pr-10"
                                :class="errors.fine_date ? 'border-red-500' : 'border-slate-200'">
                                <option value="">Select Day of Month</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}">Day {{ $i }}</option>
                                @endfor
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-calendar-day text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.fine_date">
                            <p class="modal-error-message" x-text="errors.fine_date[0]"></p>
                        </template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'late-fee-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-emerald-600 !to-teal-600 hover:!from-emerald-700 hover:!to-teal-700 shadow-emerald-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Rule' : 'Save Rule'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function lateFeeManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    feeId: null,
                    formData: { late_fee_amount: '', fine_date: '' },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.feeId = null;
                        this.errors = {};
                        this.formData = { late_fee_amount: '', fine_date: '' };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'late-fee-modal');
                    },

                    openEditModal(fee) {
                        this.editMode = true;
                        this.feeId = fee.id;
                        this.errors = {};
                        this.formData = {
                            late_fee_amount: fee.late_fee_amount,
                            fine_date: String(fee.fine_date)
                        };
                        this.$dispatch('open-modal', 'late-fee-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/late-fee/${this.feeId}`
                            : '{{ route('school.late-fee.store') }}';

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
                                this.$dispatch('close-modal', 'late-fee-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Something went wrong');
                            }
                        } catch (error) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(fee) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Late Fee Rule',
                                message: `Are you sure you want to delete the late fee rule for Day ${fee.fine_date}? This action cannot be undone.`,
                                confirmText: 'Delete',
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/late-fee/${fee.id}`, {
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
