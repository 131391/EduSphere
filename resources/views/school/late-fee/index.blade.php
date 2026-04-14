@extends('layouts.school')

@section('title', 'Late Fee Management')

@section('content')
<div x-data="lateFeeManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-clock text-xs"></i>
                    </div>
                    Late Fee Management
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure automatic fines for late fee payments</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Update Late Fee
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'fine_date',
                'label' => 'FINE DATE',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <span class="text-[10px] font-bold">' . e($row->fine_date) . '</span>
                        </div>
                        <span class="font-bold text-gray-700 underline decoration-emerald-200 underline-offset-4">Day ' . e($row->fine_date) . ' of Month</span>
                    </div>';
                }
            ],
            [
                'key' => 'late_fee_amount',
                'label' => 'FINE AMOUNT',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1 rounded-lg inline-block border border-emerald-100">
                                <span class="text-xs mr-0.5">₹</span>' . number_format($row->late_fee_amount, 2) . '
                            </div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'CONFIGURED ON',
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
                        'fine_date' => $row->fine_date,
                        'late_fee_amount' => $row->late_fee_amount,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-late-fee', { detail: $encoded }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = "Day " . $row->fine_date;
                    return "window.dispatchEvent(new CustomEvent('open-delete-late-fee', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div x-on:open-edit-late-fee.window="openEditModal($event.detail)" 
         x-on:open-delete-late-fee.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$lateFees"
            :actions="$tableActions"
            empty-message="No late fee rules configured"
            empty-icon="fas fa-hourglass-end"
        >
            Late Fee Rules
        </x-data-table>
    </div>

    <!-- Update Modal -->
    <x-modal name="late-fee-modal" alpineTitle="editMode ? 'Edit Late Fee Rule' : 'Add Late Fee Rule'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Late Fee Amount -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Late Fine Amount <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="number" 
                        name="late_fee_amount" 
                        x-model="formData.late_fee_amount"
                        @input="clearError('late_fee_amount')"
                        step="0.01"
                        placeholder="0.00"
                        class="modal-input-premium pl-4"
                        :class="{'border-red-500 ring-red-500/10': errors.late_fee_amount}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                        <span class="text-sm font-bold">₹</span>
                    </div>
                </div>
                <template x-if="errors.late_fee_amount">
                    <p class="modal-error-message" x-text="errors.late_fee_amount[0]"></p>
                </template>
            </div>

            <!-- Fine Date -->
            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Select Late Fine Date <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <select 
                        name="fine_date" 
                        x-model="formData.fine_date"
                        @change="clearError('fine_date')"
                        class="modal-input-premium pl-4 appearance-none"
                        :class="{'border-red-500 ring-red-500/10': errors.fine_date}"
                    >
                        <option value="">Select Day of Month</option>
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">Day {{ $i }}</option>
                        @endfor
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                        <i class="fas fa-calendar-day text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.fine_date">
                    <p class="modal-error-message" x-text="errors.fine_date[0]"></p>
                </template>
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
                    <span x-text="editMode ? 'Update Rule' : 'Save Rule'"></span>
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
    Alpine.data('lateFeeManagement', () => ({
        editMode: false,
        feeId: null,
        submitting: false,
        errors: {},
        formData: {
            late_fee_amount: '',
            fine_date: ''
        },

        init() {
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('select[name="fine_date"]').on('change', (e) => {
                        this.formData.fine_date = e.target.value;
                        this.clearError('fine_date');
                    });
                }
            });
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/late-fee/${this.feeId}` 
                : '{{ route('school.late-fee.store') }}';
            
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
            this.formData = { late_fee_amount: '', fine_date: '' };
            this.$dispatch('open-modal', 'late-fee-modal');
        },
        
        openEditModal(fee) {
            this.editMode = true;
            this.feeId = fee.id;
            this.errors = {};
            this.formData = {
                late_fee_amount: fee.late_fee_amount,
                fine_date: String(fee.fine_date)
            };
            this.$dispatch('open-modal', 'late-fee-modal');
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        $('select[name="fine_date"]').val(this.formData.fine_date).trigger('change');
                    }
                }, 150);
            });
        },

        async confirmDelete(fee) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Late Fee Rule',
                    message: `Are you sure you want to delete the late fee rule for "${fee.name}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/late-fee/${fee.id}`, {
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
            this.$dispatch('close-modal', 'late-fee-modal');
        }
    }));
});
</script>
@endpush
@endsection

