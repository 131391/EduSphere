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
}), feeManager())" class="space-y-6">

    {{-- ── Stats ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                <i class="fas fa-chalkboard text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Classes Configured</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.unique_classes ?? '{{ $stats['unique_classes'] }}'">{{ $stats['unique_classes'] }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <i class="fas fa-rupee-sign text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Average Fee</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">₹<span x-text="stats.average_admission_fee ?? '{{ $stats['average_admission_fee'] }}'">{{ $stats['average_admission_fee'] }}</span></p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                <i class="fas fa-list-check text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Records</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.total_configurations ?? '{{ $stats['total_configurations'] }}'">{{ $stats['total_configurations'] }}</p>
            </div>
        </div>
    </div>

    {{-- ── Page Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-door-open text-indigo-600 text-base"></i>
                Admission Fee
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Set the one-time fee charged when a student is formally admitted, per class.</p>
        </div>
        <button @click="openAddModal()"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95 shrink-0">
            <i class="fas fa-plus text-xs"></i> Add Fee
        </button>
    </div>

    {{-- ── Table Card ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

        {{-- Toolbar --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <x-table.search placeholder="Search by class name…" />
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-table.filter-select
                    model="filters.class_id"
                    action="applyFilter('class_id', $event.target.value)"
                    placeholder="All Classes"
                    :options="$classes->pluck('name', 'id')->toArray()"
                />
                <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
            </div>
        </div>

        {{-- Active filters --}}
        <div class="px-5 pb-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
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

        {{-- Table --}}
        <div class="overflow-x-auto relative ajax-table-wrapper">
            <x-table.loading-overlay />
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-700/40">
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Admission Fee</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Set On</th>
                        <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>

                {{-- SSR --}}
                <tbody :class="{'hidden': true}" class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($initialData['rows'] as $row)
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 text-xs shrink-0"><i class="fas fa-graduation-cap"></i></div>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $row['class_name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold px-3 py-1 rounded-lg text-sm border border-emerald-100 dark:border-emerald-800">
                                ₹{{ number_format($row['amount'], 2) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-sm">{{ $row['created_at'] }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEditModal(@js($row))" title="Edit" class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 flex items-center justify-center transition-colors"><i class="fas fa-pencil text-xs"></i></button>
                                <button @click="confirmDelete(@js($row))" title="Delete" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 hover:bg-red-100 flex items-center justify-center transition-colors"><i class="fas fa-trash text-xs"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Alpine --}}
                <tbody x-cloak class="divide-y divide-gray-50 dark:divide-gray-700/50 transition-opacity duration-150" :class="loading && rows.length > 0 ? 'opacity-40' : ''">
                    <template x-for="row in rows" :key="row.id">
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 text-xs shrink-0"><i class="fas fa-graduation-cap"></i></div>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100" x-text="row.class_name"></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold px-3 py-1 rounded-lg text-sm border border-emerald-100 dark:border-emerald-800">
                                    ₹<span x-text="Number(row.amount).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-sm" x-text="row.created_at"></td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button @click="openEditModal(row)" title="Edit" class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 flex items-center justify-center transition-colors"><i class="fas fa-pencil text-xs"></i></button>
                                    <button @click="confirmDelete(row)" title="Delete" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 hover:bg-red-100 flex items-center justify-center transition-colors"><i class="fas fa-trash text-xs"></i></button>
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
    <x-modal name="adm-fee-modal" alpineTitle="editMode ? 'Edit Admission Fee' : 'Add Admission Fee'" maxWidth="sm">
        <form @submit.prevent="save()" class="p-1 space-y-5">
            @csrf

            {{-- Class --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                    Class <span class="text-red-500">*</span>
                </label>
                <select x-show="!editMode" x-model="form.class_id" @change="clearErr('class_id')"
                    class="no-select2 w-full rounded-xl border px-4 py-2.5 text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    :class="errors.class_id ? 'border-red-400' : 'border-gray-200 dark:border-gray-600'">
                    <option value="">— Select a class —</option>
                    @foreach($unassignedClasses as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <div x-show="editMode"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-semibold cursor-not-allowed flex items-center gap-2">
                    <i class="fas fa-lock text-xs text-gray-400"></i>
                    <span x-text="form.class_name"></span>
                </div>
                <template x-if="errors.class_id">
                    <p x-text="errors.class_id[0]" class="mt-1 text-xs text-red-500 font-medium"></p>
                </template>
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                    Fee Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm select-none">₹</span>
                    <input type="number" step="0.01" min="0" x-model="form.amount" @input="clearErr('amount')"
                        placeholder="0.00"
                        class="w-full rounded-xl border pl-8 pr-4 py-2.5 text-sm font-semibold bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                        :class="errors.amount ? 'border-red-400' : 'border-gray-200 dark:border-gray-600'">
                </div>
                <template x-if="errors.amount">
                    <p x-text="errors.amount[0]" class="mt-1 text-xs text-red-500 font-medium"></p>
                </template>
            </div>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'adm-fee-modal')"
                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <button type="button" @click="save()" :disabled="saving"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-semibold transition-all flex items-center gap-2 min-w-[110px] justify-center">
                    <span x-show="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span x-text="saving ? 'Saving…' : (editMode ? 'Update' : 'Save Fee')"></span>
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
        saving:   false,
        errors:   {},
        form:     { class_id: '', class_name: '', amount: '' },

        clearErr(f) { delete this.errors[f]; },

        openAddModal() {
            this.editMode = false;
            this.feeId    = null;
            this.errors   = {};
            this.form     = { class_id: '', class_name: '', amount: '' };
            this.$dispatch('open-modal', 'adm-fee-modal');
        },

        openEditModal(row) {
            this.editMode = true;
            this.feeId    = row.id;
            this.errors   = {};
            this.form     = { class_id: row.class_id, class_name: row.class_name, amount: Number(row.amount) };
            this.$dispatch('open-modal', 'adm-fee-modal');
        },

        async save() {
            if (this.saving) return;
            this.saving = true;
            this.errors = {};

            const url    = this.editMode
                ? `/school/settings/admission-fee/${this.feeId}`
                : `/school/settings/admission-fee`;
            const method = this.editMode ? 'PUT' : 'POST';

            try {
                const res  = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ class_id: this.form.class_id, amount: this.form.amount }),
                });
                const json = await res.json();

                if (res.ok && json.success) {
                    window.Toast?.fire({ icon: 'success', title: json.message });
                    this.$dispatch('close-modal', 'adm-fee-modal');
                    this.refreshTable?.();
                } else if (res.status === 422) {
                    this.errors = json.errors ?? {};
                } else {
                    window.Toast?.fire({ icon: 'error', title: json.message ?? 'Something went wrong.' });
                }
            } catch (e) {
                window.Toast?.fire({ icon: 'error', title: 'Network error. Please try again.' });
            } finally {
                this.saving = false;
            }
        },

        confirmDelete(row) {
            const self = this;
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title:    'Delete Admission Fee',
                    message:  `Remove the ₹${Number(row.amount).toLocaleString('en-IN', {minimumFractionDigits:2})} fee for ${row.class_name}? This cannot be undone.`,
                    callback: async () => {
                        try {
                            const res  = await fetch(`/school/settings/admission-fee/${row.id}`, {
                                method:  'DELETE',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            });
                            const json = await res.json();
                            window.Toast?.fire({ icon: res.ok ? 'success' : 'error', title: json.message });
                            if (res.ok) self.refreshTable?.();
                        } catch {
                            window.Toast?.fire({ icon: 'error', title: 'Deletion failed.' });
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
