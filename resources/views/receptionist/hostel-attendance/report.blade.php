@extends('layouts.receptionist')

@section('title', 'Hostel Attendance Report')
@section('page-title', 'Attendance Report')
@section('page-description', 'View and export hostel attendance history')

@section('content')
<div class="space-y-6">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Records" :value="$stats['total_logs']" icon="fas fa-file-invoice" color="blue" />
        <x-stat-card label="Hostels" :value="$hostels->count()" icon="fas fa-building-circle-check" color="emerald" />
        <x-stat-card label="Present Rate" :value="$stats['compliance_percentage'] . '%'" icon="fas fa-chart-line" color="teal" />
        <x-stat-card label="Data Status" value="Up to Date" icon="fas fa-shield-halved" color="indigo" />
    </div>

    {{-- Page Header --}}
    <x-page-header title="Attendance Report" description="View hostel attendance history by date and hostel" icon="fas fa-history">
        <a href="{{ route('receptionist.hostel-attendance.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-xs"></i>
            Back to Attendance
        </a>
        @if($attendances->total() > 0)
        <a href="{{ route('receptionist.hostel-attendance.report', array_merge(request()->all(), ['export' => 'excel'])) }}" 
           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-file-excel mr-2 text-xs"></i>
            Export CSV
        </a>
        @endif
    </x-page-header>

    {{-- Filtering Engine --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <form method="GET" action="{{ route('receptionist.hostel-attendance.report') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Hostel</label>
                        <select name="hostel_id" class="w-full h-11 px-4 bg-slate-50 border-slate-200 dark:bg-gray-700/50 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">All Hostels</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}" {{ request('hostel_id') == $hostel->id ? 'selected' : '' }}>
                                    {{ $hostel->hostel_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">From Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full h-11 px-4 bg-slate-50 border-slate-200 dark:bg-gray-700/50 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none shadow-sm cursor-pointer">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">To Date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full h-11 px-4 bg-slate-50 border-slate-200 dark:bg-gray-700/50 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none shadow-sm cursor-pointer">
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full h-11 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all duration-300 shadow-lg shadow-teal-500/20 flex items-center justify-center gap-3 active:scale-95">
                            <i class="fas fa-search text-sm"></i>
                            Search
                        </button>
                    </div>
                </div>

                @if(request()->anyFilled(['hostel_id', 'date_from', 'date_to']))
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('receptionist.hostel-attendance.report') }}" class="px-3 py-1 bg-rose-50 text-rose-600 text-[10px] font-black rounded-lg border border-rose-100 hover:bg-rose-100 transition-all uppercase tracking-widest">
                        <i class="fas fa-times-circle mr-1 text-[8px]"></i> Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>

        {{-- Data Repository --}}
        <div class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 overflow-hidden relative min-h-[400px]">
            <div class="overflow-x-auto text-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-700/10 border-b border-slate-100 dark:border-gray-700">
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Student</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Hostel / Room</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                            <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Marked By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($attendances as $index => $attendance)
                        <tr class="hover:bg-teal-50/30 dark:hover:bg-teal-900/10 transition-colors group">
                            <td class="px-8 py-4 font-black text-gray-300 group-hover:text-teal-400 transition-colors">
                                #{{ str_pad(($attendances->currentPage() - 1) * $attendances->perPage() + $index + 1, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-600 dark:text-gray-400 font-bold border border-slate-200 dark:border-gray-600 uppercase text-[10px] shadow-sm">
                                        {{ substr($attendance->student->first_name ?? 'S', 0, 1) }}{{ substr($attendance->student->last_name ?? 'T', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ trim(($attendance->student->first_name ?? '') . ' ' . ($attendance->student->last_name ?? '')) }}</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $attendance->student->admission_no ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-tight">{{ $attendance->hostel->hostel_name ?? 'N/A' }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Unit {{ $attendance->room_name ?? 'N/A' }} • Bed {{ $attendance->bed_no ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if($attendance->is_present)
                                    <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                        <i class="fas fa-check-circle mr-1.5 text-[8px]"></i> Present
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                        <i class="fas fa-times-circle mr-1.5 text-[8px]"></i> Absent
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-tight">
                                    {{ $attendance->attendance_date ? $attendance->attendance_date->format('d M, Y') : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-slate-50 dark:bg-gray-700/50 px-2 py-1 rounded-lg border border-slate-100 dark:border-gray-600">
                                    {{ $attendance->markedBy->name ?? 'SYSTEM' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 rounded-3xl flex items-center justify-center mb-6 border border-slate-100 dark:border-gray-600">
                                        <i class="fas fa-folder-open text-3xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">No Records Found</h3>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest max-w-sm mx-auto mt-2 leading-relaxed">
                                        No attendance records match your filters. Try adjusting the date range or hostel selection.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
