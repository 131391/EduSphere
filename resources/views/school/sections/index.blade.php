@extends('layouts.school')

@section('title', 'Section Management')

@section('content')
<div x-data="sectionManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Section Management</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all sections for your school classes</p>
            </div>
            <button @click="openAddModal" 
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
                    $data = json_encode([
                        'id' => $row->id,
                        'class_id' => $row->class_id,
                        'name' => $row->name,
                        'capacity' => $row->capacity,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-section', { detail: $data }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-section', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
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
    <x-modal name="section-modal" alpineTitle="editMode ? 'Edit Section' : 'Create New Section'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6">
                <!-- Class Selection -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Assign to Class <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select 
                            name="class_id" 
                            x-model="formData.class_id"
                            @change="clearError('class_id')"
                            class="modal-input-premium appearance-none !pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.class_id}"
                        >
                            <option value="">Choose a class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <i class="fas fa-chevron-down text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.class_id">
                        <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                    </template>
                </div>

                <!-- Section Name -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Section Name <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input 
                            type="text" 
                            name="name" 
                            x-model="formData.name"
                            @input="clearError('name')"
                            placeholder="e.g., Section A"
                            class="modal-input-premium"
                            :class="{'border-red-500 ring-red-500/10': errors.name}"
                        >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i class="fas fa-tag text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.name">
                        <p class="modal-error-message" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <!-- Capacity -->
                <div class="space-y-2 mb-8">
                    <label class="modal-label-premium">Student Capacity <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input 
                            type="number" 
                            name="capacity" 
                            x-model="formData.capacity"
                            @input="clearError('capacity')"
                            placeholder="Max students in this section"
                            class="modal-input-premium"
                            :class="{'border-red-500 ring-red-500/10': errors.capacity}"
                        >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i class="fas fa-user-friends text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.capacity">
                        <p class="modal-error-message" x-text="errors.capacity[0]"></p>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Create Section'"></span>
                </button>
            </x-slot>
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
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message
                        });
                    }
                    setTimeout(() => window.location.reload(), 800);
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

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
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
            // Strict type ensuring for select components
            this.formData = {
                class_id: section.class_id,
                name: section.name,
                capacity: section.capacity
            };
            this.$dispatch('open-modal', 'section-modal');
        },

        async confirmDelete(section) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Section',
                    message: `Are you sure you want to delete the section "${section.name}"? This action cannot be undone and will affect all assigned students.`,
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
                            
                            if (response.ok) {
                                window.location.reload();
                            } else {
                                const result = await response.json();
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: 'error',
                                        title: result.message || 'Delete failed'
                                    });
                                }
                            }
                        } catch (error) {
                            console.error('Delete Error:', error);
                        }
                    }
                }
            }));
        },

        closeModal() {
            this.$dispatch('close-modal', 'section-modal');
        }
    }));
});
</script>
@endpush
@endsection
