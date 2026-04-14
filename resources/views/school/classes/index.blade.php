@extends('layouts.school')

@section('title', 'Class Management')

@section('content')
    <div x-data="classManagement">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Class Management</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage school classes and their availability</p>
                </div>
                <button @click="openAddModal"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Class
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-chalkboard text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Classes</p>
                    <p class="text-2xl font-black text-gray-800">{{ $stats['total_classes'] }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Available</p>
                    <p class="text-2xl font-black text-emerald-600">{{ $stats['available_classes'] }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Unavailable</p>
                    <p class="text-2xl font-black text-rose-600">{{ $stats['unavailable_classes'] }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-xl"></i>
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
                    'key' => 'name',
                    'label' => 'CLASS DETAILS',
                    'sortable' => true,
                    'render' => function ($row) {
                        return '
                                                                                                                                                                                                <div class="flex items-center gap-4">
                                                                                                                                                                                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-chalkboard-teacher"></i></div>
                                                                                                                                                                                                    <div>
                                                                                                                                                                                                        <div class="text-sm font-bold text-gray-800">' . e($row->name) . '</div>
                                                                                                                                                                                                        <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-tight">' . $row->sections_count . ' sections • ' . $row->students_count . ' students</div>
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                </div>';
                    }
                ],
                [
                    'key' => 'is_available',
                    'label' => 'STATUS',
                    'sortable' => true,
                    'render' => function ($row) {
                        $color = $row->is_available ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200';
                        $text = $row->is_available ? 'Available' : 'Unavailable';
                        return '<span class="px-3 py-1 text-[10px] font-bold rounded-full border uppercase tracking-wider ' . $color . '">' . $text . '</span>';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'button',
                    'icon' => 'fas fa-edit',
                    'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors',
                    'onclick' => function ($row) {
                        $data = json_encode([
                            'id' => $row->id,
                            'name' => $row->name,
                        ]);
                        return "window.dispatchEvent(new CustomEvent('open-edit-class', { detail: $data }))";
                    },
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-power-off',
                    'class' => 'text-teal-600 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 p-2 rounded-lg transition-colors',
                    'onclick' => function ($row) {
                        return "window.dispatchEvent(new CustomEvent('toggle-class-availability', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                    },
                    'title' => 'Toggle Availability',
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                    'onclick' => function ($row) {
                        $name = addslashes($row->name);
                        return "window.dispatchEvent(new CustomEvent('open-delete-class', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                    },
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <div>
            <x-data-table :columns="$tableColumns" :data="$classes" :actions="$tableActions"
                empty-message="No classes found" empty-icon="fas fa-chalkboard">
                Classes List
            </x-data-table>
        </div>

        <!-- Add/Edit Class Modal -->
        <x-modal name="class-modal" alpineTitle="editMode ? 'Edit Class' : 'Create New Class'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Class Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                placeholder="e.g., Grade 10-A" class="modal-input-premium pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.name}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-indigo-500">
                                <i class="fas fa-school text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Modal Footer -->
                <x-slot name="footer">
                    <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px]">
                        <template x-if="submitting">
                            <span
                                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Class' : 'Create Class'"></span>
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
                Alpine.data('classManagement', () => ({
                    editMode: false,
                    classId: null,
                    submitting: false,
                    errors: {},
                    formData: {
                        name: ''
                    },

                    init() {
                        window.addEventListener('open-edit-class', (e) => this.openEditModal(e.detail));
                        window.addEventListener('toggle-class-availability', (e) => this.toggleAvailability(e.detail));
                        window.addEventListener('open-delete-class', (e) => this.confirmDelete(e.detail));
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/classes/${this.classId}`
                            : '{{ route('school.classes.store') }}';

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
                        this.classId = null;
                        this.errors = {};
                        this.formData = { name: '' };
                        this.$dispatch('open-modal', 'class-modal');
                    },

                    openEditModal(classData) {
                        this.editMode = true;
                        this.classId = classData.id;
                        this.errors = {};
                        this.formData = {
                            name: classData.name
                        };
                        this.$dispatch('open-modal', 'class-modal');
                    },

                    async toggleAvailability(classData) {
                        try {
                            const response = await fetch(`/school/classes/${classData.id}/toggle-availability`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'PATCH' })
                            });

                            if (response.ok) {
                                window.location.reload();
                            } else {
                                const result = await response.json();
                                alert(result.message || 'Toggle failed');
                            }
                        } catch (error) {
                            alert('An error occurred');
                        }
                    },

                    async confirmDelete(classData) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Class',
                                message: `Are you sure you want to delete the class "${classData.name}"? This action cannot be undone and will affect all associated students and sections.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/classes/${classData.id}`, {
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
                        this.$dispatch('close-modal', 'class-modal');
                    }
                }));
            });
        </script>
    @endpush
@endsection