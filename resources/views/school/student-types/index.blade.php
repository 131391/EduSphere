@extends('layouts.school')

@section('title', 'Student Types')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.student-types.fetch') }}',
        defaultSort: 'name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), studentTypeManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'student-type-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Student Types" :value="$stats['total']" icon="fas fa-user-tag" color="indigo" alpine-text="stats.total" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Student Types" description="Manage student classification types" icon="fas fa-user-tag">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Student Type
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Student Types List</h2>
                        <x-table.search placeholder="Search types..." />
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
                            <x-table.sort-header column="name" label="Student Type" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Default</th>
                            <x-table.sort-header column="created_at" label="Added On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-user-tag text-xs"></i>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($row['is_default'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-tight border border-emerald-200">Default</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-gray-100 text-gray-500 uppercase tracking-tight border border-gray-200">Optional</span>
                                    @endif
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
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-user-tag text-xs"></i>
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="row.is_default">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-tight border border-emerald-200">Default</span>
                                    </template>
                                    <template x-if="!row.is_default">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-gray-100 text-gray-500 uppercase tracking-tight border border-gray-200">Optional</span>
                                    </template>
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

                        <x-table.empty-state :colspan="4" icon="fas fa-user-tag" message="No student types found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="student-type-modal" alpineTitle="editMode ? 'Edit Student Type' : 'Create New Student Type'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Student Type Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                placeholder="e.g., General, Staff Ward"
                                class="modal-input-premium pr-10"
                                :class="errors.name ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-user-tag text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <!-- Default Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 flex items-center justify-center text-indigo-600 shadow-sm border border-gray-100 dark:border-gray-700">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-white">Make Default?</p>
                                <p class="text-xs text-gray-500">Assign this type by default to new students</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="formData.is_default" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:width-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'student-type-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-blue-600 hover:!from-indigo-700 hover:!to-blue-700 shadow-indigo-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Create Type'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function studentTypeManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    typeId: null,
                    formData: {
                        name: '',
                        is_default: false
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    resetForm() {
                        this.editMode = false;
                        this.typeId = null;
                        this.errors = {};
                        this.formData = { 
                            name: '',
                            is_default: false
                        };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'student-type-modal');
                    },

                    openEditModal(type) {
                        this.editMode = true;
                        this.typeId = type.id;
                        this.errors = {};
                        this.formData = { 
                            name: type.name || '',
                            is_default: !!type.is_default
                        };
                        this.$dispatch('open-modal', 'student-type-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/student-types/${this.typeId}`
                            : '{{ route('school.student-types.store') }}';

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
                                this.$dispatch('close-modal', 'student-type-modal');
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

                    confirmDelete(type) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Student Type',
                                message: `Are you sure you want to delete "${type.name}"? This action cannot be undone.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/student-types/${type.id}`, {
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
