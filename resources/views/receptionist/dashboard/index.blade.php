@php
    use App\Enums\VisitorStatus;
@endphp
@extends('layouts.receptionist')

@section('title', 'Administrative Nerve Center')

@section('content')
<div class="space-y-6">
    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Collection Stat -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-t-4 border-emerald-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Total Assets</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">₹ {{ number_format($stats['total_collection'], 0) }}</h3>
                </div>
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet text-lg"></i>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Today</span>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">₹ {{ number_format($stats['today_collection'], 0) }}</span>
            </div>
        </div>

        <!-- Admission Stat -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-t-4 border-blue-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Enrollments</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_admission']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 text-blue-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-plus text-lg"></i>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">New Today</span>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $stats['today_admission'] }}</span>
            </div>
        </div>

        <!-- Enquiry Stat -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-t-4 border-amber-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Lead Matrix</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_enquiry']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/20 text-amber-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-search-dollar text-lg"></i>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Active Today</span>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $stats['today_enquiry'] }}</span>
            </div>
        </div>

        <!-- Academic Classes Stat -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-t-4 border-rose-500 transition-all duration-300 hover:shadow-md group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Unit Clusters</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['running_classes'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-rose-100 dark:bg-rose-900/20 text-rose-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-school text-lg"></i>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Sections</span>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $stats['total_sections'] }}</span>
            </div>
        </div>
    </div>

    <!-- Premium Greeting Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-teal-100/50 dark:border-gray-700 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 -mr-16 -mt-16 w-64 h-64 bg-teal-50/30 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center text-teal-600 shadow-sm">
                    <i class="fas fa-hand-sparkles text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight">
                        Welcome back, <span class="text-teal-600 truncate">{{ Auth::user()->name }}</span>
                    </h1>
                    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-1 font-medium">Front Desk Intelligence Matrix • {{ now()->format('l, jS F Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-5 py-2.5 bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-900/30 rounded-xl flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></div>
                    <span class="text-[11px] font-bold text-teal-700 dark:text-teal-400 uppercase tracking-widest">Active Session</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Visitor Activity Table -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-7 py-4 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-teal-100 flex items-center justify-center text-teal-500 shadow-sm">
                        <i class="fas fa-bolt-lightning text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Visitor Synchronization</h3>
                        <p class="text-[10px] font-semibold text-gray-400 mt-0.5">Real-time occupancy tracking</p>
                    </div>
                </div>
                <a href="{{ route('receptionist.visitors.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-[10px] font-bold uppercase tracking-wider text-teal-600 rounded-lg hover:bg-teal-50 transition-colors shadow-sm">Analyze All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/30 dark:bg-gray-700/10">
                            <th class="text-left px-7 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Entry ID</th>
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Subject Identity</th>
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Protocol Stance</th>
                            <th class="text-right px-7 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Logged At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-xs text-gray-700 dark:text-gray-300">
                        @forelse($recentVisitors as $visitor)
                        <tr class="hover:bg-teal-50/20 dark:hover:bg-teal-900/5 transition-colors group">
                            <td class="px-7 py-4">
                                <span class="font-bold text-gray-400 group-hover:text-teal-500 transition-colors tracking-tighter">{{ $visitor->visitor_no }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $visitor->name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $visitor->mobile }} • {{ $visitor->visit_purpose ?? 'Institutional' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $theme = match($visitor->status) {
                                        VisitorStatus::Scheduled => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'dot' => 'bg-blue-500'],
                                        VisitorStatus::CheckedIn => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'dot' => 'bg-amber-500'],
                                        VisitorStatus::Completed => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'dot' => 'bg-emerald-500'],
                                        VisitorStatus::Cancelled => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100', 'dot' => 'bg-rose-500'],
                                        default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-100', 'dot' => 'bg-gray-500'],
                                    };
                                @endphp
                                <div class="inline-flex items-center gap-2 {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['border'] }} border px-3 py-1 rounded-full shadow-sm">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $theme['dot'] }}"></div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">{{ $visitor->status->label() }}</span>
                                </div>
                            </td>
                            <td class="px-7 py-4 text-right">
                                <span class="font-semibold text-gray-400 text-[10px]">{{ $visitor->created_at->format('d M • H:i') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-16 text-center">
                                <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-gray-100">
                                    <i class="fas fa-inbox text-xl text-gray-200"></i>
                                </div>
                                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest">Quiet Zone Detected</h4>
                                <p class="text-[10px] text-gray-300 mt-1">No visitor activity records found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Secondary Insights / Visitor Segment -->
        <div class="space-y-6">
            <div class="bg-gray-900 rounded-xl p-6 shadow-xl shadow-gray-900/10 relative overflow-hidden group">
                <div class="absolute right-0 bottom-0 -mb-8 -mr-8 w-48 h-48 bg-teal-500/10 rounded-full blur-2xl group-hover:scale-110 transition-transform duration-700"></div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6 flex items-center gap-2 relative z-10">
                    <i class="fas fa-project-diagram text-teal-400"></i>
                    Traffic Analytics
                </h3>
                
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div class="bg-white/5 border border-white/10 rounded-xl p-3.5 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-bold text-teal-400 uppercase tracking-widest mb-1.5">Total Load</p>
                        <p class="text-xl font-bold text-white tabular-nums">{{ $visitorStats['total'] }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-3.5 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1.5">Online</p>
                        <p class="text-xl font-bold text-white tabular-nums">{{ $visitorStats['online'] }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-3.5 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-1.5">Campus</p>
                        <p class="text-xl font-bold text-white tabular-nums">{{ $visitorStats['offline'] }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-3.5 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1.5">Exceptions</p>
                        <p class="text-xl font-bold text-white tabular-nums">{{ $visitorStats['cancelled'] }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-white/5 relative z-10">
                    <div class="flex items-center justify-between text-[10px] font-bold text-white/40 uppercase tracking-widest mb-2">
                        <span>Efficiency Range</span>
                        <span class="text-teal-400">98.4%</span>
                    </div>
                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 w-[98.4%] shadow-[0_0_10px_rgba(20,184,166,0.3)]"></div>
                    </div>
                </div>
            </div>

            <!-- Call to Action Card -->
            <div class="bg-gradient-to-br from-teal-600 to-emerald-700 rounded-xl p-6 shadow-xl shadow-teal-700/20 relative overflow-hidden group border border-teal-500/50">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-125 transition-transform duration-700">
                    <i class="fas fa-headset text-8xl text-white"></i>
                </div>
                <h4 class="text-white font-bold text-lg tracking-tight relative z-10">Operational Mastery</h4>
                <p class="text-teal-50/70 text-xs font-medium mt-2 leading-relaxed relative z-10">Absolute precision in institutional protocol synchronization.</p>
                <div class="mt-6 relative z-10">
                    <button class="w-full bg-white text-teal-700 font-bold text-[10px] uppercase tracking-widest py-3 rounded-xl shadow-lg hover:bg-teal-50 active:scale-95 transition-all">Execute Audit Protocol</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
