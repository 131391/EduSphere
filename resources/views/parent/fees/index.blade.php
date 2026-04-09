@extends('layouts.parent')

@section('title', "Fee Details")
@section('page-title', 'Fee Details')

@section('content')
<div class="space-y-6">

    <!-- Child Selector -->
    @if($children->count() > 1)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('parent.fees.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Filter by Child</label>
                <select name="student_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Children</option>
                    @foreach($children as $child)
                    <option value="{{ $child->id }}" {{ $selectedChildId == $child->id ? 'selected' : '' }}>
                        {{ $child->full_name }} ({{ optional($child->class)->name }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Total Payable</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">₹{{ number_format($summary['total_payable'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Total Paid</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">₹{{ number_format($summary['total_paid'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 col-span-2 lg:col-span-1">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Balance Due</p>
            <p class="text-2xl font-bold mt-1 {{ $summary['total_due'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                ₹{{ number_format($summary['total_due'], 2) }}
            </p>
        </div>
    </div>

    <!-- Fee Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">Fee Statement</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        @if(!$selectedChildId)
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Child</th>
                        @endif
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fee Head</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payable</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($fees as $fee)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        @if(!$selectedChildId)
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs font-medium">{{ optional($fee->student)->full_name ?? '—' }}</td>
                        @endif
                        <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-200">{{ optional($fee->feeName)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $fee->fee_period ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">₹{{ number_format($fee->payable_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">₹{{ number_format($fee->paid_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ ($fee->due_amount ?? 0) > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                            ₹{{ number_format($fee->due_amount ?? 0, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $label = $fee->payment_status?->label() ?? 'Pending';
                                $colors = ['Paid' => 'green', 'Partial' => 'blue', 'Pending' => 'yellow', 'Overdue' => 'red'];
                                $color = $colors[$label] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('parent.fees.show', $fee->id) }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs font-medium">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <i class="fas fa-receipt text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <p class="text-gray-500 dark:text-gray-400">No fee records found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
