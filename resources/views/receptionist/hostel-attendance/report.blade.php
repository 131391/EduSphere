@extends('layouts.receptionist')

@section('title', 'Hostel Attendance Report')

@section('content')
<div class="space-y-6">
    {{-- Page Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hostel Attendance Report</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View and export hostel attendance records</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('receptionist.hostel-attendance.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
                @if($attendances->count() > 0)
                <a href="{{ route('receptionist.hostel-attendance.report', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export To Excel
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Filter Options</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Select filters to generate the report</p>
        </div>
        <form method="GET" action="{{ route('receptionist.hostel-attendance.report') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Hostel Select -->
            <div>
                <label for="hostel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Hostel
                </label>
                <select id="hostel_id" 
                        name="hostel_id"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 shadow-sm">
                    <option value="">All Hostels</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}" {{ request('hostel_id') == $hostel->id ? 'selected' : '' }}>
                            {{ $hostel->hostel_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date From
                </label>
                <div class="relative">
                    <input type="date" 
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 shadow-sm pl-10 pr-4 py-2.5 h-[42px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-alt text-gray-400 dark:text-gray-500"></i>
                    </div>
                </div>
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date To
                </label>
                <div class="relative">
                    <input type="date" 
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 shadow-sm pl-10 pr-4 py-2.5 h-[42px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-alt text-gray-400 dark:text-gray-500"></i>
                    </div>
                </div>
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'render' => function($row, $index, $data) use ($attendances) {
                    return ($attendances->currentPage() - 1) * $attendances->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'ADMISSION NO',
                'render' => function($row) {
                    return $row->student->admission_no ?? 'N/A';
                }
            ],
            [
                'key' => 'student_name',
                'label' => 'STUDENT NAME',
                'render' => function($row) {
                    if (!$row->student) return 'N/A';
                    return trim(($row->student->first_name ?? '') . ' ' . ($row->student->middle_name ?? '') . ' ' . ($row->student->last_name ?? ''));
                }
            ],
            [
                'key' => 'class',
                'label' => 'CLASS',
                'render' => function($row) {
                    if (!$row->student) {
                        return 'N/A';
                    }
                    
                    // Ensure class relationship is loaded
                    if (!$row->student->relationLoaded('class')) {
                        $row->student->load('class');
                    }
                    if (!$row->student->relationLoaded('section')) {
                        $row->student->load('section');
                    }
                    
                    if (!$row->student->class) {
                        return 'N/A';
                    }
                    
                    // ClassModel uses 'name' attribute, not 'class_name'
                    // Section also uses 'name' attribute, not 'section_name'
                    $class = $row->student->class->name ?? 'N/A';
                    $section = $row->student->section ? $row->student->section->name : '';
                    return $class . ($section ? ' - ' . $section : '');
                }
            ],
            [
                'key' => 'hostel',
                'label' => 'HOSTEL',
                'render' => function($row) {
                    return $row->hostel ? $row->hostel->hostel_name : 'N/A';
                }
            ],
            [
                'key' => 'floor',
                'label' => 'FLOOR',
                'render' => function($row) {
                    return $row->floor_name ?? 'N/A';
                }
            ],
            [
                'key' => 'room',
                'label' => 'ROOM',
                'render' => function($row) {
                    return $row->room_name ?? 'N/A';
                }
            ],
            [
                'key' => 'bed_no',
                'label' => 'BED NO',
                'render' => function($row) {
                    return $row->bed_no ?? 'N/A';
                }
            ],
            [
                'key' => 'attendance',
                'label' => 'ATTENDANCE',
                'render' => function($row) {
                    if ($row->is_present) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Present</span>';
                    } else {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Absent</span>';
                    }
                }
            ],
            [
                'key' => 'attendance_date',
                'label' => 'ATTENDANCE DATE',
                'render' => function($row) {
                    return $row->attendance_date ? $row->attendance_date->format('d/m/Y') : 'N/A';
                }
            ],
        ];

        // Prepare filters
        $filters = [
            [
                'name' => 'hostel_id',
                'label' => 'Select Hostel',
                'options' => ['' => 'All Hostels'] + $hostels->pluck('hostel_name', 'id')->toArray()
            ],
        ];
    @endphp

    <x-data-table
        :columns="$tableColumns"
        :data="$attendances"
        :searchable="true"
        :filterable="true"
        :filters="$filters"
    >
        Hostel Attendance Report
    </x-data-table>
</div>
@endsection

