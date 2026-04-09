@extends('layouts.parent')

@section('title', 'Fee Detail')
@section('page-title', 'Fee Detail')

@section('content')
<div class="space-y-6">
    <a href="{{ route('parent.fees.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        <i class="fas fa-arrow-left"></i> Back to Fees
    </a>

    <!-- Fee Summary Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    {{ optional($fee->feeName)->name ?? 'Fee' }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Student: <span class="font-medium text-gray-700 dark:text-gray-300">{{ optional($fee->student)->full_name ?? '—' }}</span>
                    @if($fee->student)
                    &middot; {{ optional($fee->student->class)->name }} {{ optional($fee->student->section)->name }}
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ optional($fee->feeType)->name ?? '' }}
                    @if($fee->fee_period)&middot; {{ $fee->fee_period }}@endif
                    @if($fee->due_date)&middot; Due: {{ $fee->due_date->format('d M Y') }}@endif
                </p>
            </div>
            @php
                $label = $fee->payment_status?->label() ?? 'Pending';
                $colors = ['Paid' => 'green', 'Partial' => 'blue', 'Pending' => 'yellow', 'Overdue' => 'red'];
                $color = $colors[$label] ?? 'gray';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold self-start
                bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-300">
                {{ $label }}
            </span>
        </div>

        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Payable</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white">₹{{ number_format($fee->payable_amount, 2) }}</p>
            </div>
            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-xs text-green-600 dark:text-green-400">Paid</p>
                <p class="text-lg font-bold text-green-700 dark:text-green-300">₹{{ number_format($fee->paid_amount ?? 0, 2) }}</p>
            </div>
            @if(($fee->waiver_amount ?? 0) > 0)
            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-xs text-blue-600 dark:text-blue-400">Waiver</p>
                <p class="text-lg font-bold text-blue-700 dark:text-blue-300">₹{{ number_format($fee->waiver_amount, 2) }}</p>
            </div>
            @endif
            <div class="text-center p-3 {{ ($fee->due_amount ?? 0) > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-700/50' }} rounded-lg">
                <p class="text-xs {{ ($fee->due_amount ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">Balance Due</p>
                <p class="text-lg font-bold {{ ($fee->due_amount ?? 0) > 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                    ₹{{ number_format($fee->due_amount ?? 0, 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">Payment History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Receipt No.</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $payment->receipt_no }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ optional($payment->paymentMethod)->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-right font-semibold text-green-600 dark:text-green-400">₹{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">No payments recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
