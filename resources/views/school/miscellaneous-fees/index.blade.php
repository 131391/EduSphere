@extends('layouts.school')

@section('title', 'Miscellaneous Fees')

@section('content')
<div x-data="miscFeeManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-coins text-xs"></i>
                    </div>
                    Miscellaneous Fees
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage ad-hoc and one-time fees for various services</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Fee
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'FEE PARTICULARS',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm">
                            <i class="fas fa-file-invoice-dollar text-[10px]"></i>
                        </div>
                        <div>
                            <span class="font-bold text-gray-700 block">' . e($row->name) . '</span>
                            <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">' . ($row->description ?: 'No Description') . '</span>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'amount',
                'label' => 'UNIT AMOUNT',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1.5 rounded-xl inline-flex items-center gap-1 border border-emerald-100 shadow-sm">
                                <span class="text-xs font-medium text-emerald-500">₹</span>
                                <span class="tracking-tight">' . number_format($row->amount, 2) . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'updated_at',
                'label' => 'LAST UPDATED',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-[12px] font-medium">' . $row->updated_at->format('M d, Y') . '</div>';
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
                        'amount' => $row->amount,
                        'description' => $row->description,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-misc-fee', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-misc-fee', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-misc-fee.window="openEditModal($event.detail)" 
         x-on:open-delete-misc-fee.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$fees"
            :actions="$tableActions"
            empty-message="No miscellaneous fees found"
            empty-icon="fas fa-coins"
        >
            Miscellaneous Fees List
        </x-data-table>
    </div>

    <!-- Add/Edit Fee Modal -->
    <x-modal name="misc-fee-modal" alpineTitle="editMode ? 'Edit Fee Details' : 'Add New Miscellaneous Fee'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8 space-y-6">
                <!-- Fee Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Fee Particulars <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                            <i class="fas fa-signature text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="name" 
                            x-model="formData.name"
                            @input="if(errors.name) delete errors.name"
                            placeholder="e.g., ID Card Processing"
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

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Fee Amount <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                            <span class="text-sm font-bold">₹</span>
                        </div>
                        <input 
                            type="number" 
                            name="amount" 
                            step="0.01"
                            x-model="formData.amount"
                            @input="if(errors.amount) delete errors.amount"
                            placeholder="0.00"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 font-medium"
                            :class="{'border-red-500 ring-red-500/10': errors.amount}"
                        >
                    </div>
                    <div class="min-h-[24px] mt-1 ml-1">
                        <template x-if="errors.amount">
                            <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="errors.amount[0]"></span>
                            </p>
                        </template>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Additional Notes</label>
                    <div class="relative group">
                        <textarea 
                            name="description" 
                            x-model="formData.description"
                            rows="3"
                            placeholder="Optional explanation of what this fee covers..."
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 placeholder:text-gray-400 resize-none font-medium"
                        ></textarea>
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
                    <span x-text="editMode ? (submitting ? 'Saving...' : 'Update Fee') : (submitting ? 'Adding...' : 'Add Fee')"></span>
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
    Alpine.data('miscFeeManagement', () => ({
        editMode: false,
        feeId: null,
        submitting: false,
        errors: {},
        formData: {
            name: '',
            amount: '',
            description: ''
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/miscellaneous-fees/${this.feeId}` 
                : '{{ route('school.miscellaneous-fees.store') }}';
            
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
            this.feeId = null;
            this.errors = {};
            this.formData = { name: '', amount: '', description: '' };
            this.$dispatch('open-modal', 'misc-fee-modal');
        },
        
        openEditModal(fee) {
            this.editMode = true;
            this.feeId = fee.id;
            this.errors = {};
            this.formData = {
                name: fee.name,
                amount: fee.amount,
                description: fee.description || ''
            };
            this.$dispatch('open-modal', 'misc-fee-modal');
        },

        async confirmDelete(fee) {
            if (window.confirm(`Are you sure you want to delete the miscellaneous fee "${fee.name}"?`)) {
                try {
                    const response = await fetch(`/school/miscellaneous-fees/${fee.id}`, {
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
            this.$dispatch('close-modal', 'misc-fee-modal');
        }
    }));
});
</script>
@endpush
@endsection
