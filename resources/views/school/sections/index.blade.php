@extends('layouts.school')

@section('title', 'Section Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.sections.fetch') }}',
        defaultSort: 'class_name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), sectionManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'section-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Sections" :value="$stats['total_sections']" icon="fas fa-layer-group" color="indigo" alpine-text="stats.total_sections" />
            <x-stat-card label="Total Capacity" :value="$stats['total_capacity']" icon="fas fa-users" color="emerald" alpine-text="stats.total_capacity" />
            <x-stat-card label="Total Students" :value="$stats['total_students']" icon="fas fa-user-graduate" color="violet" alpine-text="stats.total_students" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Section Management" description="Manage all sections for your school classes" icon="fas fa-layer-group">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add New Section
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search & Filter -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Sections List</h2>
                        <x-table.search placeholder="Search sections..." />
                        
                        <!-- Class Filter Dropdown -->
                        <div class="relative min-w-[200px]">
                            <select x-model="filters.class_id" @change="refreshTable()"
                                class="w-full pl-4 pr-10 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="all">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <i class="fas fa-filter text-xs"></i>
                            </div>
                        </div>
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
                            <x-table.sort-header column="class_name" label="Class / Grade" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="name" label="Section Name" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="students_count" label="Students" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-school text-xs"></i></div>
                                        <span class="font-bold text-gray-700">{{ $row['class_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-black">{{ $row['name'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-500">
                                        <i class="fas fa-user-graduate text-[10px] text-gray-300"></i>
                                        {{ $row['students_count'] }} Students
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($row['is_available'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-tight border border-emerald-200">Available</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-100 text-rose-700 uppercase tracking-tight border border-rose-200">Unavailable</span>
                                    @endif
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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-school text-xs"></i></div>
                                        <span class="font-bold text-gray-700" x-text="row.class_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-black" x-text="row.name"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-500">
                                        <i class="fas fa-user-graduate text-[10px] text-gray-300"></i>
                                        <span x-text="row.students_count"></span> Students
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="row.is_available">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-tight border border-emerald-200">Available</span>
                                    </template>
                                    <template x-if="!row.is_available">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-100 text-rose-700 uppercase tracking-tight border border-rose-200">Unavailable</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-layer-group" message="No sections found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="section-modal" alpineTitle="editMode ? 'Edit Section' : 'Create New Section'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <!-- Class Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Assign to Class <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="class_id" x-model="formData.class_id" @change="clearError('class_id')"
                                class="modal-input-premium appearance-none pr-10"
                                :class="errors.class_id ? 'border-red-500' : 'border-slate-200'">
                                <option value="">Choose a class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.class_id">
                            <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                        </template>
                    </div>

                    <!-- Section Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Section Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                placeholder="e.g., Section A"
                                class="modal-input-premium pr-10"
                                :class="errors.name ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-tag text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <!-- Availability Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 flex items-center justify-center text-indigo-600 shadow-sm border border-gray-100 dark:border-gray-700">
                                <i class="fas fa-toggle-on"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-white">Is Available?</p>
                                <p class="text-xs text-gray-500">Students can be assigned if available</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="formData.is_available" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:width-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'section-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-blue-600 hover:!from-indigo-700 hover:!to-blue-700 shadow-indigo-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Create Section'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function sectionManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    sectionId: null,
                    formData: {
                        class_id: '',
                        name: '',
                        is_available: true
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    resetForm() {
                        this.editMode = false;
                        this.sectionId = null;
                        this.errors = {};
                        this.formData = { 
                            class_id: '',
                            name: '',
                            is_available: true
                        };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'section-modal');
                    },

                    openEditModal(section) {
                        this.editMode = true;
                        this.sectionId = section.id;
                        this.errors = {};
                        this.formData = { 
                            class_id: section.class_id || '',
                            name: section.name || '',
                            is_available: !!section.is_available
                        };
                        this.$dispatch('open-modal', 'section-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/sections/${this.sectionId}`
                            : '{{ route('school.sections.store') }}';

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
                                this.$dispatch('close-modal', 'section-modal');
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

                    confirmDelete(section) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Section',
                                message: `Are you sure you want to delete "${section.name}"? This action cannot be undone.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/sections/${section.id}`, {
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
