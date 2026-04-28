@extends('layouts.school')

@section('title', 'Academic Session - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-bold uppercase tracking-wider text-gray-400">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[11px] text-gray-300"></i>
                            <span>Academic Session</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Academic Session</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Set the active academic year for the school
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6"
         x-data="{ selected: '{{ $currentSessionId }}' }">

        {{-- Left: Session picker --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Force-confirm warning (session-based) --}}
            @if(session('warning'))
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-300">{!! session('warning') !!}</p>
                </div>
            </div>
            @endif

            <form action="{{ route('school.settings.session.update') }}" method="POST">
                @csrf
                @method('PUT')
                @if(session('force_confirm'))
                    <input type="hidden" name="force" value="1">
                @endif

                {{-- Year cards --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-calendar-alt text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Select Active Year</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Choose the year that all modules will operate under.</p>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($academicYears->isEmpty())
                            <div class="text-center py-10">
                                <i class="fas fa-calendar-times text-4xl text-gray-200 mb-3"></i>
                                <p class="text-sm text-gray-400 font-medium">No academic years found.</p>
                                <a href="{{ route('school.academic-years.index') }}"
                                   class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                    <i class="fas fa-plus text-[10px]"></i> Create one
                                </a>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($academicYears as $year)
                                <label class="cursor-pointer">
                                    <input type="radio" name="current_session_id"
                                           value="{{ $year->id }}"
                                           x-model="selected"
                                           class="sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all"
                                         :class="selected == '{{ $year->id }}'
                                             ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-900/20'
                                             : 'border-gray-100 dark:border-gray-600 hover:border-gray-200 dark:hover:border-gray-500 bg-white dark:bg-gray-700'">
                                        <div class="flex items-center justify-between mb-2">
                                            {{-- Radio indicator --}}
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all flex-shrink-0"
                                                 :class="selected == '{{ $year->id }}'
                                                     ? 'border-indigo-500 bg-indigo-500'
                                                     : 'border-gray-300 dark:border-gray-500'">
                                                <i class="fas fa-check text-white text-[8px]"
                                                   x-show="selected == '{{ $year->id }}'"></i>
                                            </div>
                                            @if($year->is_current === \App\Enums\YesNo::Yes)
                                                <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wide rounded-lg">
                                                    Active
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $year->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                            {{ $year->start_date->format('d M Y') }} — {{ $year->end_date->format('d M Y') }}
                                        </p>
                                    </div>
                                </label>
                                @endforeach
                            </div>

                            @error('current_session_id')
                                <p class="mt-3 text-xs text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </p>
                            @enderror
                        @endif
                    </div>
                </div>

                {{-- Warning notice --}}
                <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-2xl p-4 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-exclamation-triangle text-amber-500 dark:text-amber-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-300 mb-0.5">Heads up before switching</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                            Changing the active session affects student promotions, fee calculations, and all dashboard metrics school-wide. Make sure students in the current year have been promoted first.
                        </p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-check text-xs"></i>
                        Apply Session
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Current session info --}}
        <div class="space-y-5">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-check text-emerald-600"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Current Session</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Live data for the active year.</p>
                    </div>
                </div>

                <div class="p-6">
                    @if($currentSession)
                        <div class="space-y-4">
                            {{-- Session name badge --}}
                            <div class="p-4 bg-indigo-600 rounded-xl text-white">
                                <p class="text-xs font-semibold text-indigo-200 uppercase tracking-wide mb-1">Active Year</p>
                                <p class="text-xl font-black">{{ $currentSession->name }}</p>
                                <p class="text-xs text-indigo-200 mt-1">
                                    {{ $currentSession->start_date->format('d M Y') }} — {{ $currentSession->end_date->format('d M Y') }}
                                </p>
                            </div>

                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Students</p>
                                    <p class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($currentSession->students()->count()) }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Fee Configs</p>
                                    <p class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($currentSession->fees()->count()) }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i class="fas fa-calendar-times text-3xl text-gray-200 dark:text-gray-600 mb-2"></i>
                            <p class="text-sm text-gray-400 font-medium">No active session set</p>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('school.academic-years.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                        Manage academic years
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
