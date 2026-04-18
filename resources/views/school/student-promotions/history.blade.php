@extends('layouts.school')
@section('title', 'Promotion History')

@section('content')
    <div>
        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Promotion History</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Audit log of all student promotions</p>
                </div>
                <a href="{{ route('school.student-promotions.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md">
                    <i class="fas fa-plus mr-2"></i> New Promotion
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('school.student-promotions.history') }}"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Academic Year</label>
                    <select name="academic_year_id"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Student ID</label>
                    <input type="number" name="student_id" value="{{ request('student_id') }}"
                        placeholder="Filter by student ID"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('school.student-promotions.history') }}"
                       class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition-all">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            @if($history->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <i class="fas fa-history text-4xl mb-3"></i>
                    <p class="text-sm font-medium">No promotion records found</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">From Year / Class</th>
                                <th class="px-4 py-3 text-left">To Year / Class</th>
                                <th class="px-4 py-3 text-left">Result</th>
                                <th class="px-4 py-3 text-left">Promoted By</th>
                                <th class="px-4 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($history as $record)
                                @php
                                    $resultMap = [
                                        1 => ['label' => 'Promoted',    'color' => 'green'],
                                        2 => ['label' => 'Graduated',   'color' => 'blue'],
                                        3 => ['label' => 'Detained',    'color' => 'orange'],
                                        4 => ['label' => 'Transferred', 'color' => 'purple'],
                                    ];
                                    $r = $resultMap[$record->result] ?? ['label' => 'Unknown', 'color' => 'gray'];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800 dark:text-white">{{ $record->student?->full_name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400">{{ $record->student?->admission_no }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        <div>{{ $record->fromAcademicYear?->name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400">{{ $record->fromClass?->name }} / {{ $record->fromSection?->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        <div>{{ $record->toAcademicYear?->name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ $record->toClass?->name ?? 'N/A' }} / {{ $record->toSection?->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full
                                            bg-{{ $r['color'] }}-100 text-{{ $r['color'] }}-700 border border-{{ $r['color'] }}-200">
                                            {{ $r['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $record->promotedBy?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $record->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $history->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
