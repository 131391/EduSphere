@extends('layouts.school')

@section('title', 'Fee Master')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.fee-master.fetch') }}',
        defaultSort: 'id',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '', fee_type_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach },
            fee_type_id: { @foreach($feeTypes as $t) '{{ $t->id }}': '{{ $t->name }}', @endforeach }
        }
    }), feeMasterManagement())" class="space-y-6" @close-modal.window="if ($event.detail.startsWith('fee-master')) { resetForms(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Fee Configurations" :value="$stats['total_configurations']" icon="fas fa-university" color="indigo" alpine-text="stats.total_configurations" />
            <x-stat-card label="Active Fee Types" :value="$stats['fee_types_count']" icon="fas fa-layer-group" color="emerald" alpine-text="stats.fee_types_count" />
            <x-stat-card label="Classes Configured" :value="$stats['classes_mapped']" icon="fas fa-school" color="amber" alpine-text="stats.classes_mapped" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Fee Master" description="Define institutional fee structures, seasonal installments, and academic billing components." icon="fas fa-university">
            <div class="flex items-center gap-3">
                <button @click="openBulkModal()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-cyan-600 to-indigo-600 hover:from-cyan-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-layer-group mr-2 text-xs"></i>
                    Bulk Assignment
                </button>
                <button @click="openMiscModal()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i>
                    Single Entry
                </button>
            </div>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Rate Inventory</h2>
                        <x-table.search placeholder="Search by fee head..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.fee_type_id"
                            action="applyFilter('fee_type_id', $event.target.value)"
                            placeholder="Installment"
                            :options="$feeTypes->pluck('name', 'id')->toArray()"
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
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Class / Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fee Label</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Installment</th>
                            <x-table.sort-header column="amount" label="Standard Rate" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 border border-emerald-100 dark:border-emerald-800 shadow-sm">
                                            <i class="fas fa-school text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['class_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300">
                                    {{ $row['fee_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-bold rounded-lg uppercase tracking-tight border border-gray-200 dark:border-gray-600">
                                        {{ $row['fee_type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-900/40 px-3 py-1 rounded-lg inline-block border border-emerald-100 dark:border-emerald-800">
                                        <span class="text-xs mr-0.5 font-medium">₹</span>{{ $row['amount'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
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
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 border border-emerald-100 dark:border-emerald-800 shadow-sm">
                                            <i class="fas fa-school text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.class_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300" x-text="row.fee_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-bold rounded-lg uppercase tracking-tight border border-gray-200 dark:border-gray-600" x-text="row.fee_type"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-900/40 px-3 py-1 rounded-lg inline-block border border-emerald-100 dark:border-emerald-800" x-text="'₹' + row.amount"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-red-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-university" message="No fee configurations match your filters." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Bulk Assignment Modal -->
        <x-modal name="bulk-fee-master-modal" alpineTitle="'Bulk Fee Master Assignment'" maxWidth="2xl">
            <div class="space-y-5">

                {{-- Section 1: Class & Installment --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="modal-label-premium">Class <span class="text-red-500">*</span></label>
                        <select x-model="bulkData.class_id"
                                @change="clearError('class_id')"
                                class="no-select2 modal-input-premium"
                                :class="errors.class_id ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select a class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.class_id">
                            <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="modal-label-premium">Installment Type <span class="text-red-500">*</span></label>
                        <select x-model="bulkData.fee_type_id"
                                @change="clearError('fee_type_id')"
                                class="no-select2 modal-input-premium"
                                :class="errors.fee_type_id ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select installment type</option>
                            @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.fee_type_id">
                            <p class="modal-error-message" x-text="errors.fee_type_id[0]"></p>
                        </template>
                    </div>
                </div>

                {{-- Section 2: Fee Components --}}
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="grid grid-cols-[1fr_160px] bg-gray-50 border-b border-gray-200 px-4 py-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fee Component</span>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide text-right pr-1">Amount (₹)</span>
                    </div>

                    <div class="max-h-[300px] overflow-y-auto divide-y divide-gray-100 custom-scrollbar">
                        @foreach($feeNames as $feeName)
                        <div class="grid grid-cols-[1fr_160px] items-center px-4 py-2.5 hover:bg-indigo-50/40 transition-colors"
                             :class="bulkData.amounts[{{ $feeName->id }}] > 0 ? 'bg-emerald-50/30' : ''">
                            <span class="text-sm font-medium text-gray-700">{{ $feeName->name }}</span>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-medium pointer-events-none">₹</span>
                                <input
                                    type="number"
                                    x-model="bulkData.amounts[{{ $feeName->id }}]"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full pl-7 pr-2 py-1.5 text-sm font-semibold text-right bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition"
                                    :class="bulkData.amounts[{{ $feeName->id }}] > 0 ? 'border-emerald-300 text-emerald-700' : 'text-gray-700'"
                                >
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-[1fr_160px] items-center px-4 py-2.5 bg-indigo-50 border-t border-indigo-100">
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Total</span>
                        <span class="text-sm font-bold text-indigo-700 text-right pr-1"
                              x-text="'₹ ' + Object.values(bulkData.amounts).reduce((s, v) => s + (parseFloat(v) || 0), 0).toFixed(2)">
                        </span>
                    </div>
                </div>

                <p class="text-xs text-gray-400 flex items-center gap-1.5">
                    <i class="fas fa-info-circle text-indigo-400"></i>
                    Leave a field at 0.00 to skip that component. Only non-zero amounts will be saved.
                </p>

            </div>

            <x-slot name="footer">
                <button type="button"
                        @click="$dispatch('close-modal', 'bulk-fee-master-modal')"
                        class="btn-premium-cancel px-8">
                    Cancel
                </button>
                <button type="button"
                        @click="submitBulkForm()"
                        :disabled="submitting || !bulkData.class_id || !bulkData.fee_type_id"
                        class="btn-premium-primary min-w-[180px] !from-indigo-600 !to-violet-600 shadow-indigo-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Saving...' : 'Assign Class Fees'"></span>
                </button>
            </x-slot>
        </x-modal>

        <!-- Single Edit Modal -->
        <x-modal name="edit-fee-master-modal" alpineTitle="'Modify Fee Rate'" maxWidth="xl">
            <form @submit.prevent="submitEditForm" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <div class="p-5 bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 rounded-2xl space-y-1.5 shadow-sm">
                        <div class="flex justify-between text-[10px] font-black text-indigo-400 uppercase tracking-widest">
                            <span x-text="editData.class_name"></span>
                            <span x-text="editData.fee_type"></span>
                        </div>
                        <div class="text-base font-black text-indigo-900 dark:text-indigo-400" x-text="editData.fee_name"></div>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Updated Fee Amount <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative">
                            <input
                                type="number"
                                x-model="editData.amount"
                                step="0.01"
                                min="0"
                                class="modal-input-premium !pr-10 font-black text-slate-800 dark:text-gray-100"
                                :class="errors.amount ? 'border-red-500' : 'border-slate-200'"
                            >
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm">₹</div>
                        </div>
                        <template x-if="errors.amount">
                            <p class="modal-error-message" x-text="errors.amount[0]"></p>
                        </template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'edit-fee-master-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[180px] !from-indigo-600 !to-violet-600 shadow-indigo-200">
                        <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="submitting ? 'Updating...' : 'Save Rate Change'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <!-- Single Entry Modal -->
        <x-modal name="misc-fee-master-modal" alpineTitle="'Single Fee Entry'" maxWidth="2xl">
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="modal-label-premium">Class <span class="text-red-500">*</span></label>
                        <select x-model="miscData.class_id"
                                @change="miscClassError = null"
                                class="no-select2 modal-input-premium"
                                :class="miscClassError ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select a class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="miscClassError">
                            <p class="modal-error-message" x-text="miscClassError"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="modal-label-premium">Fee Component <span class="text-red-500">*</span></label>
                        <select x-model="miscData.fee_name_id"
                                @change="miscFeeNameError = null"
                                class="no-select2 modal-input-premium"
                                :class="miscFeeNameError ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select fee component</option>
                            @foreach($feeNames as $fn)
                                <option value="{{ $fn->id }}">{{ $fn->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="miscFeeNameError">
                            <p class="modal-error-message" x-text="miscFeeNameError"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="modal-label-premium">Installment Type <span class="text-red-500">*</span></label>
                        <select x-model="miscData.fee_type_id"
                                @change="miscFeeTypeError = null"
                                class="no-select2 modal-input-premium"
                                :class="miscFeeTypeError ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select installment type</option>
                            @foreach($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="miscFeeTypeError">
                            <p class="modal-error-message" x-text="miscFeeTypeError"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="modal-label-premium">Amount (₹) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none">₹</span>
                            <input type="number"
                                   x-model="miscData.amount"
                                   @input="miscAmountError = null"
                                   placeholder="0.00"
                                   step="0.01" min="0"
                                   class="modal-input-premium !pl-10 font-semibold text-gray-800"
                                   :class="miscAmountError ? 'border-red-500' : 'border-slate-200'">
                        </div>
                        <template x-if="miscAmountError">
                            <p class="modal-error-message" x-text="miscAmountError"></p>
                        </template>
                    </div>
                </div>

                <div class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
                    <i class="fas fa-info-circle mt-0.5 flex-shrink-0 text-blue-400"></i>
                    <span class="text-xs text-blue-700">This sets the standard rate for the selected class and component. It will be used during automated fee generation.</span>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button"
                        @click="$dispatch('close-modal', 'misc-fee-master-modal')"
                        class="btn-premium-cancel px-8">
                    Cancel
                </button>
                <button type="button"
                        @click="submitMiscForm()"
                        :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-emerald-600 !to-teal-600 shadow-emerald-200">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Saving...' : 'Save Configuration'"></span>
                </button>
            </x-slot>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function feeMasterManagement() {
                return {
                    submitting: false,
                    errors: {},
                    // Separate error state for misc modal fields
                    miscClassError: null,
                    miscFeeTypeError: null,
                    miscFeeNameError: null,
                    miscAmountError: null,
                    bulkData: { class_id: '', fee_type_id: '', amounts: {} },
                    miscData: { class_id: '', fee_type_id: '', fee_name_id: '', amount: '' },
                    editData: { id: '', class_name: '', fee_name: '', fee_type: '', amount: '' },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    resetForms() {
                        this.errors = {};
                        this.miscClassError = null;
                        this.miscFeeTypeError = null;
                        this.miscFeeNameError = null;
                        this.miscAmountError = null;
                        this.bulkData = { class_id: '', fee_type_id: '', amounts: {} };
                        this.miscData = { class_id: '', fee_type_id: '', fee_name_id: '', amount: '' };
                    },

                    openBulkModal() {
                        this.resetForms();
                        this.$dispatch('open-modal', 'bulk-fee-master-modal');
                    },

                    openMiscModal() {
                        this.resetForms();
                        this.$dispatch('open-modal', 'misc-fee-master-modal');
                    },

                    openEditModal(row) {
                        this.errors = {};
                        this.editData = {
                            id: row.id,
                            class_name: row.class_name,
                            fee_name: row.fee_name,
                            fee_type: row.fee_type,
                            amount: row.amount.replace(/,/g, '')
                        };
                        this.$dispatch('open-modal', 'edit-fee-master-modal');
                    },

                    async submitBulkForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const response = await fetch("{{ route('school.fee-master.store') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify(this.bulkData)
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'bulk-fee-master-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                                if (Object.keys(this.errors).length === 0 && window.Toast) {
                                    window.Toast.fire({ icon: 'error', title: result.message || 'Please check your inputs.' });
                                }
                            } else {
                                throw new Error(result.message || 'Bulk assignment failed');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    async submitEditForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const response = await fetch(`/school/fee-master/${this.editData.id}`, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({ amount: this.editData.amount, _method: "PUT" })
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'edit-fee-master-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Update failed');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    async submitMiscForm() {
                        if (this.submitting) return;

                        // Client-side validation for all fields
                        this.miscClassError = null;
                        this.miscFeeTypeError = null;
                        this.miscFeeNameError = null;
                        this.miscAmountError = null;
                        let hasClientError = false;

                        if (!this.miscData.class_id) {
                            this.miscClassError = 'Please select a class.';
                            hasClientError = true;
                        }
                        if (!this.miscData.fee_type_id) {
                            this.miscFeeTypeError = 'Please select an installment type.';
                            hasClientError = true;
                        }
                        if (!this.miscData.fee_name_id) {
                            this.miscFeeNameError = 'Please select a fee component.';
                            hasClientError = true;
                        }
                        if (!this.miscData.amount && this.miscData.amount !== 0) {
                            this.miscAmountError = 'Please enter an amount.';
                            hasClientError = true;
                        }
                        if (hasClientError) return;

                        this.submitting = true;
                        this.errors = {};

                        try {
                            const payload = {
                                class_id: this.miscData.class_id,
                                fee_type_id: this.miscData.fee_type_id,
                                amounts: { [this.miscData.fee_name_id]: this.miscData.amount }
                            };

                            const response = await fetch("{{ route('school.fee-master.store') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify(payload)
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'misc-fee-master-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                                if (Object.keys(this.errors).length === 0 && window.Toast) {
                                    window.Toast.fire({ icon: 'error', title: result.message || 'Please check your inputs.' });
                                }
                            } else {
                                throw new Error(result.message || 'Single entry failed');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(row) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Fee Configuration',
                                message: `Are you sure you want to delete the configuration for "${row.fee_name}"? This action cannot be undone and may affect billing reports.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/fee-master/${row.id}`, {
                                            method: "POST",
                                            headers: {
                                                "Content-Type": "application/json",
                                                "Accept": "application/json",
                                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                            },
                                            body: JSON.stringify({ _method: "DELETE" })
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
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        </style>
    @endpush
@endsection
