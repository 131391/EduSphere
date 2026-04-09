@extends('layouts.student')

@section('title', 'My Attendance')
@section('page-title', 'Attendance')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Total Days</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Present</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $summary['present'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Absent</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $summary['absent'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Attendance %</p>
            @php $pct = $summary['percentage']; @endphp
            <p class="text-2xl font-bold mt-1 {{ $pct >= 75 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                {{ $pct }}%
            </p>
        </div>
    </div>

    @if($summary['percentage'] < 75 && $summary['total'] > 0)
    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-400 text-sm flex items-center gap-3">
        <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
        <span>Your attendance is below 75%. Please regularise your attendance to avoid academic consequences.</span>
    </div>
    @endif

    <!-- Monthly Breakdown -->
    @forelse($monthly as $month => $monthRecords)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-calendar text-indigo-500 mr-2"></i>{{ $month }}
            </h3>
            @php
                $mp = $monthRecords->filter(fn($r) => $r->status?->value === 1)->count();
                $mt = $monthRecords->count();
            @endphp
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $mp }}/{{ $mt }} days present
                <span class="ml-2 font-semibold {{ $mt > 0 && ($mp/$mt*100) >= 75 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    ({{ $mt > 0 ? round($mp/$mt*100, 1) : 0 }}%)
                </span>
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($monthRecords->sortByDesc('date') as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                            {{ $record->date->format('D, d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $color = $record->status?->color() ?? 'gray'; @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                {{ $record->status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $record->remarks ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No attendance records found.</p>
    </div>
    @endforelse
</div>
@endsection
