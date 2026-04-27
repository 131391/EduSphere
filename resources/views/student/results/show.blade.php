@extends('layouts.student')

@section('title', 'Result Detail — ' . $exam->display_name)
@section('page-title', 'Result Detail')

@section('content')
<div class="space-y-6">
    <!-- Back -->
    <a href="{{ route('student.results.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        <i class="fas fa-arrow-left"></i> Back to Results
    </a>

    <!-- Exam Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $exam->display_name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $student->full_name }} &middot;
                    {{ optional($student->class)->name }} {{ optional($student->section)->name }}
                </p>
            </div>
            <div class="flex gap-4 text-center">
                <div class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                    <p class="text-xs text-indigo-500 dark:text-indigo-400">Marks Obtained</p>
                    <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ $obtainedMarks }} / {{ $totalMarks }}</p>
                </div>
                <div class="px-4 py-2 rounded-lg {{ $percentage >= 75 ? 'bg-green-50 dark:bg-green-900/20' : ($percentage >= 50 ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-red-50 dark:bg-red-900/20') }}">
                    <p class="text-xs {{ $percentage >= 75 ? 'text-green-500' : ($percentage >= 50 ? 'text-yellow-500' : 'text-red-500') }}">Percentage</p>
                    <p class="text-xl font-bold {{ $percentage >= 75 ? 'text-green-700 dark:text-green-300' : ($percentage >= 50 ? 'text-yellow-700 dark:text-yellow-300' : 'text-red-700 dark:text-red-300') }}">{{ $percentage }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject-wise Breakdown -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">Subject-wise Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Obtained</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">%</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($results as $result)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-200">
                            {{ optional($result->subject)->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $result->marks_obtained }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $result->total_marks }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $pct = (float)$result->percentage; @endphp
                            <span class="font-semibold {{ $pct >= 75 ? 'text-green-600 dark:text-green-400' : ($pct >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $pct }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                {{ $result->grade ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $result->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">No subject results found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
