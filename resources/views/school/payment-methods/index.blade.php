@extends('layouts.school')

@section('title', 'Payment Methods')

@section('content')
<div x-data="paymentMethodManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-credit-card text-xs"></i>
                    </div>
                    Payment Methods
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure acceptable payment modes like Cash, Bank Transfer, or Online Portals</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Payment Method
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'METHOD NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm">
                            <i class="fas fa-money-bill-wave text-[10px]"></i>
                        </div>
                        <span class="font-bold text-gray-700">' . e($row->name) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'code',
                'label' => 'SYSTEM CODE',
                'sortable' => true,
                'render' => function($row) {
                    return '<code class="px-2 py-1 bg-gray-100 text-emerald-700 text-[10px] font-bold rounded-md border border-gray-200 uppercase tracking-tighter">' . ($row->code ?: 'N/A') . '</code>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'CONFIGURED ON',
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
                'class' => 'text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $encoded = base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'code' => $row->code,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-payment-method', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-payment-method', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-payment-method.window="openEditModal($event.detail)" 
         x-on:open-delete-payment-method.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$methods"
            :actions="$tableActions"
            empty-message="No payment methods configured"
            empty-icon="fas fa-credit-card"
        >
            Payment Methods Matrix
        </x-data-table>
    </div>

    <!-- Add/Edit Payment Method Modal -->
    <x-modal name="payment-method-modal" alpineTitle="editMode ? 'Edit Payment Method' : 'Create Payment Mode'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8 space-y-6">
                <!-- Method Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Method Name <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                            <i class="fas fa-wallet text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="name" 
                            x-model="formData.name"
                            @input="if(errors.name) delete errors.name"
                            placeholder="e.g., Online Gateway"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 font-medium"
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

                <!-- Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Reference Code</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                            <i class="fas fa-barcode text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="code" 
                            x-model="formData.code"
                            @input="if(errors.code) delete errors.code"
                            placeholder="e.g., ONLINE_PG"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 uppercase font-bold"
                            :class="{'border-red-500 ring-red-500/10': errors.code}"
                        >
                    </div>
                    <div class="min-h-[24px] mt-1 ml-1">
                        <template x-if="errors.code">
                            <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="errors.code[0]"></span>
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
                    class="px-8 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 shadow-lg shadow-emerald-200 flex items-center justify-center min-w-[160px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Changes') : (submitting ? 'Creating...' : 'Create Mode')"></span>
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
    Alpine.data('paymentMethodManagement', () => ({
        editMode: false,
        methodId: null,
        submitting: false,
        errors: {},
        formData: {
            name: '',
            code: ''
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/payment-methods/${this.methodId}` 
                : '{{ route('school.payment-methods.store') }}';
            
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
            this.methodId = null;
            this.errors = {};
            this.formData = { name: '', code: '' };
            this.$dispatch('open-modal', 'payment-method-modal');
        },
        
        openEditModal(method) {
            this.editMode = true;
            this.methodId = method.id;
            this.errors = {};
            this.formData = {
                name: method.name,
                code: method.code || ''
            };
            this.$dispatch('open-modal', 'payment-method-modal');
        },

        async confirmDelete(method) {
            if (window.confirm(`Are you sure you want to delete the payment method "${method.name}"?`)) {
                try {
                    const response = await fetch(`/school/payment-methods/${method.id}`, {
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
            this.$dispatch('close-modal', 'payment-method-modal');
        }
    }));
});
</script>
@endpush
@endsection
