@extends('layouts.school')

@section('title', 'Monthly Attendance Report')

@section('content')
<div x-data="{
    classId: '{{ $classId ?? '' }}',
    sectionId: '{{ $sectionId ?? '' }}',
    monthYear: '{{ $monthYear }}',
    allSections: @js($sections->groupBy('class_id')->map->values()),
    get filteredSections() {
        return this.classId ? (this.allSections[this.classId] || []) : [];
    },
    submit() {
        if (!this.classId || !this.sectionId || !this.monthYear) return;
        const url = new URL('{{ route('school.reports.attendance.monthly') }}', window.location.origin);
        url.searchParams.set('class_id', this.classId);
        url.searchParams.set('section_id', this.sectionId);
        url.searchParams.set('month', this.monthYear);
        window.location.href = url.toString();
    }
}" class="space-y-6">

    {{-- Page Header --}}
    <x-page-header
        title="Monthly Attendance Report"
        description="View class-wise monthly attendance with day-by-day breakdown and percentage."
        icon="fas fa-calendar-check">
        @if($reportData)
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 transition-colors shadow-sm no-print">
            <i class="fas fa-print"></i> Print
        </button>
        @endif
    </x-page-header>

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 no-print">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            {{-- Class --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Class</label>
                <select x-model="classId" @change="sectionId = ''"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm px-3 py-2.5 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Section</label>
                <select x-model="sectionId"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm px-3 py-2.5 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    :disabled="!classId">
                    <option value="">Select Section</option>
                    <template x-for="sec in filteredSections" :key="sec.id">
                        <option :value="sec.id" :selected="sec.id == {{ $sectionId ?? 'null' }}" x-text="sec.name"></option>
                    </template>
                </select>
            </div>

            {{-- Month --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Month</label>
                <input type="month" x-model="monthYear"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm px-3 py-2.5 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Submit --}}
            <div>
                <button @click="submit()" :disabled="!classId || !sectionId || !monthYear"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
                    <i class="fas fa-chart-bar"></i> Generate Report
                </button>
            </div>
        </div>
    </div>

    @if($reportData)
    {{-- Summary Stats --}}
    @php
        $totalStudents = count($reportData['report']);
        $avgPercent = $totalStudents > 0
            ? round(collect($reportData['report'])->avg('percentage'), 1)
            : 0;
        $above75 = collect($reportData['report'])->filter(fn($r) => $r['percentage'] >= 75)->count();
        $below75 = $totalStudents - $above75;
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 no-print">
        <x-stat-card label="Total Students" :value="$totalStudents" icon="fas fa-users" color="indigo" />
        <x-stat-card label="Avg Attendance" :value="$avgPercent . '%'" icon="fas fa-chart-pie" color="emerald" />
        <x-stat-card label="Working Days" :value="$reportData['workingDays']" icon="fas fa-calendar-day" color="amber" />
        <x-stat-card label="Below 75%" :value="$below75" icon="fas fa-exclamation-triangle" color="rose" />
    </div>

    {{-- Report Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800 dark:text-white">
                    {{ $reportData['monthName'] }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $classes->find($classId)?->name }} &mdash; Section {{ $sections->find($sectionId)?->name }}
                    &bull; {{ $reportData['workingDays'] }} working days
                </p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold">
                <span class="flex items-center gap-1.5 text-emerald-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>P = Present</span>
                <span class="flex items-center gap-1.5 text-rose-600"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>A = Absent</span>
                <span class="flex items-center gap-1.5 text-amber-600"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>L = Leave</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap sticky left-0 bg-gray-50 dark:bg-gray-700/50 border-r border-gray-200 dark:border-gray-600 min-w-[160px]">Student</th>
                        @for($i = 1; $i <= $reportData['daysInMonth']; $i++)
                            <th class="px-1.5 py-3 text-center font-bold text-gray-500 dark:text-gray-400 w-7">{{ $i }}</th>
                        @endfor
                        <th class="px-3 py-3 text-center font-bold text-emerald-600 uppercase tracking-wider whitespace-nowrap border-l border-gray-200 dark:border-gray-600">P</th>
                        <th class="px-3 py-3 text-center font-bold text-rose-600 uppercase tracking-wider whitespace-nowrap">A</th>
                        <th class="px-3 py-3 text-center font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($reportData['report'] as $row)
                    @php
                        $pct = $row['percentage'];
                        $pctClass = $pct >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-4 py-2.5 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700 whitespace-nowrap">
                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $row['student']->full_name }}</div>
                            <div class="text-[10px] text-gray-400 font-mono">{{ $row['student']->admission_no }}</div>
                        </td>
                        @for($i = 1; $i <= $reportData['daysInMonth']; $i++)
                            @php $status = $row['days'][$i] ?? null; @endphp
                            <td class="px-1 py-2.5 text-center w-7">
                                @if($status === \App\Enums\AttendanceStatus::Present)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">P</span>
                                @elseif($status === \App\Enums\AttendanceStatus::Absent)
                                    <span class="text-rose-600 dark:text-rose-400 font-bold">A</span>
                                @elseif($status !== null)
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">L</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                @endif
                            </td>
                        @endfor
                        <td class="px-3 py-2.5 text-center font-bold text-emerald-600 dark:text-emerald-400 border-l border-gray-100 dark:border-gray-700">{{ $row['present_count'] }}</td>
                        <td class="px-3 py-2.5 text-center font-bold text-rose-600 dark:text-rose-400">{{ $reportData['workingDays'] - $row['present_count'] }}</td>
                        <td class="px-3 py-2.5 text-center font-bold {{ $pctClass }}">{{ $pct }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
@media print {
    .no-print, aside, header { display: none !important; }
    body { background: white !important; font-size: 10px; }
    table { font-size: 9px; }
    .sticky { position: static !important; }
}
</style>
@endpush
@endsection
