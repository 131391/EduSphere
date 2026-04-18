@extends('layouts.school')
@section('title', 'Academic Years')

@section('content')
    <div x-data="academicYearManagement">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Academic Years</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage academic years for your school</p>
                </div>
                <button @click="openAddModal"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Add Academic Year
                </button>
            </div>
        </div>

        @php
            $tableColumns = [
                [
                    'key' => 'name',
                    'label' => 'ACADEMIC YEAR',
                    'sortable' => true,
                    'render' => function ($row) {
                        $html = '<div class="flex items-center gap-3">';
                        $html .= '<div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600"><i class="fas fa-calendar-alt text-xs"></i></div>';
                        $html .= '<span class="font-bold text-gray-700">' . e($row->name) . '</span>';
                        if ($row->is_current === \App\Enums\YesNo::Yes) {
                            $html .= '<span class="px-2.5 py-0.5 text-[10px] font-bold bg-green-100 text-green-700 rounded-full uppercase tracking-wider shadow-sm border border-green-200">Current</span>';
                        }
                        $html .= '</div>';
                        return $html;
                    }
                ],
                [
                    'key' => 'start_date',
                    'label' => 'DURATION',
                    'sortable' => true,
                    'render' => function ($row) {
                        return '<div class="flex items-center text-gray-500 text-sm">' .
                            '<i class="far fa-clock mr-2 text-gray-400"></i>' .
                            $row->start_date->format('M d, Y') . ' — ' . $row->end_date->format('M d, Y') .
                            '</div>';
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
                            'start_date' => $row->start_date->format('Y-m-d'),
                            'end_date' => $row->end_date->format('Y-m-d'),
                            'is_current' => $row->is_current,
                        ]);
                        return "window.dispatchEvent(new CustomEvent('open-edit-year', { detail: $data }))";
                    },
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                    'onclick' => function ($row) {
                        $name = addslashes($row->name);
                        return "window.dispatchEvent(new CustomEvent('open-delete-year', { detail: { id: {$row->id}, name: '{$name}' } }))";
                    },
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$academicYears" :actions="$tableActions"
            empty-message="No academic years found" empty-icon="fas fa-calendar-alt">
            Academic Years List
        </x-data-table>

        <x-modal name="academic-year-modal" alpineTitle="editMode ? 'Edit Academic Year' : 'Create New Academic Year'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Name -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Academic Year Name <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="text" name="name" x-model="formData.name"
                            @input="clearError('name')" placeholder="e.g., 2025-2026"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.name}">
                    </div>
                    <template x-if="errors.name">
                        <p class="modal-error-message" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Start Date -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Start Date <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="date" name="start_date" x-model="formData.start_date"
                                @input="clearError('start_date')"
                                class="modal-input-premium !pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.start_date}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i class="fas fa-calendar-alt text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.start_date">
                            <p class="modal-error-message" x-text="errors.start_date[0]"></p>
                        </template>
                    </div>

                    <!-- End Date -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">End Date <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="date" name="end_date" x-model="formData.end_date"
                                @input="clearError('end_date')"
                                class="modal-input-premium !pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.end_date}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i class="fas fa-calendar-alt text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.end_date">
                            <p class="modal-error-message" x-text="errors.end_date[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Is Current (Toggle Section) -->
                <div class="mb-8 flex items-center justify-between bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 leading-tight">Set as current academic year</span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80">Active sessions will transition to this timeline.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_current" x-model="formData.is_current" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 transition-all"></div>
                    </label>
                </div>

                <!-- Footer -->
                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'academic-year-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Create Year'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>
    </div>

    <!-- Confirmation Modal (assuming it exists as a component) -->
    <x-confirm-modal />

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('academicYearManagement', () => ({
                    editMode: false,
                    yearId: null,
                    submitting: false,
                    errors: {},
                    formData: {
                        name: '',
                        start_date: '',
                        end_date: '',
                        is_current: false
                    },

                    init() {
                        window.addEventListener('open-edit-year', (e) => this.openEditModal(e.detail));
                        window.addEventListener('open-delete-year', (e) => this.confirmDelete(e.detail));
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/school/academic-years/${this.yearId}`
                            : '{{ route('school.academic-years.store') }}';

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
                        this.yearId = null;
                        this.errors = {};
                        this.formData = { name: '', start_date: '', end_date: '', is_current: false };
                        this.$dispatch('open-modal', 'academic-year-modal');
                    },

                    openEditModal(year) {
                        this.editMode = true;
                        this.yearId = year.id;
                        this.errors = {};
                        this.formData = {
                            name: year.name,
                            start_date: year.start_date,
                            end_date: year.end_date,
                            is_current: !!year.is_current
                        };
                        this.$dispatch('open-modal', 'academic-year-modal');
                    },

                    async confirmDelete(year) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Academic Year',
                                message: `Are you sure you want to delete the academic year "${year.name}"? This action cannot be undone and may affect enrolled students.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/academic-years/${year.id}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
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
                    }
                }));
            });
        </script>
    @endpush
@endsection