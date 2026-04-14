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
                    $data = json_encode([
                        'id' => $row->id,
                        'grade' => $row->grade,
                        'range_start' => $row->range_start,
                        'range_end' => $row->range_end,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-grade', { detail: $data }))";
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

    <div>
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
    <x-modal name="grade-modal" alpineTitle="editMode ? 'Modify Grade Scale' : 'Establish New Grade'" maxWidth="xl">
        <form id="grade-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Form Body - Academic Year Standard -->
            <div class="space-y-6">
                <!-- Grade Symbol Block -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Grade Symbol <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="text" x-model="formData.grade" @input="clearError('grade')" placeholder="e.g., A+"
                            class="modal-input-premium font-black uppercase pr-10" :class="{'border-red-500 ring-red-500/10': errors.grade}">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                            <i class="fas fa-font text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.grade">
                        <p class="modal-error-message" x-text="errors.grade[0]"></p>
                    </template>
                </div>

                <!-- Range Grid -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Min Percentage <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="number" x-model="formData.range_start" @input="clearError('range_start')" placeholder="0"
                                class="modal-input-premium pr-10 font-bold" :class="{'border-red-500 ring-red-500/10': errors.range_start}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-percentage text-xs"></i>
                            </div>
                        </div>
                        <template x-if="errors.range_start">
                            <p class="modal-error-message" x-text="errors.range_start[0]"></p>
                        </template>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Max Percentage <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="number" x-model="formData.range_end" @input="clearError('range_end')" placeholder="100"
                                class="modal-input-premium pr-10 font-bold" :class="{'border-red-500 ring-red-500/10': errors.range_end}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-percentage text-xs"></i>
                            </div>
                        </div>
                        <template x-if="errors.range_end">
                            <p class="modal-error-message" x-text="errors.range_end[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Guidance Notification Card -->
                <div class="mb-8 flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-graduation-cap text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 leading-tight">Grading Policy Notice</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Ensure percentage ranges do not <span class="text-indigo-600 italic underline decoration-indigo-100">overlap</span> with existing grades to maintain calculation integrity.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer - Academic Year Standard -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="grade-form" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Create Grade'"></span>
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

        init() {
            window.addEventListener('open-edit-grade', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-grade', (e) => this.confirmDelete(e.detail));
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
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Grading Metric',
                    message: `Are you sure you want to delete the grading metric for "${row.name}"? This action cannot be undone.`,
                    callback: async () => {
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
            this.$dispatch('close-modal', 'grade-modal');
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        }
    }));
});
</script>
@endpush
@endsection
