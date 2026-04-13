@extends('layouts.school')

@section('title', 'Blood Groups')

@section('content')
<div x-data="bloodGroupManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Blood Groups</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage blood group types for medical records</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Blood Group
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'BLOOD GROUP',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
                            <i class="fas fa-tint text-xs"></i>
                        </div>
                        <span class="font-bold text-gray-700">' . e($row->name) . '</span>
                    </div>';
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
                'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $encoded = json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-blood-group', { detail: $encoded }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-blood-group', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-blood-group.window="openEditModal($event.detail)" 
         x-on:open-delete-blood-group.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$groups"
            :actions="$tableActions"
            empty-message="No blood groups found"
            empty-icon="fas fa-tint"
        >
            Blood Groups List
        </x-data-table>
    </div>

    <!-- Add/Edit Group Modal -->
    <x-modal name="blood-group-modal" alpineTitle="editMode ? 'Edit Blood Group' : 'Create New Blood Group'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Blood Group Name <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        @input="clearError('name')"
                        placeholder="e.g., A+, O-"
                        class="modal-input-premium"
                        :class="{'border-red-500 ring-red-500/10': errors.name}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-red-500">
                        <i class="fas fa-tint text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.name">
                    <p class="modal-error-message" x-text="errors.name[0]"></p>
                </template>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px] !from-red-600 !to-rose-600 hover:!from-red-700 hover:!to-rose-700 shadow-red-200">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Create Group'"></span>
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
    Alpine.data('bloodGroupManagement', () => ({
        editMode: false,
        groupId: null,
        submitting: false,
        errors: {},
        formData: {
            name: ''
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/blood-groups/${this.groupId}` 
                : '{{ route('school.blood-groups.store') }}';
            
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
            this.groupId = null;
            this.errors = {};
            this.formData = { name: '' };
            this.$dispatch('open-modal', 'blood-group-modal');
        },
        
        openEditModal(group) {
            this.editMode = true;
            this.groupId = group.id;
            this.errors = {};
            this.formData = {
                name: group.name
            };
            this.$dispatch('open-modal', 'blood-group-modal');
        },

        async confirmDelete(group) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Blood Group',
                    message: `Are you sure you want to delete the blood group "${group.name}"? This action cannot be undone and may affect medical records.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/blood-groups/${group.id}`, {
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
            this.$dispatch('close-modal', 'blood-group-modal');
        }
    }));
});
</script>
@endpush
@endsection
