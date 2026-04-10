@extends('layouts.school')

@section('title', 'Grading System - Examination')

@section('content')
<div x-data="gradeManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-graduation-cap text-xs"></i>
                    </div>
                    Grading Scale
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure academic grading metrics and percentage ranges</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Define Grade
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'grade',
                'label' => 'GRADE SYMBOL',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100 font-black">
                            ' . e($row->grade) . '
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'range',
                'label' => 'PERCENTAGE RANGE',
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg border border-gray-200">
                            ' . $row->range_start . '%
                        </span>
                        <div class="w-8 h-px bg-gray-200"></div>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100">
                            ' . $row->range_end . '%
                        </span>
                    </div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'STATUS',
                'render' => function($row) {
                    return '<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-emerald-100">Active</span>';
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
                        'grade' => $row->grade,
                        'range_start' => $row->range_start,
                        'range_end' => $row->range_end,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-grade', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-grade', { detail: { id: " . $row->id . ", name: 'Grade " . addslashes($row->grade) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-grade.window="openEditModal($event.detail)" 
         x-on:open-delete-grade.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$grades"
            :actions="$tableActions"
            empty-message="No grading scales defined yet"
            empty-icon="fas fa-graduation-cap"
        >
            Academic Performance Index
        </x-data-table>
    </div>

    <!-- Add/Edit Grade Modal -->
    <x-modal name="grade-modal" alpineTitle="editMode ? 'Modify Grade Scale' : 'Establish New Grade'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8 space-y-6">
                <!-- Grade Symbol -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Grade Symbol <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                            <i class="fas fa-font text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="grade" 
                            x-model="formData.grade"
                            @input="if(errors.grade) delete errors.grade"
                            placeholder="e.g., A+"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 font-black uppercase"
                            :class="{'border-red-500 ring-red-500/10': errors.grade}"
                        >
                    </div>
                    <div class="min-h-[24px] mt-1 ml-1">
                        <template x-if="errors.grade">
                            <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="errors.grade[0]"></span>
                            </p>
                        </template>
                    </div>
                </div>

                <!-- Range Start & End -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Min % <span class="text-red-500">*</span></label>
                        <input 
                            type="number" 
                            name="range_start" 
                            x-model="formData.range_start"
                            @input="if(errors.range_start) delete errors.range_start"
                            placeholder="0"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-indigo-500 transition-all font-bold text-gray-700"
                            :class="{'border-red-500': errors.range_start}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Max % <span class="text-red-500">*</span></label>
                        <input 
                            type="number" 
                            name="range_end" 
                            x-model="formData.range_end"
                            @input="if(errors.range_end) delete errors.range_end"
                            placeholder="100"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-indigo-500 transition-all font-bold text-gray-700"
                            :class="{'border-red-500': errors.range_end}"
                        >
                    </div>
                </div>
                <div class="min-h-[24px] -mt-4 ml-1">
                    <template x-if="errors.range_start || errors.range_end">
                        <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.range_start ? errors.range_start[0] : errors.range_end[0]"></span>
                        </p>
                    </template>
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
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Scale') : (submitting ? 'Establishing...' : 'Confirm Grade')"></span>
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
    Alpine.data('gradeManagement', () => ({
        editMode: false,
        gradeId: null,
        submitting: false,
        errors: {},
        formData: {
            grade: '',
            range_start: '',
            range_end: ''
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/examination/grades/${this.gradeId}` 
                : '{{ route('school.examination.grades.store') }}';
            
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
            this.gradeId = null;
            this.errors = {};
            this.formData = { grade: '', range_start: '', range_end: '' };
            this.$dispatch('open-modal', 'grade-modal');
        },
        
        openEditModal(row) {
            this.editMode = true;
            this.gradeId = row.id;
            this.errors = {};
            this.formData = {
                grade: row.grade,
                range_start: row.range_start,
                range_end: row.range_end
            };
            this.$dispatch('open-modal', 'grade-modal');
        },

        async confirmDelete(row) {
            if (window.confirm(`Are you sure you want to delete "${row.name}"?`)) {
                try {
                    const response = await fetch(`/school/examination/grades/${row.id}`, {
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
            this.$dispatch('close-modal', 'grade-modal');
        }
    }));
});
</script>
@endpush
@endsection
