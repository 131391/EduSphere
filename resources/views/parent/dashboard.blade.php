@extends('layouts.parent')

@section('title', 'Parent Dashboard')
@section('page-title', 'Parent Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-indigo-50 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-home text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}!</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Here is a quick overview of your children's academics and financials.</p>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Enrolled Children -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-indigo-50 dark:border-gray-700 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Enrolled Children</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white">{{ $stats['total_children'] }}</p>
            </div>
        </div>
        
        <!-- Total Dues -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-rose-50 dark:border-rose-900/30 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Dues</p>
                <p class="text-3xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($stats['total_due'], 2) }}</p>
            </div>
        </div>
        
        <!-- Avg Attendance -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-emerald-50 dark:border-emerald-900/30 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-check text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Avg Attendance</p>
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['avg_attendance'] }}%</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Fees -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-receipt text-indigo-500"></i> Upcoming Dues
                </h3>
                <a href="{{ route('parent.fees.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View All &rarr;</a>
            </div>
            <div class="p-0">
                @if($stats['upcoming_fees']->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3 block"></i>
                        <p>No pending fees. All caught up!</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($stats['upcoming_fees'] as $fee)
                            <li class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center font-bold">
                                            {{ substr($fee->student_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $fee->feeName->name ?? 'Fee' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $fee->student_name }} &bull; Due: {{ \Carbon\Carbon::parse($fee->due_date)->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-rose-600 dark:text-rose-400">₹{{ number_format($fee->due_amount, 2) }}</p>
                                        <a href="{{ route('parent.fees.show', $fee->id) }}" class="text-[10px] uppercase tracking-wider font-bold text-indigo-600 hover:underline">Pay Now</a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Recent Results -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-trophy text-emerald-500"></i> Recent Results
                </h3>
                <a href="{{ route('parent.results.index') }}" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">View All &rarr;</a>
            </div>
            <div class="p-0">
                @if($stats['recent_results']->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-file-alt text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                        <p>No recent exam results published yet.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($stats['recent_results'] as $result)
                            <li class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center font-bold">
                                            {{ substr($result->student_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $result->exam->name ?? 'Exam' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $result->student_name }} &bull; {{ $result->subject->name ?? 'Subject' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $gradeColor = $result->percentage >= 80 ? 'text-emerald-600' : ($result->percentage >= 60 ? 'text-amber-500' : 'text-rose-500');
                                        @endphp
                                        <p class="text-sm font-bold {{ $gradeColor }}">{{ $result->percentage }}%</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Grade: {{ $result->grade }}</p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
