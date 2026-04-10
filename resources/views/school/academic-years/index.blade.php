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
                'render' => function($row) {
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600"><i class="fas fa-calendar-alt text-xs"></i></div>';
                    $html .= '<span class="font-bold text-gray-700">' . e($row->name) . '</span>';
                    if ($row->is_current) {
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
                'render' => function($row) {
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
                'onclick' => function($row) {
                    $encoded = base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'start_date' => $row->start_date->format('Y-m-d'),
                        'end_date' => $row->end_date->format('Y-m-d'),
                        'is_current' => $row->is_current,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-year', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-year', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-year.window="openEditModal($event.detail)" x-on:open-delete-year.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$academicYears"
            :actions="$tableActions"
            empty-message="No academic years found"
            empty-icon="fas fa-calendar-alt"
        >
            Academic Years List
        </x-data-table>
    </div>

    <!-- Add/Edit Academic Year Modal -->
    <x-modal name="academic-year-modal" alpineTitle="editMode ? 'Edit Academic Year' : 'Create New Academic Year'" maxWidth="lg">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8">
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Academic Year Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-calendar-check text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                name="name" 
                                x-model="formData.name"
                                @input="if(errors.name) delete errors.name"
                                placeholder="e.g., 2025-2026"
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

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Start Date <span class="text-red-500">*</span></label>
                            <input 
                                type="date" 
                                name="start_date" 
                                x-model="formData.start_date"
                                @input="if(errors.start_date) delete errors.start_date"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.start_date}"
                            >
                            <div class="min-h-[24px] mt-1 ml-1">
                                <template x-if="errors.start_date">
                                    <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span x-text="errors.start_date[0]"></span>
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">End Date <span class="text-red-500">*</span></label>
                            <input 
                                type="date" 
                                name="end_date" 
                                x-model="formData.end_date"
                                @input="if(errors.end_date) delete errors.end_date"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.end_date}"
                            >
                            <div class="min-h-[24px] mt-1 ml-1">
                                <template x-if="errors.end_date">
                                    <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span x-text="errors.end_date[0]"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Is Current -->
                    <div class="flex items-center ml-1 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="is_current" x-model="formData.is_current" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 transition-colors"></div>
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-indigo-600 transition-colors">Set as current academic year</span>
                        </label>
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
                    <span x-text="editMode ? (submitting ? 'Updating' : 'Save Changes') : (submitting ? 'Creating' : 'Create Year')"></span>
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

        async submitForm() {
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
            if (window.confirm(`Are you sure you want to delete the academic year "${year.name}"?`)) {
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
            this.$dispatch('close-modal', 'academic-year-modal');
        }
    }));
});
</script>
@endpush
@endsection
