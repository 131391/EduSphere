@extends('layouts.school')

@section('title', 'School Bank Accounts')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.school-banks.fetch') }}',
        defaultSort: 'name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), schoolBankManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'school-bank-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Bank Accounts" :value="$stats['total']" icon="fas fa-university" color="emerald" alpine-text="stats.total" />
        </div>

        <!-- Header Section -->
        <x-page-header title="School Bank Accounts" description="Manage official bank accounts for fee collection and payouts" icon="fas fa-university">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Bank Account
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Registered Bank Accounts</h2>
                        <x-table.search placeholder="Search accounts..." />
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
                            <x-table.sort-header column="name" label="Bank Name" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Added On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-university text-xs"></i>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</div>
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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-university text-xs"></i>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></div>
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

                        <x-table.empty-state :colspan="3" icon="fas fa-university" message="No bank accounts found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="school-bank-modal" alpineTitle="editMode ? 'Edit Bank Account' : 'Register New account'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Bank Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                placeholder="e.g., State Bank of India"
                                class="modal-input-premium pr-10"
                                :class="errors.name ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-emerald-500 transition-colors">
                                <i class="fas fa-university text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'school-bank-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-emerald-600 !to-teal-600 hover:!from-emerald-700 hover:!to-teal-700 shadow-emerald-100">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Register Account'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function schoolBankManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    bankId: null,
                    formData: {
                        name: ''
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
                        this.bankId = null;
                        this.errors = {};
                        this.formData = { 
                            name: ''
                        };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'school-bank-modal');
                    },

                    openEditModal(bank) {
                        this.editMode = true;
                        this.bankId = bank.id;
                        this.errors = {};
                        this.formData = { 
                            name: bank.name || ''
                        };
                        this.$dispatch('open-modal', 'school-bank-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/school-banks/${this.bankId}`
                            : '{{ route('school.school-banks.store') }}';

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
                                this.$dispatch('close-modal', 'school-bank-modal');
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

                    confirmDelete(bank) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Bank Account',
                                message: `Are you sure you want to delete the bank account "${bank.name}"? This action cannot be undone.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/school-banks/${bank.id}`, {
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
