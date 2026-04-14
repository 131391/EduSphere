@extends('layouts.school')

@section('title', 'Fee Names')

@section('content')
<div x-data="feeNameManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-tag text-xs"></i>
                    </div>
                    Fee Names
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define specific fee line-items used across the school</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Fee Name
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'FEE LABEL',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm">
                            <i class="fas fa-tag text-xs"></i>
                        </div>
                        <span class="font-bold text-gray-700">' . e($row->name) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'description',
                'label' => 'DESCRIPTION',
                'render' => function($row) {
                    return '<div class="text-sm text-gray-500 italic line-clamp-1">' . ($row->description ?: 'No description') . '</div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'ADDED ON',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-sm">' . $row->created_at->format('M d, Y') . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $encoded = json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'description' => $row->description,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-fee-name', { detail: $encoded }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-fee-name', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div>
        <x-data-table 
            :columns="$tableColumns"
            :data="$feeNames"
            :actions="$tableActions"
            empty-message="No fee names defined"
            empty-icon="fas fa-list-ul"
        >
            Fee Names List
        </x-data-table>
    </div>

    <!-- Add/Edit Fee Name Modal -->
    <x-modal name="fee-name-modal" alpineTitle="editMode ? 'Edit Fee Name' : 'Create Fee Name'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6">
                <!-- Fee Name -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Fee Label <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input 
                            type="text" 
                            name="name" 
                            x-model="formData.name"
                            @input="clearError('name')"
                            placeholder="e.g., Monthly Tuition Fee"
                            class="modal-input-premium pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.name}"
                        >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                            <i class="fas fa-signature text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.name">
                        <p class="modal-error-message" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Fee Description</label>
                    <textarea 
                        name="description" 
                        x-model="formData.description"
                        rows="3"
                        placeholder="Optional details about this fee item..."
                        class="modal-input-premium px-4 py-3 resize-none h-32"
                    ></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px] bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-100">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Create Name'"></span>
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
    Alpine.data('feeNameManagement', () => ({
        editMode: false,
        feeNameId: null,
        submitting: false,
        errors: {},
        formData: {
            name: '',
            description: ''
        },

        init() {
            window.addEventListener('open-edit-fee-name', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-fee-name', (e) => this.confirmDelete(e.detail));
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/fee-names/${this.feeNameId}` 
                : '{{ route('school.fee-names.store') }}';
            
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
            this.feeNameId = null;
            this.errors = {};
            this.formData = { name: '', description: '' };
            this.$dispatch('open-modal', 'fee-name-modal');
        },
        
        openEditModal(feeName) {
            this.editMode = true;
            this.feeNameId = feeName.id;
            this.errors = {};
            this.formData = {
                name: feeName.name,
                description: feeName.description || ''
            };
            this.$dispatch('open-modal', 'fee-name-modal');
        },

        async confirmDelete(feeName) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Fee Name',
                    message: `Are you sure you want to delete the fee name "${feeName.name}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/fee-names/${feeName.id}`, {
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
            this.$dispatch('close-modal', 'fee-name-modal');
        }
    }));
});
</script>
@endpush
@endsection

