@extends('layouts.receptionist')

@section('title', 'Transport Assign History')

@section('content')
<div class="space-y-6">
    <!-- Success Message -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Page Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Transport Assign History</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('receptionist.transport-assignments.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'render' => function($row, $index, $data) use ($assignments) {
                    return ($assignments->currentPage() - 1) * $assignments->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'student_name',
                'label' => 'STUDENT NAME',
                'render' => function($row) {
                    if (!$row->student) return 'N/A';
                    return trim($row->student->first_name . ' ' . $row->student->middle_name . ' ' . $row->student->last_name) ?: 'N/A';
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
                'key' => 'class',
                'label' => 'CLASS',
                'render' => function($row) {
                    return $row->student->class->name ?? 'N/A';
                }
            ],
            [
                'key' => 'academic_year',
                'label' => 'ACADEMIC YEAR',
                'render' => function($row) {
                    if (!$row->academicYear) return 'N/A';
                    return $row->academicYear->name ?? ($row->academicYear->start_date->format('Y') . '-' . $row->academicYear->end_date->format('y'));
                }
            ],
            [
                'key' => 'vehicle_no',
                'label' => 'VEHICLE NO',
                'render' => function($row) {
                    return $row->vehicle->vehicle_no ?? 'N/A';
                }
            ],
            [
                'key' => 'route_name',
                'label' => 'ROUTE NAME',
                'render' => function($row) {
                    return $row->route->route_name ?? 'N/A';
                }
            ],
            [
                'key' => 'bus_stop_no',
                'label' => 'BUS STOP NO',
                'render' => function($row) {
                    return $row->busStop->bus_stop_no ?? 'N/A';
                }
            ],
            [
                'key' => 'bus_stop_name',
                'label' => 'BUS STOP NAME',
                'render' => function($row) {
                    return $row->busStop->bus_stop_name ?? 'N/A';
                }
            ],
            [
                'key' => 'fee_per_month',
                'label' => 'FEE PER MONTH',
                'render' => function($row) {
                    return '₹' . number_format($row->fee_per_month, 2);
                }
            ],
            [
                'key' => 'withdrawal_date',
                'label' => 'WITHDRAWAL DATE',
                'render' => function($row) {
                    if ($row->deleted_at) {
                        return $row->deleted_at->format('d/m/Y');
                    }
                    return '-';
                }
            ],
        ];

        // Prepare filters
        $filters = [
            [
                'name' => 'class_id',
                'label' => 'Select Class',
                'options' => $classes->pluck('name', 'id')->toArray()
            ],
            [
                'name' => 'vehicle_id',
                'label' => 'Select Vehicle',
                'options' => $vehicles->pluck('vehicle_no', 'id')->toArray()
            ],
            [
                'name' => 'route_id',
                'label' => 'Select Route Name',
                'options' => $routes->pluck('route_name', 'id')->toArray()
            ],
            [
                'name' => 'bus_stop_id',
                'label' => 'Select Bus Stop',
                'options' => $busStops->pluck('bus_stop_name', 'id')->toArray()
            ],
        ];
    @endphp

    <x-data-table
        :columns="$tableColumns"
        :data="$assignments"
        :searchable="true"
        :filterable="true"
        :filters="$filters"
    >
        Transport Assign History
    </x-data-table>
</div>
@endsection

