@extends('layouts.school')

@section('title', 'Section Management')

@section('content')
<div x-data="sectionManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Section Management</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all sections for your school classes</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Section
            </button>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-layer-group text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Sections</p>
                <p class="text-2xl font-black text-gray-800">{{ $stats['total_sections'] }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Capacity</p>
                <p class="text-2xl font-black text-emerald-600">{{ $stats['total_capacity'] }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-user-graduate text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Students</p>
                <p class="text-2xl font-black text-violet-600">{{ $stats['total_students'] }}</p>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'class_name',
                'label' => 'CLASS / GRADE',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-school text-xs"></i></div>
                        <span class="font-bold text-gray-700">' . e($row->class->name ?? 'N/A') . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'name',
                'label' => 'SECTION NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-black">' . e($row->name) . '</span>';
                }
            ],
            [
                'key' => 'capacity',
                'label' => 'CAPACITY',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-500">
                        <i class="fas fa-user-friends text-[10px] text-gray-300"></i>
                        ' . $row->capacity . ' Max Students
                    </div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $encoded = base64_encode(json_encode([
                        'id' => $row->id,
                        'class_id' => (string) $row->class_id,
                        'name' => $row->name,
                        'capacity' => $row->capacity,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-section', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-section', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-section.window="openEditModal($event.detail)" 
         x-on:open-delete-section.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$sections"
            :actions="$tableActions"
            empty-message="No sections found"
            empty-icon="fas fa-users"
        >
            Sections List
        </x-data-table>
    </div>

    <!-- Add/Edit Section Modal -->
    <x-modal name="section-modal" alpineTitle="editMode ? 'Edit Section' : 'Create New Section'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8">
                <div class="space-y-4">
                    <!-- Class Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Assign to Class <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <select 
                                name="class_id" 
                                x-model="formData.class_id"
                                @change="if(errors.class_id) delete errors.class_id"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.class_id}"
                            >
                                <option value="">Choose a class</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.class_id">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.class_id[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Section Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Section Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-tag text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                name="name" 
                                x-model="formData.name"
                                @input="if(errors.name) delete errors.name"
                                placeholder="e.g., Section A"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm placeholder:text-gray-400 text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.name}"
                            >
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.name">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.name[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Student Capacity <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-user-plus text-sm"></i>
                            </div>
                            <input 
                                type="number" 
                                name="capacity" 
                                x-model="formData.capacity"
                                @input="if(errors.capacity) delete errors.capacity"
                                placeholder="Max students in this section"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm placeholder:text-gray-400 text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.capacity}"
                            >
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.capacity">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.capacity[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 rounded-xl transition-all duration-200"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    :disabled="submitting"
                    class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 shadow-lg shadow-indigo-200 flex items-center justify-center min-w-[160px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Updating' : 'Save Changes') : (submitting ? 'Creating' : 'Create Section')"></span>
                </button>
            </div>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sectionManagement', () => ({
        editMode: false,
        sectionId: null,
        submitting: false,
        errors: {},
        formData: {
            class_id: '',
            name: '',
            capacity: ''
        },

        async submitForm() {
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
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message
                        });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message
                    });
                }
            } finally {
                this.submitting = false;
            }
        },

        openAddModal() {
            this.editMode = false;
            this.sectionId = null;
            this.errors = {};
            this.formData = { class_id: '', name: '', capacity: '' };
            this.$dispatch('open-modal', 'section-modal');
        },
        
        openEditModal(section) {
            this.editMode = true;
            this.sectionId = section.id;
            this.errors = {};
            this.formData = {
                class_id: String(section.class_id),
                name: section.name,
                capacity: section.capacity
            };
            this.$dispatch('open-modal', 'section-modal');
        },

        async confirmDelete(section) {
            if (window.confirm(`Are you sure you want to delete the section "${section.name}"?`)) {
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
                        window.location.reload();
                    } else {
                        alert(result.message || 'Delete failed');
                    }
                } catch (error) {
                    alert('An error occurred while deleting');
                }
            }
        },

        closeModal() {
            this.$dispatch('close-modal', 'section-modal');
        }
    }));
});
</script>
@endpush
@endsection
