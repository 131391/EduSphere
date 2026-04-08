@extends('layouts.school')

@section('title', 'Collect Fees - ' . $student->full_name)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Collect Fees</h1>
        <p class="text-gray-600">{{ $student->full_name }} ({{ $student->admission_no }}) - {{ $student->class->name ?? '' }}</p>
    </div>
    <a href="{{ route('fee-payments.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back to Search
    </a>
</div>

<form action="{{ route('fee-payments.store', $student) }}" method="POST" id="payment-form">
    @csrf
    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Fees Selection -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Pending Fee Heads</h3>
                    <div class="text-xs text-gray-500 font-medium">Select heads to pay</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="fees-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="select-all" class="rounded text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Head</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Amt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paying</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pendingFees as $fee)
                            <tr class="fee-row">
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="selected_fees[]" value="{{ $fee->id }}" class="fee-checkbox rounded text-blue-600" data-amount="{{ $fee->due_amount }}">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $fee->feeName->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $fee->fee_period }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold" data-due="{{ $fee->due_amount }}">
                                    ₹ {{ number_format($fee->due_amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative rounded-md shadow-sm w-32">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₹</span>
                                        </div>
                                        <input type="number" name="payments[{{ $fee->id }}][amount]" 
                                               class="fee-amount-input focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-3 sm:text-sm border-gray-300 rounded-md" 
                                               value="{{ $fee->due_amount }}" step="0.01" min="0" max="{{ $fee->due_amount }}" disabled>
                                        <input type="hidden" name="payments[{{ $fee->id }}][fee_id]" value="{{ $fee->id }}">
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">No pending fees for this student.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Details Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-600">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-receipt mr-2 text-blue-600"></i> Payment Details
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount Payable</label>
                        <div class="text-3xl font-black text-gray-900" id="total-display">₹ 0.00</div>
                    </div>

                    <div class="border-t pt-4">
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" required value="{{ date('Y-m-d') }}" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="payment_method_id" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="payment_method_id" id="payment_method_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">Transaction ID / Cheque No</label>
                        <input type="text" name="transaction_id" id="transaction_id" placeholder="Optional" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <button type="submit" id="submit-btn" disabled class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed mt-4">
                        <i class="fas fa-check-circle mr-2"></i> RECORD PAYMENT
                    </button>
                    <p class="text-xs text-center text-gray-500 italic mt-2">Selecting fees activates the button.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.fee-checkbox');
    const selectAll = document.getElementById('select-all');
    const totalDisplay = document.getElementById('total-display');
    const submitBtn = document.getElementById('submit-btn');

    function updateTotal() {
        let total = 0;
        let count = 0;
        checkboxes.forEach(cb => {
            const row = cb.closest('tr');
            const input = row.querySelector('.fee-amount-input');
            if (cb.checked) {
                total += parseFloat(input.value || 0);
                input.disabled = false;
                count++;
            } else {
                input.disabled = true;
            }
        });
        totalDisplay.textContent = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
        submitBtn.disabled = count === 0;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateTotal);
    });

    const amountInputs = document.querySelectorAll('.fee-amount-input');
    amountInputs.forEach(input => {
        input.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('max'));
            if (parseFloat(this.value) > max) this.value = max;
            updateTotal();
        });
    });

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateTotal();
        });
    }
});
</script>
@endsection
