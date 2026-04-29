@extends('layouts.teacher')

@section('title', 'My Timetable')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-indigo-100/50 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-clock text-xs"></i>
                </div>
                My Timetable
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $total }} {{ \Illuminate\Support\Str::plural('period', $total) }} across the week.
            </p>
        </div>
        <div class="text-xs text-gray-500">
            Today: <span class="font-semibold text-indigo-600 capitalize">{{ $todayKey }}</span>
        </div>
    </div>

    @if($total === 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">No timetable assigned yet</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                Once your school admin publishes the schedule, your weekly periods will appear here.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($days as $day)
                @php
                    $periods = $byDay->get($day, collect());
                    $isToday = $day === $todayKey;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border {{ $isToday ? 'border-indigo-200 ring-2 ring-indigo-100' : 'border-gray-100 dark:border-gray-700' }} overflow-hidden">
                    <div class="px-5 py-3 border-b {{ $isToday ? 'bg-indigo-50 border-indigo-100' : 'bg-gray-50/50 dark:bg-gray-700/30 border-gray-100 dark:border-gray-700' }} flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white capitalize">{{ $day }}</h3>
                        <span class="text-[10px] font-bold {{ $isToday ? 'text-indigo-700 bg-white' : 'text-gray-500 bg-gray-100' }} px-2 py-0.5 rounded-full uppercase tracking-wider">
                            {{ $isToday ? 'Today' : $periods->count() . ' periods' }}
                        </span>
                    </div>

                    @if($periods->isEmpty())
                        <div class="px-5 py-8 text-center text-xs text-gray-400">
                            <i class="fas fa-mug-hot text-2xl text-gray-300 mb-2"></i>
                            <p>No periods.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($periods as $period)
                                <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                                {{ optional($period->subject)->name ?? 'Subject' }}
                                            </div>
                                            <div class="text-xs text-gray-500 truncate">
                                                {{ optional($period->class)->name ?? '—' }}
                                                @if($period->section) &middot; {{ $period->section->name }} @endif
                                            </div>
                                            @if($period->room_number)
                                                <div class="text-[10px] font-semibold text-indigo-500 mt-0.5">
                                                    <i class="fas fa-door-open mr-1"></i>Room {{ $period->room_number }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs font-bold text-gray-700 tabular-nums">
                                                {{ optional($period->start_time)->format('H:i') }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 tabular-nums">
                                                {{ optional($period->end_time)->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
