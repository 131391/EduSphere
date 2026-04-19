@extends('layouts.parent')

@section('title', $student->full_name)
@section('page-title', 'Child Profile')

@section('content')
<div class="space-y-6">
    <a href="{{ route('parent.children.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        <i class="fas fa-arrow-left"></i> Back to My Children
    </a>

    <!-- Profile Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row gap-5 items-start sm:items-center justify-between">
            <div class="flex items-center gap-4">
                @if($student->student_photo)
                <img src="{{ asset('storage/' . $student->student_photo) }}" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" alt="">
                @else
                <div class="w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                </div>
                @endif
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $student->full_name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ optional($student->class)->name }}
                        @if($student->section)&middot; {{ $student->section->name }}@endif
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Admission No: {{ $student->admission_no }}</p>
                </div>
            </div>
            <div class="flex gap-3 flex-wrap">
                <a href="{{ route('parent.results.index', ['student_id' => $student->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 transition-colors">
                    <i class="fas fa-trophy"></i> View Results
                </a>
                <a href="{{ route('parent.fees.index', ['student_id' => $student->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-receipt"></i> View Fees
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Days Recorded</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $attendanceSummary['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Attendance %</p>
            @php $pct = $attendanceSummary['percentage']; @endphp
            <p class="text-2xl font-bold mt-1 {{ $pct >= 75 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $pct }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Total Fee Due</p>
            <p class="text-2xl font-bold mt-1 {{ $feeSummary['total_due'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                ₹{{ number_format($feeSummary['total_due'], 2) }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Total Fee Paid</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">₹{{ number_format($feeSummary['total_paid'], 2) }}</p>
        </div>
    </div>

    @if($attendanceSummary['percentage'] < 75 && $attendanceSummary['total'] > 0)
    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-400 text-sm flex items-center gap-3">
        <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
        <span>{{ $student->first_name }}'s attendance is below 75%. Please ensure regular attendance.</span>
    </div>
    @endif

    <!-- Recent Results -->
    @if($student->results->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">Recent Results</h3>
            <a href="{{ route('parent.results.index', ['student_id' => $student->id]) }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exam</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Marks</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">%</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($student->results->take(10) as $result)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 text-gray-700 dark:text-gray-300">{{ optional($result->exam)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ optional($result->subject)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $result->marks_obtained }} / {{ $result->total_marks }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $p = (float)$result->percentage; @endphp
                            <span class="font-semibold {{ $p >= 75 ? 'text-green-600 dark:text-green-400' : ($p >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $p }}%
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
    @endif
</div>
@endsection
