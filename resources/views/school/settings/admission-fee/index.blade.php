@extends('layouts.school')

@section('title', 'Admission Fee — Settings')

@section('content')
<div x-data="Object.assign(ajaxDataTable({
    fetchUrl: '{{ route('school.settings.admission-fee.fetch') }}',
    defaultSort: 'created_at',
    defaultDirection: 'desc',
    defaultPerPage: 25,
    defaultFilters: { class_id: '' },
    initialRows: @js($initialData['rows']),
    initialPagination: @js($initialData['pagination']),
    initialStats: @js($initialData['stats']),
    filterLabels: { class_id: { @foreach($classes as $c)'{{ $c->id }}': '{{ addslashes($c->name) }}',@endforeach } }
}), feeManager())" class="space-y-6" @close-modal.window="if ($event.detail === 'adm-fee-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Classes Configured" :value="$stats['unique_classes']" icon="fas fa-chalkboard" color="indigo" alpine-text="stats.unique_classes" />
            <x-stat-card label="Average Fee" :value="'₹' . $stats['average_admission_fee']" icon="fas fa-rupee-sign" color="emerald" alpine-text="'₹' + (stats.average_admission_fee || '0.00')" />
            <x-stat-card label="Total Records" :value="$stats['total_configurations']" icon="fas fa-list-check" color="rose" alpine-text="stats.total_configurations" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Admission Fee" description="Set the one-time fee charged when a student is formally admitted, per class." icon="fas fa-door-open text-indigo-600">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Fee
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Fee Configuration List</h2>
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
            </div>

            {{-- Active filters --}}
            <div class="px-6 pb-3 pt-3 flex flex-wrap gap-2 bg-gray-50 dark:bg-gray-800/50" x-show="hasActiveFilters()" x-cloak>
                <template x-for="(value, key) in filters" :key="key">
                    <template x-if="value">
                        <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full text-xs font-semibold">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-0.5 hover:text-indigo-900"><i class="fas fa-times text-[10px]"></i></button>
                        </span>
                    </template>
                </template>
                <button @click="clearAllFilters()" class="text-xs font-semibold text-red-500 hover:text-red-700 ml-1">Clear all</button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse table-fixed">
                    <colgroup>
                        <col class="w-[34%]">
                        <col class="w-[34%]">
                        <col class="w-[16%]">
                        <col class="w-32">
                    </colgroup>
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="class_name" label="Class" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="amount" label="Admission Fee" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Set On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-graduation-cap text-xs"></i>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['class_name'] }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">
                                        ₹{{ number_format($row['amount'], 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs">
                                        <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                        {{ $row['created_at'] }}
                                    </div>
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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-graduation-cap text-xs"></i>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.class_name"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">
                                        ₹<span x-text="Number(row.amount).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs">
                                        <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                        <span x-text="row.created_at"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-door-open" message="No admission fees configured yet. Click 'Add Fee' to get started." />
                    </tbody>
                </table>
            </div>
        <x-table.pagination :initial="$initialData['pagination']" />
    </div>

    {{-- ── Modal ── --}}
    <x-modal name="adm-fee-modal" alpineTitle="editMode ? 'Edit Admission Fee' : 'Add Admission Fee'" maxWidth="md">
        <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6">
                {{-- Class --}}
                <div class="space-y-2">
                    <label class="modal-label-premium">Class <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-show="!editMode" name="class_id" x-model="formData.class_id" @change="clearError('class_id')"
                            class="modal-input-premium no-select2"
                            :class="errors.class_id ? 'border-red-500' : 'border-slate-200'">
                            <option value="">— Select a class —</option>
                            @foreach($unassignedClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div x-show="editMode"
                            class="modal-input-premium bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed flex items-center gap-2">
                            <i class="fas fa-lock text-xs text-gray-400"></i>
                            <span x-text="formData.class_name"></span>
                        </div>
                    </div>
                    <template x-if="errors.class_id">
                        <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                    </template>
                </div>

                {{-- Amount --}}
                <div class="space-y-2">
                    <label class="modal-label-premium">Fee Amount <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-indigo-500 transition-colors z-10 flex items-center justify-center">
                            <i class="fas fa-rupee-sign text-sm"></i>
                        </div>
                        <input type="number" step="0.01" min="0" name="amount" x-model="formData.amount" @input="clearError('amount')"
                            placeholder="0.00"
                            class="modal-input-premium pl-10"
                            :class="errors.amount ? 'border-red-500' : 'border-slate-200'">
                    </div>
                    <template x-if="errors.amount">
                        <p class="modal-error-message" x-text="errors.amount[0]"></p>
                    </template>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'adm-fee-modal')" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting"
                    class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-blue-600 hover:!from-indigo-700 hover:!to-blue-700 shadow-indigo-200">
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
function feeManager() {
    return {
        editMode: false,
        feeId:    null,
        submitting: false,
        errors:   {},
        formData: { class_id: '', class_name: '', amount: '' },

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
            this.formData = { class_id: '', class_name: '', amount: '' };
        },

        openAddModal() {
            this.resetForm();
            this.$dispatch('open-modal', 'adm-fee-modal');
        },

        openEditModal(row) {
            this.editMode = true;
            this.feeId = row.id;
            this.errors = {};
            this.formData = { class_id: row.class_id, class_name: row.class_name, amount: Number(row.amount) };
            this.$dispatch('open-modal', 'adm-fee-modal');
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            const url = this.editMode
                ? `/school/settings/admission-fee/${this.feeId}`
                : `/school/settings/admission-fee`;

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

                if (response.ok && result.success) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    this.$dispatch('close-modal', 'adm-fee-modal');
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

        confirmDelete(row) {
            const self = this;
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Admission Fee',
                    message: `Remove the ₹${Number(row.amount).toLocaleString('en-IN', {minimumFractionDigits:2})} fee for ${row.class_name}? This cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/settings/admission-fee/${row.id}`, {
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
    };
}
</script>
@endpush
@endsection
