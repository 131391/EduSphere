@extends('layouts.school')

@section('title', 'School Bank Accounts')

@section('content')
<div x-data="schoolBankManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-university text-xs"></i>
                    </div>
                    School Bank Accounts
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage official bank accounts for fee collection and payouts</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Bank Account
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'bank_name',
                'label' => 'BANK & BRANCH',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm">
                            <i class="fas fa-building-columns text-[10px]"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-700">' . e($row->bank_name) . '</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">' . e($row->branch_name ?: 'Main Branch') . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'account_number',
                'label' => 'ACCOUNT DETAILS',
                'sortable' => true,
                'render' => function($row) {
                    return '<div>
                                <div class="text-sm font-mono font-bold text-gray-700 tracking-wider">' . e($row->account_number) . '</div>
                                <div class="text-[10px] text-emerald-600 font-bold tracking-widest">' . e($row->ifsc_code ?: 'NO IFSC') . '</div>
                            </div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'STATUS',
                'render' => function($row) {
                    return '<span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-lg uppercase tracking-tight border border-emerald-100">Active</span>';
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
                        'bank_name' => $row->bank_name,
                        'account_number' => $row->account_number,
                        'branch_name' => $row->branch_name,
                        'ifsc_code' => $row->ifsc_code,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-school-bank', { detail: $encoded }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->bank_name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-school-bank', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-school-bank.window="openEditModal($event.detail)" 
         x-on:open-delete-school-bank.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$banks"
            :actions="$tableActions"
            empty-message="No school bank accounts configured"
            empty-icon="fas fa-university"
        >
            Registered Bank Accounts
        </x-data-table>
    </div>

    <!-- Add/Edit Bank Modal -->
    <x-modal name="school-bank-modal" alpineTitle="editMode ? 'Edit Account Details' : 'Register New Account'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Bank Name -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Bank Name <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="text" 
                        name="bank_name" 
                        x-model="formData.bank_name"
                        @input="clearError('bank_name')"
                        placeholder="e.g., State Bank of India"
                        class="modal-input-premium"
                        :class="{'border-red-500 ring-red-500/10': errors.bank_name}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                        <i class="fas fa-university text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.bank_name">
                    <p class="modal-error-message" x-text="errors.bank_name[0]"></p>
                </template>
            </div>

            <!-- Account Number -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Account Number <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="text" 
                        name="account_number" 
                        x-model="formData.account_number"
                        @input="clearError('account_number')"
                        placeholder="Enter full account number"
                        class="modal-input-premium font-bold tracking-wider"
                        :class="{'border-red-500 ring-red-500/10': errors.account_number}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                        <i class="fas fa-hashtag text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.account_number">
                    <p class="modal-error-message" x-text="errors.account_number[0]"></p>
                </template>
            </div>

            <!-- Branch & IFSC -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="space-y-2">
                    <label class="modal-label-premium">Branch Name</label>
                    <input 
                        type="text" 
                        name="branch_name" 
                        x-model="formData.branch_name"
                        placeholder="Branch"
                        class="modal-input-premium text-sm font-medium"
                    >
                </div>
                <div class="space-y-2">
                    <label class="modal-label-premium">IFSC Code</label>
                    <input 
                        type="text" 
                        name="ifsc_code" 
                        x-model="formData.ifsc_code"
                        placeholder="IFSC"
                        class="modal-input-premium text-sm font-bold uppercase"
                    >
                </div>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-100 min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Register Account'"></span>
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
    Alpine.data('schoolBankManagement', () => ({
        editMode: false,
        bankId: null,
        submitting: false,
        errors: {},
        formData: {
            bank_name: '',
            account_number: '',
            branch_name: '',
            ifsc_code: ''
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/school-banks/${this.bankId}` 
                : '{{ route('school.school-banks.store') }}';
            
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
            this.bankId = null;
            this.errors = {};
            this.formData = { bank_name: '', account_number: '', branch_name: '', ifsc_code: '' };
            this.$dispatch('open-modal', 'school-bank-modal');
        },
        
        openEditModal(bank) {
            this.editMode = true;
            this.bankId = bank.id;
            this.errors = {};
            this.formData = {
                bank_name: bank.bank_name,
                account_number: bank.account_number,
                branch_name: bank.branch_name || '',
                ifsc_code: bank.ifsc_code || ''
            };
            this.$dispatch('open-modal', 'school-bank-modal');
        },

        async confirmDelete(bank) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Bank Account',
                    message: `Are you sure you want to delete the bank account "${bank.bank_name}"? This action cannot be undone and may affect pending fee transactions.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/school-banks/${bank.id}`, {
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
            this.$dispatch('close-modal', 'school-bank-modal');
        }
    }));
});
</script>
@endpush
@endsection
