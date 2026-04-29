@extends('layouts.school')

@section('title', 'Miscellaneous Fees')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.miscellaneous-fees.fetch') }}',
        defaultSort: 'name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), miscFeeManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'misc-fee-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Fee Types" :value="$stats['total_fees']" icon="fas fa-coins" color="indigo" alpine-text="stats.total_fees" />
            <x-stat-card label="Total Potential Revenue" :value="$stats['total_amount_formatted']" icon="fas fa-money-bill-wave" color="emerald" alpine-text="stats.total_amount_formatted" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Miscellaneous Fees" description="Manage ad-hoc and one-time fees for various services" icon="fas fa-coins">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add New Fee
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Fees List</h2>
                        <x-table.search placeholder="Search fees..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="name" label="Fee Particulars" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="amount" label="Unit Amount" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Added On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-file-invoice-dollar text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</div>
                                            <div class="text-[10px] text-gray-400 font-medium line-clamp-1 max-w-[250px]">{{ $row['description'] ?? 'No description' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 font-black bg-emerald-50 px-3 py-1.5 rounded-xl inline-flex items-center gap-1 border border-emerald-100 shadow-sm">
                                        <span class="text-xs font-bold text-emerald-500">₹</span>
                                        <span class="tracking-tight">{{ $row['amount_formatted'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $row['created_at'] }}
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
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-file-invoice-dollar text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></div>
                                            <div class="text-[10px] text-gray-400 font-medium line-clamp-1 max-w-[250px]" x-text="row.description || 'No description'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 font-black bg-emerald-50 px-3 py-1.5 rounded-xl inline-flex items-center gap-1 border border-emerald-100 shadow-sm">
                                        <span class="text-xs font-bold text-emerald-500">₹</span>
                                        <span class="tracking-tight" x-text="row.amount_formatted"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.created_at"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-coins" message="No fees found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="misc-fee-modal" alpineTitle="editMode ? 'Edit Fee Details' : 'Add New Miscellaneous Fee'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <!-- Fee Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Fee Particulars <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                placeholder="e.g., ID Card Processing"
                                class="modal-input-premium pr-10"
                                :class="errors.name ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-teal-500 transition-colors">
                                <i class="fas fa-signature text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <!-- Amount -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Fee Amount <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="number" name="amount" step="0.01" x-model="formData.amount" @input="clearError('amount')"
                                placeholder="0.00"
                                class="modal-input-premium pr-10"
                                :class="errors.amount ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm transition-colors group-focus-within:text-emerald-500">₹</div>
                        </div>
                        <template x-if="errors.amount">
                            <p class="modal-error-message" x-text="errors.amount[0]"></p>
                        </template>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Description <span class="text-gray-400 text-xs">(Optional)</span></label>
                        <textarea name="description" x-model="formData.description" @input="clearError('description')"
                            placeholder="Optional explanation of what this fee covers..."
                            class="modal-input-premium px-4 py-3 resize-none h-24"
                            :class="errors.description ? 'border-red-500' : 'border-slate-200'"></textarea>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'misc-fee-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-teal-600 !to-emerald-600 hover:!from-teal-700 hover:!to-emerald-700 shadow-teal-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Save Fee'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function miscFeeManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    feeId: null,
                    formData: {
                        name: '',
                        amount: '',
                        description: ''
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
                        this.feeId = null;
                        this.errors = {};
                        this.formData = { 
                            name: '',
                            amount: '',
                            description: ''
                        };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'misc-fee-modal');
                    },

                    openEditModal(fee) {
                        this.editMode = true;
                        this.feeId = fee.id;
                        this.errors = {};
                        this.formData = { 
                            name: fee.name || '',
                            amount: fee.amount || '',
                            description: fee.description || ''
                        };
                        this.$dispatch('open-modal', 'misc-fee-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/miscellaneous-fees/${this.feeId}`
                            : '{{ route('school.miscellaneous-fees.store') }}';

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
                                this.$dispatch('close-modal', 'misc-fee-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, window.firstValidationMessage(this.errors)) });
                            } else {
                                throw new Error(window.resolveApiMessage(result, 'Something went wrong'));
                            }
                        } catch (error) {
                            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(error.response?.data || { message: error.message }, error.message || 'Something went wrong') });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(fee) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Fee Particular',
                                message: `Are you sure you want to delete and remove "${fee.name}"? This action cannot be undone.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/miscellaneous-fees/${fee.id}`, {
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
                                            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, 'Delete failed') });
                                        }
                                    } catch (error) {
                                        window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(error.response?.data || { message: error.message }, 'Delete failed') });
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
