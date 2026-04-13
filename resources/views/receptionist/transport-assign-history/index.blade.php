@extends('layouts.receptionist')

@section('title', 'Transport Assign History')

@section('content')
<div class="space-y-6">
    <!-- Success Message -->

    {{-- Page Header --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 p-3 rounded-2xl shadow-lg shadow-slate-200">
                    <i class="fas fa-archive text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Assignment Archive</h2>
                    <p class="text-sm text-gray-500 font-medium">Historical audit trail of student transit mapping</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('receptionist.transport-assignments.index') }}"
                    class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-arrow-left mr-2 text-indigo-500"></i>
                    Back to Registry
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

