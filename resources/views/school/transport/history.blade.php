@extends('layouts.school')

@section('title', 'Transport Assignment History - School Admin')
@section('page-title', 'Transit History')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-archive text-xs"></i>
                    </div>
                    Assignment Archive
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Historical audit trail of student transit mapping.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.transport.assignments.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i>
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
                    return trim($row->student->first_name . ' ' . $row->student->last_name) ?: 'N/A';
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
                    return $row->academicYear->name ?? 'N/A';
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
                'key' => 'bus_stop_name',
                'label' => 'BUS STOP',
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
                    return $row->deleted_at ? $row->deleted_at->format('d/m/Y') : '-';
                }
            ],
        ];

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
