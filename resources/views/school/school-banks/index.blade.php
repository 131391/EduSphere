@extends('layouts.school')

@section('title', 'School Bank Accounts')

@section('content')
<div x-data="schoolBankManagement()">
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
            <button @click="openAddModal()" 
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
                    $encoded = base64_encode(json_encode([
                        'id' => $row->id,
                        'bank_name' => $row->bank_name,
                        'account_number' => $row->account_number,
                        'branch_name' => $row->branch_name,
                        'ifsc_code' => $row->ifsc_code,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-school-bank', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-school-bank', { detail: { id: " . $row->id . ", name: '" . addslashes($row->bank_name) . "' } }))";
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
    <x-modal name="school-bank-modal" alpineTitle="editMode ? 'Edit Account Details' : 'Register New Account'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8 space-y-5">
                <!-- Bank Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Bank Name <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                            <i class="fas fa-university text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="bank_name" 
                            x-model="formData.bank_name"
                            @input="if(errors.bank_name) delete errors.bank_name"
                            placeholder="e.g., State Bank of India"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 font-medium"
                            :class="{'border-red-500 ring-red-500/10': errors.bank_name}"
                        >
                    </div>
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Account Number <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                            <i class="fas fa-hashtag text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="account_number" 
                            x-model="formData.account_number"
                            @input="if(errors.account_number) delete errors.account_number"
                            placeholder="Enter full account number"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 font-bold tracking-wider"
                            :class="{'border-red-500 ring-red-500/10': errors.account_number}"
                        >
                    </div>
                </div>

                <!-- Branch & IFSC -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Branch Name</label>
                        <input 
                            type="text" 
                            name="branch_name" 
                            x-model="formData.branch_name"
                            placeholder="Branch"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-emerald-500 transition-all text-sm font-medium text-gray-700"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">IFSC Code</label>
                        <input 
                            type="text" 
                            name="ifsc_code" 
                            x-model="formData.ifsc_code"
                            placeholder="IFSC"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-emerald-500 transition-all text-sm font-bold text-gray-700 uppercase"
                        >
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
                    class="px-8 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 shadow-lg shadow-emerald-200 flex items-center justify-center min-w-[160px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Changes') : (submitting ? 'Registering...' : 'Register Account')"></span>
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
            if (window.confirm(`Are you sure you want to delete the bank account "${bank.name}"?`)) {
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
            this.$dispatch('close-modal', 'school-bank-modal');
        }
    }));
});
</script>
@endpush
@endsection
