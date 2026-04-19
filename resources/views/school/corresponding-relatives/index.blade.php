@extends('layouts.school')

@section('title', 'Corresponding Relatives')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.corresponding-relatives.fetch') }}',
        defaultSort: 'name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), relativeManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'relative-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Total Types" :value="$stats['total_relatives']" icon="fas fa-users-cog" color="indigo" alpine-text="stats.total_relatives" />
            <x-stat-card label="Active Configurations" :value="$stats['active_relatives']" icon="fas fa-check-circle" color="emerald" alpine-text="stats.active_relatives" />
            <x-stat-card label="System Status" value="Optimized" icon="fas fa-shield-alt" color="amber" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Corresponding Relatives" description="Configure and manage relationship types for student profiles and emergency contacts." icon="fas fa-users-cog">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Relative Type
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Configured Types</h2>
                        <x-table.search placeholder="Search relative types..." />
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
                            <x-table.sort-header column="name" label="Relation Name" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Added On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                            <i class="fas fa-hashtag text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</span>
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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                            <i class="fas fa-hashtag text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></span>
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

                        <x-table.empty-state :colspan="3" icon="fas fa-users-cog" message="No relationship types found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="relative-modal" alpineTitle="editMode ? 'Edit Relationship Type' : 'Add New Relationship Type'" maxWidth="md">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Relationship Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                placeholder="e.g., Guardian, Sibling, Spouse"
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

                    <div class="bg-teal-50 dark:bg-gray-700/50 p-4 rounded-xl border border-teal-100 dark:border-gray-600">
                        <p class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest mb-1">Administrative Tip</p>
                        <p class="text-xs text-teal-800/70 dark:text-gray-400 leading-relaxed font-medium">Use standardized naming to keep student profiles and relative mappings clean and searchable.</p>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'relative-modal')" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-teal-600 !to-emerald-600 shadow-teal-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Type' : 'Save Record'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function relativeManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    relativeId: null,
                    formData: {
                        name: ''
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    resetForm() {
                        this.editMode = false;
                        this.relativeId = null;
                        this.errors = {};
                        this.formData = { name: '' };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'relative-modal');
                    },

                    openEditModal(relative) {
                        this.editMode = true;
                        this.relativeId = relative.id;
                        this.errors = {};
                        this.formData = { name: relative.name || '' };
                        this.$dispatch('open-modal', 'relative-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/corresponding-relatives/${this.relativeId}`
                            : '{{ route('school.corresponding-relatives.store') }}';

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
                                this.$dispatch('close-modal', 'relative-modal');
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

                    confirmDelete(relative) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Relationship Type',
                                message: `Are you sure you want to delete "${relative.name}"? This action cannot be undone and may affect associated student records.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/corresponding-relatives/${relative.id}`, {
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
