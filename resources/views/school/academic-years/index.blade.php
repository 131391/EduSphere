@extends('layouts.school')
@section('title', 'Academic Years')

@section('content')
    <div x-data="academicYearManagement()">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Academic Years</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage academic years for your school</p>
                </div>
                <button @click="openAddModal()"
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

        <!-- Unified Modal Implementation (Direct HTML to ensure Scope Integrity) -->
        <div x-show="modalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;" x-cloak
            @keydown.escape.window="modalOpen = false">
            <!-- Backdrop -->
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transform transition-all" @click="modalOpen = false">
                <div class="absolute inset-0 modal-backdrop-premium"></div>
            </div>

            <!-- Modal Content Container -->
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="mb-6 bg-white rounded-xl overflow-hidden editorial-shadow transform transition-all sm:w-full sm:mx-auto sm:max-w-lg mt-20 flex flex-col">

                <form @submit.prevent="submitForm()" method="POST" novalidate>
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Modal Header -->
                    <div class="modal-header-premium">
                        <h3 class="modal-title-premium"
                            x-text="editMode ? 'Edit Academic Year' : 'Create New Academic Year'"></h3>
                        <button type="button" @click="modalOpen = false"
                            class="text-white/80 hover:text-white transition-opacity focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-8 space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="modal-label-premium">Academic Year Name <span
                                    class="text-red-600 ml-1 font-bold text-base leading-none">*</span></label>
                            <input type="text" name="name" x-model="formData.name"
                                @input="if(errors.name) delete errors.name" placeholder="e.g., 2025-2026"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.name}">
                            <div x-show="errors.name" class="mt-1">
                                <p class="modal-error-message" x-text="errors.name ? errors.name[0] : ''"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Start Date -->
                            <div class="space-y-2">
                                <label class="modal-label-premium">Start Date <span
                                        class="text-red-600 ml-1 font-bold text-base leading-none">*</span></label>
                                <div class="relative">
                                    <input type="date" name="start_date" x-model="formData.start_date"
                                        @input="if(errors.start_date) delete errors.start_date"
                                        class="modal-input-premium pr-10"
                                        :class="{'border-red-500 ring-red-500/10': errors.start_date}">
                                </div>
                                <div x-show="errors.start_date" class="mt-1">
                                    <p class="modal-error-message" x-text="errors.start_date ? errors.start_date[0] : ''">
                                    </p>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="space-y-2">
                                <label class="modal-label-premium">End Date <span
                                        class="text-red-600 ml-1 font-bold text-base leading-none">*</span></label>
                                <div class="relative">
                                    <input type="date" name="end_date" x-model="formData.end_date"
                                        @input="if(errors.end_date) delete errors.end_date"
                                        class="modal-input-premium pr-10"
                                        :class="{'border-red-500 ring-red-500/10': errors.end_date}">
                                </div>
                                <div x-show="errors.end_date" class="mt-1">
                                    <p class="modal-error-message" x-text="errors.end_date ? errors.end_date[0] : ''"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Is Current (Toggle Section) -->
                        <div class="modal-toggle-section">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-900 tracking-tight">Set as current academic
                                    year</span>
                                <span class="text-[10px] text-slate-500 font-bold uppercase mt-0.5 tracking-wide">Active
                                    sessions will transition to this timeline.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_current" x-model="formData.is_current" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all toggle-bg-premium shadow-sm transition-all">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer-premium text-right">
                        <button type="button" @click="modalOpen = false" class="btn-premium-cancel mr-3">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting" class="btn-premium-primary">
                            <template x-if="submitting">
                                <span
                                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"></span>
                            </template>
                            <span
                                x-text="editMode ? (submitting ? 'Updating...' : 'Save Changes') : (submitting ? 'Creating...' : 'Create Year')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal (assuming it exists as a component) -->
    <x-confirm-modal />

    @push('scripts')
        <script>
            function academicYearManagement() {
                return {
                    modalOpen: false,
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

                    openAddModal() {
                        this.editMode = false;
                        this.yearId = null;
                        this.errors = {};
                        this.formData = { name: '', start_date: '', end_date: '', is_current: false };
                        this.modalOpen = true;
                    },

                    openEditModal(year) {
                        console.log('Editing year:', year);
                        this.editMode = true;
                        this.yearId = year.id;
                        this.errors = {};
                        this.formData = {
                            name: year.name,
                            start_date: year.start_date,
                            end_date: year.end_date,
                            is_current: !!year.is_current
                        };
                        this.modalOpen = true;
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
                                setTimeout(() => window.location.reload(), 500);
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Something went wrong');
                            }
                        } catch (error) {
                            console.error('Submission Error:', error);
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

                    async confirmDelete(year) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Academic Year',
                                message: `Are you sure you want to delete the academic year "${year.name}"? This action cannot be undone and may affect enrolled students.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/academic-years/${year.id}`, {
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
                    }
                }
            }
        </script>
    @endpush
@endsection