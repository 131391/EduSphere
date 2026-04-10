@extends('layouts.receptionist')

@section('title', 'Historical Index - Hostel Attendance')
@section('page-title', 'Historical Index')
@section('page-description', 'Audit and export long-term residential attendance metrics')

@section('content')
<div class="space-y-6">
    {{-- Statistics Overview (Conceptual for current view) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Audited Records</p>
                <p class="text-3xl font-black text-gray-800">{{ $attendances->total() }}</p>
            </div>
            <div class="bg-indigo-100 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-file-invoice text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Target Hosting</p>
                <p class="text-3xl font-black text-gray-800">{{ $hostels->count() }}</p>
            </div>
            <div class="bg-emerald-100 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-building-circle-check text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Compliance Rate</p>
                <p class="text-3xl font-black text-emerald-600">{{ $attendances->total() > 0 ? round(($attendances->where('is_present', true)->count() / max($attendances->count(), 1)) * 100) : 0 }}%</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Data Integrity</p>
                <p class="text-3xl font-black text-indigo-600">High</p>
            </div>
            <div class="bg-indigo-50 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-shield-halved text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Page Header --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('receptionist.hostel-attendance.index') }}" 
                   class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Historical Index</h2>
                    <p class="text-sm text-gray-500 font-medium tracking-tight">Generate multi-dimensional occupancy reports</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($attendances->total() > 0)
                <a href="{{ route('receptionist.hostel-attendance.report', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-sm font-black rounded-xl transition-all shadow-xl shadow-emerald-100 group">
                    <i class="fas fa-file-excel mr-2 group-hover:scale-110 transition-transform"></i>
                    Export Comprehensive CSV
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Universal Filtering Engine --}}
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-8 border border-gray-100 shadow-sm mb-8 relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-8 text-gray-50 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-filter text-9xl"></i>
        </div>

        <form method="GET" action="{{ route('receptionist.hostel-attendance.report') }}" class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Segment Filter</label>
                <select name="hostel_id" 
                        class="w-full px-5 py-3.5 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all outline-none font-bold text-sm">
                    <option value="">All Segments</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}" {{ request('hostel_id') == $hostel->id ? 'selected' : '' }}>
                            {{ $hostel->hostel_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Temporal Start</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-5 py-3.5 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all outline-none font-bold text-sm">
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Temporal End</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-5 py-3.5 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all outline-none font-bold text-sm">
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-indigo-100 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-bolt"></i>
                    Synthesize Report
                </button>
            </div>
        </form>
    </div>

    {{-- Data Repository --}}
    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'ENTRY ID',
                'render' => function($row) {
                    return '<span class="text-[10px] font-black text-gray-400">#' . str_pad($row->id, 5, '0', STR_PAD_LEFT) . '</span>';
                }
            ],
            [
                'key' => 'identity',
                'label' => 'RESIDENT IDENTITY',
                'render' => function($row) {
                    $student = $row->student;
                    $name = $student ? trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) : 'N/A';
                    $admission = $student->admission_no ?? 'N/A';
                    return '<div class="flex flex-col">
                                <span class="font-black text-gray-800">' . $name . '</span>
                                <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter">' . $admission . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'mapping',
                'label' => 'MAPPING CONTEXT',
                'render' => function($row) {
                    return '<div class="flex flex-col">
                                <span class="font-bold text-gray-700 text-xs">' . ($row->hostel->hostel_name ?? 'N/A') . '</span>
                                <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter">Room ' . ($row->room_name ?? 'N/A') . ' • Unit ' . ($row->bed_no ?? 'N/A') . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'COMPLIANCE',
                'render' => function($row) {
                    if ($row->is_present) {
                        return '<div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Present</span>
                                </div>';
                    } else {
                        return '<div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span class="text-xs font-black text-red-600 uppercase tracking-widest">Absent</span>
                                </div>';
                    }
                }
            ],
            [
                'key' => 'date',
                'label' => 'LOG TIMESTAMP',
                'render' => function($row) {
                    return '<div class="flex flex-col">
                                <span class="font-bold text-gray-700 text-xs">' . ($row->attendance_date ? $row->attendance_date->format('d M, Y') : 'N/A') . '</span>
                                <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter">Checked: ' . ($row->created_at->format('H:i')) . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'auditor',
                'label' => 'VERIFIED BY',
                'render' => function($row) {
                    return '<span class="text-[10px] font-black text-gray-500 uppercase">' . ($row->markedBy->name ?? 'SYSTEM') . '</span>';
                }
            ],
        ];
    @endphp

    <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden">
        <x-data-table 
            :columns="$tableColumns"
            :data="$attendances"
            :searchable="true"
            empty-message="No historical records matched your query parameters"
            empty-icon="fas fa-folder-open"
        />
    </div>
</div>
@endsection
