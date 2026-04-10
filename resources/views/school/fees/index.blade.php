@extends('layouts.school')

@section('title', 'Fee Inventory Ledger')

@section('content')
<div x-data="feeInventoryManager()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-file-invoice text-xs"></i>
                    </div>
                    Fee Inventory Ledger
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review and manage generated fees, payment statuses, and student obligations</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('school.fees.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Generate New Fees
                </a>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student',
                'label' => 'BENEFICIARY',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 font-bold text-xs ring-2 ring-emerald-50 ring-offset-1">
                            ' . substr($row->student->full_name, 0, 1) . '
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">' . e($row->student->full_name) . '</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">' . e($row->student->admission_no) . ' | ' . e($row->class->name ?? 'N/A') . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'fee_name',
                'label' => 'FEE PARTICULARS',
                'render' => function($row) {
                    return '
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-700">' . e($row->feeName->name ?? 'N/A') . '</span>
                        <span class="text-[10px] font-medium text-emerald-500 uppercase tracking-tighter">' . e($row->fee_period) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'payable_amount',
                'label' => 'OBLIGATION',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1 rounded-lg inline-block border border-emerald-100">
                                <span class="text-xs mr-0.5 font-medium">₹</span>' . number_format($row->payable_amount, 2) . '
                            </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'FULFILLMENT',
                'render' => function($row) {
                    if($row->payment_status->value === 3) {
                        return '<span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-emerald-200">Full Paid</span>';
                    } elseif($row->payment_status->value === 1) {
                         return '<span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-amber-100">Unpaid</span>';
                    } else {
                         return '<span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-red-100">Overdue</span>';
                    }
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'icon' => 'fas fa-eye',
                'class' => 'text-emerald-600 hover:text-emerald-800 bg-emerald-50 p-2 rounded-lg transition-colors',
                'url' => fn($row) => route('school.fees.show', $row),
                'title' => 'View Details',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-fee-record', { detail: { id: " . $row->id . ", name: 'Record for " . addslashes($row->student->full_name) . "' } }))";
                },
                'title' => 'Delete Record',
            ],
        ];
    @endphp

    <div x-on:open-delete-fee-record.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$fees"
            :actions="$tableActions"
            empty-message="No fee records available in history"
            empty-icon="fas fa-file-invoice"
        >
            Fee Transaction Ledger
        </x-data-table>
    </div>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('feeInventoryManager', () => ({
        confirmDelete(record) {
            this.$dispatch('open-confirm-modal', {
                title: 'Confirm Record Deletion',
                message: `Are you sure you want to permanently remove this fee record? This action cannot be undone.`,
                confirmLabel: 'Delete Record',
                confirmClass: 'bg-red-600 hover:bg-red-700',
                onConfirm: async () => {
                    try {
                        const response = await fetch(`/school/fees/${record.id}`, {
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
                            if (window.Toast) {
                                window.Toast.fire({
                                    icon: 'success',
                                    title: result.message
                                });
                            }
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            throw new Error(result.message || 'Deletion failed');
                        }
                    } catch (error) {
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'error',
                                title: error.message
                            });
                        }
                    }
                }
            });
        }
    }));
});
</script>
@endpush
@endsection
