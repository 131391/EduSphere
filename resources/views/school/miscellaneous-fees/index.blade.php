@extends('layouts.school')

@section('title', 'Miscellaneous Fees')

@section('content')
<div x-data="miscFeeManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-coins text-xs"></i>
                    </div>
                    Miscellaneous Fees
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage ad-hoc and one-time fees for various services</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
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
                            <span class="font-bold text-gray-700 block uppercase tracking-tight">' . e($row->name) . '</span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">' . ($row->description ?: 'No Description') . '</span>
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
                                <span class="text-xs font-bold text-emerald-500">₹</span>
                                <span class="tracking-tight">' . number_format($row->amount, 2) . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'updated_at',
                'label' => 'LAST UPDATED',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-[12px] font-bold uppercase tracking-wider">' . $row->updated_at->format('M d, Y') . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-teal-600 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $encoded = json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'amount' => $row->amount,
                        'description' => $row->description,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-misc-fee', { detail: $encoded }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-misc-fee', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
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
    <x-modal name="misc-fee-modal" alpineTitle="editMode ? 'Edit Fee Details' : 'Add New Miscellaneous Fee'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Fee Name -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Fee Particulars <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        @input="clearError('name')"
                        placeholder="e.g., ID Card Processing"
                        class="modal-input-premium pl-4"
                        :class="{'border-red-500 ring-red-500/10': errors.name}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-teal-500">
                        <i class="fas fa-signature text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.name">
                    <p class="modal-error-message" x-text="errors.name[0]"></p>
                </template>
            </div>

            <!-- Amount -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Fee Amount <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01"
                        x-model="formData.amount"
                        @input="clearError('amount')"
                        placeholder="0.00"
                        class="modal-input-premium pl-4"
                        :class="{'border-red-500 ring-red-500/10': errors.amount}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                        <span class="text-sm font-bold">₹</span>
                    </div>
                </div>
                <template x-if="errors.amount">
                    <p class="modal-error-message" x-text="errors.amount[0]"></p>
                </template>
            </div>

            <!-- Description -->
            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Additional Notes</label>
                <textarea 
                    name="description" 
                    x-model="formData.description"
                    rows="3"
                    placeholder="Optional explanation of what this fee covers..."
                    class="modal-input-premium pl-4 resize-none !h-auto font-medium"
                ></textarea>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px] bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 shadow-teal-100">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Fee' : 'Save Fee'"></span>
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
            if (this.submitting) return;
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
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Miscellaneous Fee',
                    message: `Are you sure you want to delete the miscellaneous fee "${fee.name}"? This action cannot be undone.`,
                    callback: async () => {
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
            this.$dispatch('close-modal', 'misc-fee-modal');
        }
    }));
});
</script>
@endpush
@endsection
