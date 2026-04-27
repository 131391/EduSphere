@extends('layouts.student')

@section('title', 'My Results')
@section('page-title', 'My Results')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Exams Taken</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $summary['total_exams'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Average %</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $summary['average'] }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Highest %</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $summary['highest'] }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Lowest %</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $summary['lowest'] }}%</p>
        </div>
    </div>

    @forelse($results as $examResults)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-file-alt text-indigo-500 mr-2"></i>{{ $examResults->first()?->exam?->display_name ?? 'Exam' }}
            </h3>
            @if($examResults->first()->exam)
            <a href="{{ route('student.results.show', $examResults->first()->exam->id) }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                View Detail <i class="fas fa-arrow-right ml-1"></i>
            </a>
            @endif
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($examResults as $result)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200 font-medium">
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
        <i class="fas fa-trophy text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No results published yet.</p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Check back after your exams are evaluated.</p>
    </div>
    @endforelse
</div>
@endsection
