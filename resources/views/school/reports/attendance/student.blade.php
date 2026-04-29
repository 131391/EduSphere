@extends('layouts.school')

@section('title', 'Student Attendance History')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <x-page-header
        title="Student Attendance History"
        description="Search and view individual student attendance records for the current academic year."
        icon="fas fa-user-clock">
        @if($history)
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 transition-colors shadow-sm no-print">
            <i class="fas fa-print"></i> Print
        </button>
        @endif
    </x-page-header>

    {{-- Search Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 no-print">
        <form action="{{ route('school.reports.attendance.student') }}" method="GET"
            class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Student</label>
                <select name="student_id" required
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm px-3 py-2.5 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">— Select Student —</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                            {{ $student->admission_no }} — {{ $student->full_name }}
                            ({{ $student->class->name ?? '' }} {{ $student->section->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class="fas fa-search"></i> View History
                </button>
            </div>
        </form>
    </div>

    @if($history)
    @php
        $student     = $students->firstWhere('id', $studentId);
        $total       = $history->count();
        $present     = $history->filter(fn($r) => $r->status === \App\Enums\AttendanceStatus::Present)->count();
        $absent      = $history->filter(fn($r) => $r->status === \App\Enums\AttendanceStatus::Absent)->count();
        $leave       = $total - $present - $absent;
        $percentage  = $total > 0 ? round(($present / $total) * 100, 1) : 0;
    @endphp

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 no-print">
        <x-stat-card label="Total Days" :value="$total" icon="fas fa-calendar-alt" color="indigo" />
        <x-stat-card label="Present" :value="$present" icon="fas fa-check-circle" color="emerald" />
        <x-stat-card label="Absent" :value="$absent" icon="fas fa-times-circle" color="rose" />
        <x-stat-card label="Attendance %" :value="$percentage . '%'" icon="fas fa-chart-pie"
            :color="$percentage >= 75 ? 'emerald' : 'rose'" />
    </div>

    {{-- Student Info + History Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        {{-- Student Header --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-lg border border-indigo-200 dark:border-indigo-700">
                {{ substr($student->full_name, 0, 1) }}
            </div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ $student->full_name }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Admission No: <span class="font-mono font-bold">{{ $student->admission_no }}</span>
                    &bull; {{ $student->class->name ?? '' }} &mdash; {{ $student->section->name ?? '' }}
                </p>
            </div>
            {{-- Attendance bar --}}
            <div class="hidden md:block w-48">
                <div class="flex justify-between text-xs font-bold mb-1">
                    <span class="text-gray-500 dark:text-gray-400">Attendance</span>
                    <span class="{{ $percentage >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all {{ $percentage >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}"
                        style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        </div>

        {{-- History Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/70 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Day</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($history as $record)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 whitespace-nowrap font-semibold text-gray-800 dark:text-gray-100">
                            {{ $record->date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                            {{ $record->date->format('l') }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-center">
                            @if($record->status === \App\Enums\AttendanceStatus::Present)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                    <i class="fas fa-check text-[8px]"></i> Present
                                </span>
                            @elseif($record->status === \App\Enums\AttendanceStatus::Absent)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800">
                                    <i class="fas fa-times text-[8px]"></i> Absent
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800">
                                    <i class="fas fa-clock text-[8px]"></i> Leave
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 italic text-sm">
                            {{ $record->remarks ?: '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <i class="fas fa-calendar-times text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No attendance records found for this student.</p>
                        </td>
                    </tr>
                    @endforelse
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
    body { background: white !important; }
}
</style>
@endpush
@endsection
