@extends('layouts.school')

@section('title', 'Registration Fee Settings')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.settings.registration-fee.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach }
        }
    }), registrationFeeManager())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Total Categories" :value="$stats['unique_classes']" icon="fas fa-layer-group" color="emerald" alpine-text="stats.unique_classes" />
            <x-stat-card label="Average Reg. Rate" :value="'₹' . $stats['average_registration_fee']" icon="fas fa-calculator" color="indigo" alpine-text="'₹' + stats.average_registration_fee" />
            <x-stat-card label="Active Configs" :value="$stats['total_configurations']" icon="fas fa-check-double" color="rose" alpine-text="stats.total_configurations" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Registration Fee Settings" description="Configure mandatory registration charges applicable to new applicants during the initial admission process." icon="fas fa-user-plus">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Set Registration Rate
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Configuration Ledger</h2>
                        <x-table.search placeholder="Search by class name..." />
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
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Academic Class</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registration Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Last Modified</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 border border-indigo-100 dark:border-indigo-800">
                                            <i class="fas fa-graduation-cap text-[10px]"></i>
                                        </div>
                                        <span class="font-bold text-gray-700 dark:text-gray-200">{{ $row['class_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-900/40 px-3 py-1 rounded-lg inline-block border border-emerald-100 dark:border-emerald-800">
                                        <span class="text-xs mr-0.5 font-medium">₹</span>{{ $row['amount'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">{{ $row['created_at'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors shadow-sm">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 border border-indigo-100 dark:border-indigo-800">
                                            <i class="fas fa-graduation-cap text-[10px]"></i>
                                        </div>
                                        <span class="font-bold text-gray-700 dark:text-gray-200" x-text="row.class_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-900/40 px-3 py-1 rounded-lg inline-block border border-emerald-100 dark:border-emerald-800">
                                        <span class="text-xs mr-0.5 font-medium">₹</span><span x-text="row.amount"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm font-medium" x-text="row.created_at"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors shadow-sm">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-clipboard-check" message="No registration fees match your search." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="registration-fee-modal" alpineTitle="editMode ? 'Edit Registration Rate' : 'Define New Registration Rate'" maxWidth="md">
            <form @submit.prevent="submitForm()" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <!-- Class Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <template x-if="!editMode">
                                <select x-model="formData.class_id" @change="clearError('class_id')"
                                    class="no-select2 modal-input-premium appearance-none pr-10"
                                    :class="errors.class_id ? 'border-red-500' : 'border-slate-200'">
                                    <option value="">Select a Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </template>
                            
                            <template x-if="editMode">
                                <div class="modal-input-premium bg-gray-50 dark:bg-gray-800/50 border-slate-200 dark:border-gray-700 text-slate-500 dark:text-gray-400 font-bold flex items-center cursor-not-allowed pr-10">
                                    <span x-text="formData.class_name"></span>
                                </div>
                            </template>

                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:rotate-180 transition-transform">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.class_id">
                            <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                        </template>
                    </div>

                    <!-- Amount -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Registration Amount <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="number" step="0.01" x-model="formData.amount" @input="clearError('amount')"
                                placeholder="0.00"
                                class="modal-input-premium pr-10 font-bold text-slate-800 dark:text-gray-100"
                                :class="errors.amount ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm transition-colors group-focus-within:text-emerald-500">₹</div>
                        </div>
                        <template x-if="errors.amount">
                            <p class="modal-error-message" x-text="errors.amount[0]"></p>
                        </template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'registration-fee-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[160px] !from-teal-600 !to-emerald-600 shadow-teal-200">
                        <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="submitting ? 'Processing...' : (editMode ? 'Update Rate' : 'Define Rate')"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function registrationFeeManager() {
                return {
                    editMode: false,
                    feeId: null,
                    submitting: false,
                    errors: {},
                    formData: {
                        class_id: '',
                        class_name: '',
                        amount: ''
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    openAddModal() {
                        this.editMode = false;
                        this.feeId = null;
                        this.errors = {};
                        this.formData = { class_id: '', class_name: '', amount: '' };
                        this.$dispatch('open-modal', 'registration-fee-modal');
                    },

                    openEditModal(row) {
                        this.editMode = true;
                        this.feeId = row.id;
                        this.errors = {};
                        this.formData = {
                            class_id: row.class_id || '',
                            class_name: row.class_name,
                            amount: row.amount.replace(/,/g, '')
                        };
                        this.$dispatch('open-modal', 'registration-fee-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode 
                            ? `/school/settings/registration-fee/${this.feeId}`
                            : '{{ route('school.settings.registration-fee.store') }}';

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
                                this.$dispatch('close-modal', 'registration-fee-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Something went wrong');
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
                                title: 'Delete Registration Rate',
                                message: `Are you sure you want to delete the registration fee for "${row.class_name}"? This action cannot be undone.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/settings/registration-fee/${row.id}`, {
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
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Deletion failed' });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Deletion failed' });
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
