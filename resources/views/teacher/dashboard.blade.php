@extends('layouts.teacher')

@section('title', $title ?? 'Teacher Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-[#1a237e] to-indigo-600 rounded-2xl shadow-lg p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-bold mb-2">Welcome back, {{ $teacher->first_name ?? Auth::user()->name ?? 'Teacher' }}!</h2>
            <p class="text-indigo-100 text-lg">{{ now()->format('l, d M Y') }} &middot; Here's what's on for today.</p>
        </div>
        <i class="fas fa-chalkboard-teacher absolute -right-4 -bottom-8 text-white/10" style="font-size: 12rem;"></i>
    </div>

    {{-- Stat tiles --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Today's Attendance --}}
        <a href="{{ route('teacher.attendance.index') }}"
           class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-all block">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today's Attendance</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        {{ $stats['classes_marked_today'] }}<span class="text-base text-gray-400 font-medium">/{{ $stats['classes_assigned'] }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">classes marked</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3 text-xs">
                <span class="text-emerald-600 font-semibold"><i class="fas fa-check-circle mr-1"></i>{{ $stats['present_today'] }} present</span>
                <span class="text-rose-600 font-semibold"><i class="fas fa-times-circle mr-1"></i>{{ $stats['absent_today'] }} absent</span>
            </div>
        </a>

        {{-- My Students --}}
        <a href="{{ route('teacher.students.index') }}"
           class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-all block">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">My Students</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['students']) }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">across {{ $stats['classes_assigned'] }} {{ \Illuminate\Support\Str::plural('class', $stats['classes_assigned']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-4 text-xs text-indigo-600 font-medium">View roster <i class="fas fa-arrow-right ml-1"></i></div>
        </a>

        {{-- Pending Marks --}}
        <a href="{{ route('teacher.marks.index') }}"
           class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-all block">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Marks</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['pending_marks'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">of {{ $stats['open_assignments'] }} open {{ \Illuminate\Support\Str::plural('assignment', $stats['open_assignments']) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-pen-nib"></i>
                </div>
            </div>
            <div class="mt-4 text-xs text-indigo-600 font-medium">Enter marks <i class="fas fa-arrow-right ml-1"></i></div>
        </a>

        {{-- Today's Schedule --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today's Periods</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['today_periods'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">scheduled for {{ now()->format('l') }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="mt-4 text-xs text-gray-500">
                @if($stats['today_periods'] > 0)
                    Next: {{ optional($todaysSchedule->first())->start_time?->format('H:i') ?? '—' }}
                @else
                    No periods today
                @endif
            </div>
        </div>
    </div>

    {{-- Two-column: Today's Schedule + Pending Mark Entries --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Today's Schedule --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-calendar-day text-indigo-500"></i>
                    Today's Schedule
                </h3>
                <span class="text-xs text-gray-500">{{ now()->format('l, d M') }}</span>
            </div>
            @if($todaysSchedule->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i class="fas fa-mug-hot text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm text-gray-500">No periods scheduled for today.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($todaysSchedule as $period)
                        <div class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <div class="w-16 text-xs font-bold text-indigo-600 tabular-nums">
                                {{ optional($period->start_time)->format('H:i') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                    {{ optional($period->subject)->name ?? 'Subject' }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ optional($period->class)->name ?? '—' }}
                                    @if($period->section) &middot; Section {{ $period->section->name }} @endif
                                    @if($period->room_number) &middot; Room {{ $period->room_number }} @endif
                                </div>
                            </div>
                            <div class="text-[10px] font-bold text-gray-400 tabular-nums">
                                {{ optional($period->start_time)->format('H:i') }}–{{ optional($period->end_time)->format('H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pending Mark Entries --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-pen-nib text-amber-500"></i>
                    Open Mark Entries
                </h3>
                @if($openAssignments->isNotEmpty())
                    <a href="{{ route('teacher.marks.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        View all
                    </a>
                @endif
            </div>
            @if($openAssignments->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i class="fas fa-clipboard-check text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm text-gray-500">No open mark-entry assignments.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($openAssignments as $assignment)
                        <a href="{{ route('teacher.marks.entry', ['exam_id' => $assignment->exam_id, 'exam_subject_id' => $assignment->id]) }}"
                           class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ $assignment->exam?->display_name ?? 'Exam' }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5 truncate">
                                {{ $assignment->resolved_name }} &middot; {{ $assignment->exam?->class?->name ?? '—' }}
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
