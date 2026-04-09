@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<!-- Page Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Overview</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time platform metrics and management console</p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center space-x-3">
        <div class="flex items-center space-x-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">System Healthy</span>
        </div>
        <button class="p-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 text-gray-500 hover:text-blue-600 transition-colors">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Schools -->
    <div class="group bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Schools</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($stats['totalSchools']) }}</h3>
                </div>
                <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/30 rounded-2xl p-4 text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-school text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span class="text-green-500 font-bold flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> 12%
                </span>
                <span class="text-gray-400 ml-2">from last month</span>
            </div>
        </div>
        <div class="h-1 bg-blue-600 w-full"></div>
    </div>

    <!-- Active Schools -->
    <div class="group bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Schools</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($stats['activeSchools']) }}</h3>
                </div>
                <div class="flex-shrink-0 bg-green-50 dark:bg-green-900/30 rounded-2xl p-4 text-green-600 dark:text-green-400 group-hover:bg-green-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-check-double text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span class="text-green-500 font-bold flex items-center">
                    <i class="fas fa-toggle-on mr-1"></i> {{ round(($stats['activeSchools'] / max($stats['totalSchools'], 1)) * 100) }}%
                </span>
                <span class="text-gray-400 ml-2">activation rate</span>
            </div>
        </div>
        <div class="h-1 bg-green-500 w-full"></div>
    </div>

    <!-- Total Users -->
    <div class="group bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff Users</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($stats['totalUsers']) }}</h3>
                </div>
                <div class="flex-shrink-0 bg-purple-50 dark:bg-purple-900/30 rounded-2xl p-4 text-purple-600 dark:text-purple-400 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-users-cog text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span class="text-purple-500 font-bold flex items-center">
                    <i class="fas fa-user-shield mr-1"></i> Admin Managed
                </span>
            </div>
        </div>
        <div class="h-1 bg-purple-600 w-full"></div>
    </div>

    <!-- Total Students -->
    <div class="group bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Students</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($stats['totalStudents']) }}</h3>
                </div>
                <div class="flex-shrink-0 bg-orange-50 dark:bg-orange-900/30 rounded-2xl p-4 text-orange-600 dark:text-orange-400 group-hover:bg-orange-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span class="text-orange-500 font-bold flex items-center">
                    <i class="fas fa-globe mr-1"></i> Across Network
                </span>
            </div>
        </div>
        <div class="h-1 bg-orange-500 w-full"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Subscription Alerts -->
    <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/40 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-calendar-times text-orange-600 dark:text-orange-400"></i>
                </div>
                Expiry Alerts
            </h3>
            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-wider rounded">Next 30 Days</span>
        </div>
        <div class="flex-1 overflow-y-auto max-h-96 sidebar-scroll p-4">
            <div class="space-y-4">
                @forelse($expiringSchools as $school)
                    <div class="flex items-center p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 hover:border-orange-300 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $school->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $school->code }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-black text-red-600 dark:text-red-400">{{ $school->subscription_end_date->format('d M') }}</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ $school->subscription_end_date->diffForHumans(['short' => true]) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-2xl opacity-20"></i>
                        </div>
                        <p class="text-sm font-medium">All systems green</p>
                        <p class="text-xs mt-1">No upcoming expiries</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-b-2xl border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('admin.schools.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 transition-colors">
                View All Schools <i class="fas fa-chevron-right ml-2 text-xs opacity-50"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions & Growth -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Quick Actions Grid -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Console Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.schools.create') }}" class="group flex items-center p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-900/30 hover:bg-blue-600 hover:border-blue-600 transition-all duration-300">
                    <div class="w-12 h-12 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center text-blue-600 shadow-sm transition-colors group-hover:text-blue-600">
                        <i class="fas fa-plus text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-white transition-colors">Onboard School</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-100 transition-colors">Provision a new instance</p>
                    </div>
                </a>
                <a href="{{ route('admin.users.index') }}" class="group flex items-center p-4 bg-purple-50/50 dark:bg-purple-900/10 rounded-2xl border border-purple-100 dark:border-purple-900/30 hover:bg-purple-600 hover:border-purple-600 transition-all duration-300">
                    <div class="w-12 h-12 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center text-purple-600 shadow-sm transition-colors group-hover:text-purple-600">
                        <i class="fas fa-id-badge text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-white transition-colors">Identity Manager</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-purple-100 transition-colors">Manage platform security</p>
                    </div>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="group flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 hover:bg-gray-800 hover:border-gray-800 transition-all duration-300">
                    <div class="w-12 h-12 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center text-gray-600 shadow-sm transition-colors group-hover:text-gray-800">
                        <i class="fas fa-terminal text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-white transition-colors">Activity Monitor</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-300 transition-colors">Deep system auditing</p>
                    </div>
                </a>
                <a href="{{ route('admin.schools.index') }}" class="group flex items-center p-4 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 hover:bg-indigo-600 hover:border-indigo-600 transition-all duration-300">
                    <div class="w-12 h-12 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center text-indigo-600 shadow-sm transition-colors group-hover:text-indigo-600">
                        <i class="fas fa-cog text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-white transition-colors">Platform Config</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-indigo-100 transition-colors">Control global settings</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Growth Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Growth Velocity</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monthly new school onboarding</p>
                </div>
                <div class="flex items-center space-x-1 text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">
                    <i class="fas fa-chart-line mr-1"></i> +8.5%
                </div>
            </div>
            <div class="flex items-end justify-between h-48 gap-3 sm:gap-6 px-2">
                @php
                    $months = array_keys($schoolGrowth);
                    $counts = array_values($schoolGrowth);
                    $max = max($counts) ?: 1;
                @endphp
                @foreach($schoolGrowth as $month => $count)
                    <div class="flex-1 flex flex-col items-center group relative cursor-pointer">
                        <!-- Tooltip -->
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                            {{ $count }} Schools
                        </div>
                        <!-- Bar -->
                        <div class="w-full relative overflow-hidden rounded-t-lg transition-all duration-500" 
                             style="height: {{ ($count / $max) * 100 }}%">
                             <div class="absolute inset-0 bg-gradient-to-t from-blue-700 to-blue-400 group-hover:from-blue-600 group-hover:to-blue-300"></div>
                             <div class="absolute inset-0 bg-white/10 dark:bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-3 uppercase tracking-tighter">{{ date('M', strtotime($month . '-01')) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-bolt text-blue-600 dark:text-blue-400"></i>
            </div>
            Platform Pulse
        </h3>
        <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center">
            DEEP ANALYTICS <i class="fas fa-external-link-alt ml-2 text-[10px]"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">User Identity</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Operation</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Object Context</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentActivity as $activity)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-7 h-7 bg-blue-100 dark:bg-blue-900/50 rounded shadow-sm flex items-center justify-center text-[10px] font-bold text-blue-700 dark:text-blue-300 mr-3">
                                    {{ strtoupper(substr($activity->causer->name ?? 'S', 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $activity->causer->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter
                                {{ $activity->description === 'created' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 
                                   ($activity->description === 'deleted' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                {{ $activity->description }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ class_basename($activity->subject_type) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-[10px] font-bold text-gray-400 dark:text-gray-500 tabular-nums">
                            {{ $activity->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                            No recent platform telemetry detected.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


@endsection

