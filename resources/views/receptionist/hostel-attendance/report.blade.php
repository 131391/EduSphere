@extends('layouts.receptionist')

@section('title', 'Historical Index - Hostel Attendance')
@section('page-title', 'Historical Index')
@section('page-description', 'Audit and export long-term residential attendance metrics')

@section('content')
<div class="space-y-6">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Audited Records</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $attendances->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Target Blocks</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $hostels->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building-circle-check text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-teal-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Compliance Rate</p>
                    <p class="text-3xl font-bold text-teal-600 mt-2">{{ $attendances->total() > 0 ? round(($attendances->where('is_present', true)->count() / max($attendances->count(), 1)) * 100) : 0 }}%</p>
                </div>
                <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-teal-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Data Integrity</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">High</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-halved text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('receptionist.hostel-attendance.index') }}" 
                   class="w-8 h-8 bg-teal-50 border border-teal-100 rounded-lg flex items-center justify-center text-teal-600 hover:bg-teal-100 transition-all shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-history text-xs"></i>
                        </div>
                        Historical Index
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Generate multi-dimensional occupancy reports.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($attendances->total() > 0)
                <a href="{{ route('receptionist.hostel-attendance.report', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 group">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export Records (CSV)
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Filtering Engine --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('receptionist.hostel-attendance.report') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Hostel Block</label>
                <select name="hostel_id" class="modal-input-premium">
                    <option value="">All Segments</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}" {{ request('hostel_id') == $hostel->id ? 'selected' : '' }}>
                            {{ $hostel->hostel_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Temporal Start</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="modal-input-premium">
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Temporal End</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="modal-input-premium">
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full h-[42px] bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 group">
                    <i class="fas fa-search"></i>
                    Synthesize Audit
                </button>
            </div>
        </form>
    </div>

    {{-- Data Repository --}}
    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'render' => function($row, $index, $data) {
                    return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'student_name',
                'label' => 'STUDENT',
                'render' => function($row) {
                    $student = $row->student;
                    return '<span class="font-bold text-gray-800">' . trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) . '</span>';
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'ADMISSION',
                'render' => function($row) {
                    return $row->student->admission_no ?? 'N/A';
                }
            ],
            [
                'key' => 'hostel',
                'label' => 'HOSTEL',
                'render' => function($row) {
                    return $row->hostel->hostel_name ?? 'N/A';
                }
            ],
            [
                'key' => 'room',
                'label' => 'ROOM/UNIT',
                'render' => function($row) {
                    return 'Room ' . ($row->room_name ?? 'N/A') . ' • ' . ($row->bed_no ?? 'N/A');
                }
            ],
            [
                'key' => 'status',
                'label' => 'STATUS',
                'render' => function($row) {
                    if ($row->is_present) {
                        return '<span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold uppercase tracking-tight">Present</span>';
                    } else {
                        return '<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase tracking-tight">Absent</span>';
                    }
                }
            ],
            [
                'key' => 'date',
                'label' => 'LOG DATE',
                'render' => function($row) {
                    return $row->attendance_date ? $row->attendance_date->format('d M, Y') : 'N/A';
                }
            ],
            [
                'key' => 'auditor',
                'label' => 'VERIFIED BY',
                'render' => function($row) {
                    return '<span class="text-[10px] font-bold text-gray-500 uppercase">' . ($row->markedBy->name ?? 'SYSTEM') . '</span>';
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
