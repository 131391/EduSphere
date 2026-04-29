@extends('layouts.student')

@section('title', 'My Library')
@section('page-title', 'My Borrowed Books')

@section('content')
@php
    $currency = $student->school->settings['currency_symbol'] ?? '₹';
@endphp

<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Currently Borrowed</p>
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $summary['active_count'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Overdue</p>
            <p class="text-2xl font-bold {{ $summary['overdue_count'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">{{ $summary['overdue_count'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Outstanding Fines</p>
            <p class="text-2xl font-bold {{ $summary['fines_outstanding'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">
                {{ $currency }}{{ number_format($summary['fines_outstanding'], 2) }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Past Records</p>
            <p class="text-2xl font-bold text-gray-700 dark:text-gray-300 mt-1">{{ $summary['total_history'] }}</p>
        </div>
    </div>

    <!-- Active Issues -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">Active Issues</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Due</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($issues->where('status', 'issued') as $issue)
                        @php $isOverdue = $issue->isOverdue(); @endphp
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-bold text-gray-800 dark:text-gray-100">{{ $issue->book?->title ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $issue->book?->author ?? '' }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $issue->issue_date->format('d M, Y') }}</td>
                            <td class="px-6 py-3 font-bold {{ $isOverdue ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $issue->due_date->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-3">
                                @if($isOverdue)
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-xs font-bold rounded">Overdue</span>
                                @else
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">On loan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 text-xs italic">You have no active library issues.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- History -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Returned</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($issues->whereIn('status', ['returned', 'lost']) as $issue)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-bold text-gray-700 dark:text-gray-200">{{ $issue->book?->title ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $issue->issue_date->format('d M, Y') }}</td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $issue->return_date?->format('d M, Y') ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $issue->status === 'returned' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ ucfirst($issue->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-bold">
                                @if((float) $issue->fine_amount > 0)
                                    <span class="{{ $issue->isFineSettled() ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $currency }}{{ number_format($issue->fine_amount, 2) }}
                                    </span>
                                    @if($issue->isFineSettled())
                                        <span class="ml-1 text-[9px] text-emerald-500 font-black uppercase">Paid</span>
                                    @else
                                        <span class="ml-1 text-[9px] text-rose-500 font-black uppercase">Pending</span>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs italic">No past library records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
