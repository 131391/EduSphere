@extends('layouts.school')

@section('title', 'Subject Master')

@section('content')
<div x-data="subjectMaster()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Subject Master</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all academic subjects available in your school</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Subject
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'SUBJECT DETAILS',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100"><i class="fas fa-book-open"></i></div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">' . e($row->name) . '</div>
                            <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">' . ($row->code ?? 'NO-CODE') . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'description',
                'label' => 'DESCRIPTION',
                'render' => function($row) {
                    return '<div class="text-sm text-gray-500 italic line-clamp-1">' . ($row->description ?: 'No description provided') . '</div>';
                }
            ],
            [
                'key' => 'is_active',
                'label' => 'STATUS',
                'render' => function($row) {
                     return '<span class="px-3 py-1 text-[10px] font-bold rounded-full border border-green-200 bg-green-50 text-green-700 uppercase tracking-wider">Active</span>';
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
                        'code' => $row->code,
                        'description' => $row->description,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-subject', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-subject', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-subject.window="openEditModal($event.detail)" 
         x-on:open-delete-subject.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$subjects"
            :actions="$tableActions"
            empty-message="No subjects found"
            empty-icon="fas fa-book"
        >
            Subjects List
        </x-data-table>
    </div>

    <!-- Add/Edit Subject Modal -->
    <x-modal name="subject-modal" alpineTitle="editMode ? 'Edit Subject' : 'Create New Subject'" maxWidth="lg">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8">
                <div class="space-y-5">
                    <!-- Subject Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Subject Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-book text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                name="name" 
                                x-model="formData.name"
                                @input="if(errors.name) delete errors.name"
                                placeholder="e.g., Higher Mathematics"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400"
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

                    <!-- Subject Code -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Subject Code</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-hashtag text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                name="code" 
                                x-model="formData.code"
                                placeholder="e.g., MAT-001"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400"
                            >
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Description</label>
                        <div class="relative group">
                            <textarea 
                                name="description" 
                                x-model="formData.description"
                                rows="3"
                                placeholder="Enter a brief description of the subject..."
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 resize-none"
                            ></textarea>
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
                    class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 shadow-lg shadow-indigo-200 flex items-center justify-center min-w-[170px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Saving...' : 'Update Subject') : (submitting ? 'Creating...' : 'Create Subject')"></span>
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
    Alpine.data('subjectMaster', () => ({
        editMode: false,
        subjectId: null,
        submitting: false,
        errors: {},
        formData: {
            name: '',
            code: '',
            description: ''
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/subjects/${this.subjectId}` 
                : '{{ route('school.subjects.store') }}';
            
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
            this.subjectId = null;
            this.errors = {};
            this.formData = { name: '', code: '', description: '' };
            this.$dispatch('open-modal', 'subject-modal');
        },
        
        openEditModal(subject) {
            this.editMode = true;
            this.subjectId = subject.id;
            this.errors = {};
            this.formData = {
                name: subject.name,
                code: subject.code || '',
                description: subject.description || ''
            };
            this.$dispatch('open-modal', 'subject-modal');
        },

        async confirmDelete(subject) {
            if (window.confirm(`Are you sure you want to delete the subject "${subject.name}"?`)) {
                try {
                    const response = await fetch(`/school/subjects/${subject.id}`, {
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
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'error',
                                title: result.message
                            });
                        } else {
                            alert(result.message || 'Delete failed');
                        }
                    }
                } catch (error) {
                    alert('An error occurred while deleting');
                }
            }
        },

        closeModal() {
            this.$dispatch('close-modal', 'subject-modal');
        }
    }));
});
</script>
@endpush
@endsection
