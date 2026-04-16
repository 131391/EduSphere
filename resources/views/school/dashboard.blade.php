@extends('layouts.school')

@section('title', 'Institutional Intelligence Hub')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Collection -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-emerald-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Total Collection</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">₹ {{ number_format($totalCollection, 2) }}</h3>
                    <p class="text-[10px] font-semibold text-emerald-500 mt-1">Today: ₹ {{ number_format($todayCollection, 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Admission -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Total Admission</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalAdmission) }}</h3>
                    <p class="text-[10px] font-semibold text-blue-500 mt-1">Today: {{ $todayAdmission }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 text-blue-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-graduate text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Enquiry -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-orange-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Total Enquiry</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalEnquiry) }}</h3>
                    <p class="text-[10px] font-semibold text-orange-500 mt-1">Today: {{ $todayEnquiry }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 text-orange-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-question-circle text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Running Classes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-rose-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Unit Clusters</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $runningClasses }}</h3>
                    <p class="text-[10px] font-semibold text-rose-500 mt-1">Sections: {{ $totalSections }}</p>
                </div>
                <div class="w-10 h-10 bg-rose-100 dark:bg-rose-900/20 text-rose-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Collection Category Wise Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-chart-pie text-xs"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Revenue Composition</h3>
            </div>
            <div class="flex flex-col items-center justify-center py-4">
                <div class="relative w-48 h-48 mx-auto">
                    <svg class="transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-opacity="0.2" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="8" 
                                stroke-dasharray="{{ ($totalCollection / ($totalCollection + 100000 ?: 1)) * 251.2 }} 251.2"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-black text-gray-800 dark:text-white">Analytics</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Live Data</span>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap justify-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-emerald-500 rounded-sm"></div>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Collection</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-600 rounded-sm"></div>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Outstanding</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-7 py-4 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-bolt text-xs"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Activity Ledger</h3>
                    </div>
                    <div class="relative">
                        <select class="appearance-none bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-1.5 pr-8 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm cursor-pointer outline-none transition-all">
                            <option>Fee Logs</option>
                            <option>Enquiry Trail</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[8px] text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50/20 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-7 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">BILL ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">ENTITY</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">AMT (PAID)</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($recentFees as $fee)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/5 transition-colors group">
                            <td class="px-7 py-4 font-bold text-gray-400 group-hover:text-blue-500 tracking-tighter">{{ $fee->bill_no }}</td>
                            <td class="px-4 py-4">
                                <span class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $fee->student->admission_no ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-4 tabular-nums font-bold text-gray-700 dark:text-gray-300">₹ {{ number_format($fee->paid_amount, 0) }}</td>
                            <td class="px-4 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg {{ $fee->payment_mode === 'online' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                    {{ $fee->payment_mode ?? 'CASH' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-16 text-center text-gray-400 italic">No recent activity detected</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
