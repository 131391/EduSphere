@extends('layouts.school')

@section('title', 'Exam Types - Examination')

@section('content')
<div x-data="examTypeManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-file-invoice text-xs"></i>
                    </div>
                    Exam Categories
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define examination types like Mid-Term, Final, or Monthly Assessment</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Define New Type
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'EXAM TYPE NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm">
                            <i class="fas fa-bookmark text-[10px]"></i>
                        </div>
                        <span class="font-bold text-gray-700">' . e($row->name) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'ESTABLISHED ON',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-[12px] font-medium">' . $row->created_at->format('M d, Y') . '</div>';
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
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-exam-type', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-exam-type', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-exam-type.window="openEditModal($event.detail)" 
         x-on:open-delete-exam-type.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$examTypes"
            :actions="$tableActions"
            empty-message="No exam types defined yet"
            empty-icon="fas fa-file-invoice"
        >
            Examination Parameters
        </x-data-table>
    </div>

    <!-- Add/Edit Exam Type Modal -->
    <x-modal name="exam-type-modal" alpineTitle="editMode ? 'Edit Exam Category' : 'Define Exam Category'" maxWidth="2xl">
        <form id="exam-type-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Form Body - Using the exact structure from Academic Years -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Type Name <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input type="text" x-model="formData.name" @input="clearError('name')" placeholder="e.g., Mid-Term Assessment"
                        class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.name}">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:scale-110 transition-transform">
                        <i class="fas fa-tag text-[10px]"></i>
                    </div>
                </div>
                <template x-if="errors.name">
                    <p class="modal-error-message" x-text="errors.name[0]"></p>
                </template>
            </div>

            <!-- Notification Card앉 sits like Academic Year standard -->
            <div class="mb-8 flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                    <i class="fas fa-layer-group text-indigo-600 text-sm"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[13px] font-bold text-slate-900 leading-tight">Classification Notice</span>
                    <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                        Categories help group <span class="text-indigo-600 italic">assessment subjects</span> into logical grading sessions like Mid-Term or Final Exams.
                    </p>
                </div>
            </div>

            <!-- Modal Footer - Exact Match -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="exam-type-form" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Changes') : (submitting ? 'Creating...' : 'Establish Type')"></span>
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
    Alpine.data('examTypeManagement', () => ({
        editMode: false,
        typeId: null,
        submitting: false,
        errors: {},
        formData: {
            name: ''
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/examination/exam-types/${this.typeId}` 
                : '{{ route('school.examination.exam-types.store') }}';
            
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
            this.typeId = null;
            this.errors = {};
            this.formData = { name: '' };
            this.$dispatch('open-modal', 'exam-type-modal');
        },
        
        openEditModal(type) {
            this.editMode = true;
            this.typeId = type.id;
            this.errors = {};
            this.formData = {
                name: type.name
            };
            this.$dispatch('open-modal', 'exam-type-modal');
        },

        async confirmDelete(type) {
            if (window.confirm(`Are you sure you want to delete the exam type "${type.name}"?`)) {
                try {
                    const response = await fetch(`/school/examination/exam-types/${type.id}`, {
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
            this.$dispatch('close-modal', 'exam-type-modal');
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
