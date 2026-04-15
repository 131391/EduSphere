@php
    use App\Enums\VisitorStatus;
@endphp
@extends('layouts.receptionist')

@section('title', 'Administrative Nerve Center')

@section('content')
<div class="space-y-6">
    <!-- Premium Greeting Section -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-teal-100/50 shadow-xl shadow-teal-500/5 relative overflow-hidden">
        <div class="absolute right-0 top-0 -mr-12 -mt-12 w-64 h-64 bg-teal-50/30 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">
                    Welcome back, <span class="text-teal-600 truncate">{{ Auth::user()->name }}</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Front Desk Intelligence Matrix • {{ now()->format('l, jS F Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-6 py-3 bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-900/30 rounded-2xl flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></div>
                    <span class="text-xs font-black text-teal-700 dark:text-teal-400 uppercase tracking-widest">Active Session</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Collection Stat -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-50 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Assets</p>
                    <p class="text-xl font-black text-gray-800">₹ {{ number_format($stats['total_collection'], 0) }}</p>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Today</span>
                <span class="text-xs font-black text-gray-700">₹ {{ number_format($stats['today_collection'], 0) }}</span>
            </div>
        </div>

        <!-- Admission Stat -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-50 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-user-plus text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Enrollments</p>
                    <p class="text-xl font-black text-gray-800">{{ number_format($stats['total_admission']) }}</p>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">New Today</span>
                <span class="text-xs font-black text-gray-700">{{ $stats['today_admission'] }}</span>
            </div>
        </div>

        <!-- Enquiry Stat -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-50 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-search-dollar text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Lead Matrix</p>
                    <p class="text-xl font-black text-gray-800">{{ number_format($stats['total_enquiry']) }}</p>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider">Active Today</span>
                <span class="text-xs font-black text-gray-700">{{ $stats['today_enquiry'] }}</span>
            </div>
        </div>

        <!-- Academic Classes Stat -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-teal-50 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-school text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Unit Clusters</p>
                    <p class="text-xl font-black text-gray-800">{{ $stats['running_classes'] }}</p>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">Total Sections</span>
                <span class="text-xs font-black text-gray-700">{{ $stats['total_sections'] }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Visitor Activity Table -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden shadow-teal-500/5">
            <div class="px-8 py-5 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white dark:bg-gray-800 border border-teal-100 flex items-center justify-center text-teal-500 shadow-sm">
                        <i class="fas fa-bolt-lightning text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest leading-tight">Visitor Synchronization</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1">Real-time tracking of institutional traffic</p>
                    </div>
                </div>
                <a href="{{ route('receptionist.visitors.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-[10px] font-black uppercase tracking-widest text-teal-600 rounded-xl hover:bg-teal-50 transition-colors shadow-sm">Analyze All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/30 dark:bg-gray-700/10">
                            <th class="text-left px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Entry ID</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Subject Identity</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Purpose Hierarchy</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Protocol Stance</th>
                            <th class="text-right px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Logged At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                        @forelse($recentVisitors as $visitor)
                        <tr class="hover:bg-teal-50/20 dark:hover:bg-teal-900/5 transition-colors group">
                            <td class="px-8 py-4">
                                <span class="font-black text-gray-300 group-hover:text-teal-400 transition-colors tracking-tighter">{{ $visitor->visitor_no }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $visitor->name }}</span>
                                    <span class="text-[10px] font-bold text-gray-400">{{ $visitor->mobile }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 text-gray-600 dark:text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                    {{ $visitor->visit_purpose ?? 'Institutional' }}
                                </span>
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
                                <div class="inline-flex items-center gap-2 {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['border'] }} border px-3 py-1 rounded-xl shadow-sm">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $theme['dot'] }}"></div>
                                    <span class="text-[10px] font-black uppercase tracking-widest">{{ $visitor->status->label() }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <span class="font-bold text-gray-400 tabular-nums uppercase text-[10px]">{{ $visitor->created_at->format('d M • H:i') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <i class="fas fa-inbox text-2xl text-gray-200"></i>
                                </div>
                                <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Quiet Zone Detected</h4>
                                <p class="text-[10px] font-bold text-gray-300 uppercase mt-2">No visitor activity records found in the current temporal window.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Secondary Insights / Visitor Segment -->
        <div class="space-y-6">
            <div class="bg-gray-900 rounded-3xl p-6 shadow-xl shadow-gray-900/10 relative overflow-hidden group">
                <div class="absolute right-0 bottom-0 -mb-8 -mr-8 w-48 h-48 bg-teal-500/10 rounded-full blur-2xl group-hover:scale-110 transition-transform duration-700"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 flex items-center gap-2 relative z-10">
                    <i class="fas fa-project-diagram text-teal-400"></i>
                    Traffic Analytics
                </h3>
                
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-black text-teal-400 uppercase tracking-widest leading-none mb-2">Total Load</p>
                        <p class="text-2xl font-black text-white tabular-nums">{{ $visitorStats['total'] }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest leading-none mb-2">Digital (Online)</p>
                        <p class="text-2xl font-black text-white tabular-nums">{{ $visitorStats['online'] }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest leading-none mb-2">Institutional</p>
                        <p class="text-2xl font-black text-white tabular-nums">{{ $visitorStats['offline'] }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 hover:bg-white/10 transition-all">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest leading-none mb-2">Exceptions</p>
                        <p class="text-2xl font-black text-white tabular-nums">{{ $visitorStats['cancelled'] }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-white/5 relative z-10">
                    <div class="flex items-center justify-between text-[10px] font-black text-white/40 uppercase tracking-widest mb-2">
                        <span>Front Desk Efficiency Range</span>
                        <span class="text-teal-400">98.4%</span>
                    </div>
                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 w-[98.4%] shadow-[0_0_10px_rgba(20,184,166,0.3)]"></div>
                    </div>
                </div>
            </div>

            <!-- Call to Action Card -->
            <div class="bg-gradient-to-br from-teal-600 to-emerald-700 rounded-3xl p-6 shadow-xl shadow-teal-700/20 relative overflow-hidden group border border-teal-500/50">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-125 transition-transform duration-700">
                    <i class="fas fa-headset text-9xl text-white"></i>
                </div>
                <h4 class="text-white font-black text-lg tracking-tight relative z-10">Operational Mastery</h4>
                <p class="text-teal-50/70 text-xs font-medium mt-2 leading-relaxed relative z-10">Our institutional protocol demands absolute precision in registry synchronization.</p>
                <div class="mt-6 relative z-10">
                    <button class="w-full bg-white text-teal-700 font-black text-[10px] uppercase tracking-widest py-3 rounded-2xl shadow-xl hover:bg-teal-50 active:scale-95 transition-all">Execute Audit Protocol</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

