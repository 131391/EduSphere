@extends('layouts.school')

@section('title', 'Registration Codes')

@section('content')
<div x-data="registrationCodeManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-fingerprint text-xs"></i>
                    </div>
                    Registration Codes
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage official registration codes for student tracking</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Registration Code
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'code',
                'label' => 'REGISTRATION CODE',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                            <i class="fas fa-key text-[10px]"></i>
                        </div>
                        <span class="font-bold text-gray-700 tracking-wider">' . e($row->code) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'GENERATED ON',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-sm">' . $row->created_at->format('M d, Y, h:i A') . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $code = addslashes($row->code);
                    return "window.dispatchEvent(new CustomEvent('open-delete-code', { detail: { id: " . $row->id . ", code: '{$code}' } }))";
                },
                'title' => 'Delete Code',
            ],
        ];
    @endphp

    <div x-on:open-delete-code.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$codes"
            :actions="$tableActions"
            empty-message="No registration codes generated"
            empty-icon="fas fa-code"
        >
            Registration Codes List
        </x-data-table>
    </div>

    <!-- Add Code Modal -->
    <x-modal name="registration-code-modal" title="New Registration Code" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            
            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Registration Code <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="text" 
                        name="code" 
                        x-model="formData.code"
                        @input="clearError('code')"
                        placeholder="e.g. REG-2024-001"
                        class="modal-input-premium pl-4 uppercase tracking-widest font-bold"
                        :class="{'border-red-500 ring-red-500/10': errors.code}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                        <i class="fas fa-hashtag text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.code">
                    <p class="modal-error-message" x-text="errors.code[0]"></p>
                </template>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px] bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-100">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span>Save Code</span>
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
    Alpine.data('registrationCodeManagement', () => ({
        submitting: false,
        errors: {},
        formData: {
            code: ''
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            try {
                const response = await fetch('{{ route('school.registration-codes.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Operation failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
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
            this.errors = {};
            this.formData = { code: '' };
            this.$dispatch('open-modal', 'registration-code-modal');
        },

        async confirmDelete(code) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Registration Code',
                    message: `Are you sure you want to delete the registration code "${code.code}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/registration-codes/${code.id}`, {
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
            this.$dispatch('close-modal', 'registration-code-modal');
        }
    }));
});
</script>
@endpush
@endsection
