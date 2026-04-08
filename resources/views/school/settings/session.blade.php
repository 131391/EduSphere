@extends('layouts.school')

@section('title', 'Set Session - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500">
    <!-- Page Header (High Legibility) -->
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
                            <span>Set Session</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Academic Session</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Select the active operational year
            </p>
        </div>
    </div>



    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" x-data="{ selectedYear: '{{ $currentSessionId }}' }">
        <!-- Left: Configuration Form -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-400 to-blue-400"></div>
                
                <div class="p-8">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Session Configuration</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Select operational year</p>
                        </div>
                    </div>

                    <form action="{{ route('school.settings.session.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($academicYears as $year)
                            <label class="relative group cursor-pointer transition-all">
                                <input type="radio" name="current_session_id" value="{{ $year->id }}" class="sr-only" 
                                       x-model="selectedYear" @if($currentSessionId == $year->id) checked @endif>
                                
                                <div :class="selectedYear == '{{ $year->id }}' ? 'border-indigo-600 bg-indigo-50/30' : 'border-gray-100 bg-white hover:border-indigo-200'"
                                     class="p-5 border-2 rounded-2xl transition-all">
                                    <div class="flex items-center justify-between mb-3">
                                        <div :class="selectedYear == '{{ $year->id }}' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400'"
                                             class="w-6 h-6 rounded-full flex items-center justify-center transition-colors">
                                            <i class="fas fa-check text-[10px]" x-show="selectedYear == '{{ $year->id }}'"></i>
                                        </div>
                                        @if($year->is_current)
                                        <span class="px-2 py-0.5 bg-green-500 text-white text-[10px] font-black uppercase tracking-widest rounded-md">Active</span>
                                        @endif
                                    </div>
                                    <h4 class="text-base font-black text-gray-900 mb-1">{{ $year->name }}</h4>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">{{ $year->start_date->format('M Y') }} - {{ $year->end_date->format('M Y') }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        
                        @error('current_session_id')
                            <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                        @enderror

                        <div class="bg-amber-50 rounded-xl border border-amber-100 p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
                                </div>
                                <div class="pt-0.5">
                                    <h4 class="text-sm font-bold text-amber-900 mb-1 uppercase tracking-tight">Important Caution</h4>
                                    <p class="text-xs text-amber-800 leading-relaxed font-medium opacity-90">
                                        Changing the session affects student promotions, fee calculations, and all dashboard metrics system-wide.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end border-t border-gray-50 pt-6">
                            <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-indigo-100 flex items-center gap-3">
                                <i class="fas fa-sync-alt text-xs"></i>
                                Apply Academic Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: current Info Card (High Legibility) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <h3 class="text-base font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-fingerprint text-sm"></i>
                        </span>
                        System Overview
                    </h3>
                    
                    @if($currentSession)
                    <div class="space-y-4">
                        <div class="p-6 bg-gray-900 rounded-2xl text-white shadow-lg relative overflow-hidden group">
                            <div class="absolute -top-12 -right-12 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                            <p class="text-xs font-black text-indigo-300 uppercase tracking-widest mb-2 opacity-60">Currently Active</p>
                            <h4 class="text-2xl font-black mb-3">{{ $currentSession->name }}</h4>
                            <div class="text-xs font-bold text-indigo-100 flex items-center gap-3 opacity-80">
                                <i class="fas fa-calendar-check text-[10px]"></i>
                                {{ $currentSession->start_date->format('d M, Y') }} - {{ $currentSession->end_date->format('d M, Y') }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-indigo-100 transition-colors">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Enrolled Students</p>
                                <p class="text-lg font-black text-gray-900">{{ number_format($currentSession->students()->count()) }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-indigo-100 transition-colors">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Fee Configs</p>
                                <p class="text-lg font-black text-gray-900">{{ number_format($currentSession->fees()->count()) }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-6 bg-amber-50 rounded-2xl border border-amber-100 text-center">
                        <p class="text-sm font-bold text-amber-800">No active session defined</p>
                    </div>
                    @endif
                </div>
                
                <div class="p-6 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('school.academic-years.index') }}" class="inline-flex items-center text-xs font-black text-indigo-600 hover:text-indigo-700 transition-colors uppercase tracking-widest">
                        Master Year Management <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
