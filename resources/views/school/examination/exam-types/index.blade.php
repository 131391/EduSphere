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
    <x-modal name="exam-type-modal" alpineTitle="editMode ? 'Edit Exam Category' : 'Define Exam Category'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8 space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Type Name <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                            <i class="fas fa-tag text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="name" 
                            x-model="formData.name"
                            @input="if(errors.name) delete errors.name"
                            placeholder="e.g., Mid-Term Assessment"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 font-medium"
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
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Changes') : (submitting ? 'Creating...' : 'Establish Type')"></span>
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
        }
    }));
});
</script>
@endpush
@endsection
