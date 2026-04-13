@extends('layouts.school')

@section('title', 'Subject Master')

@section('content')
<div x-data="subjectMaster">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Subject Master</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all academic subjects available in your school</p>
            </div>
            <button @click="openAddModal" 
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
                'icon' => 'fas fa-trash-alt',
                'class' => 'text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors',
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
            empty-message="No subjects found in the master list"
            empty-icon="fas fa-book"
        >
            Academic Subject Inventory
        </x-data-table>
    </div>

    <!-- Add/Edit Subject Modal -->
    <x-modal name="subject-modal" alpineTitle="editMode ? 'Edit Subject Details' : 'Register New Subject'" maxWidth="2xl">
        <form id="subject-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-10 py-10 space-y-8">
                <!-- Name & Code Inline -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2 space-y-2">
                        <label class="modal-label-premium">Subject Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" x-model="formData.name" @input="clearError('name')" placeholder="e.g., Mathematics"
                                class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.name}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                                <i class="fas fa-book text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Subject Code</label>
                        <div class="relative group">
                            <input type="text" x-model="formData.code" @input="clearError('code')" placeholder="MATH-101"
                                class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.code}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                                <i class="fas fa-hashtag text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Detailed Description</label>
                    <div class="relative group">
                        <textarea x-model="formData.description" rows="3" placeholder="Brief details about the subject objectives..."
                            class="modal-input-premium !py-4 resize-none h-32"></textarea>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">Cancel</button>
                <button type="submit" form="subject-form" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Changes') : (submitting ? 'Creating...' : 'Register Subject')"></span>
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
            if (this.submitting) return;
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
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Server encountered an issue while processing your request.');
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
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Subject',
                    message: `Are you sure you want to delete the subject "${subject.name}"? This action cannot be undone and may affect academic records.`,
                    callback: async () => {
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
            this.$dispatch('close-modal', 'subject-modal');
        }
    }));
});
</script>
@endpush
@endsection
