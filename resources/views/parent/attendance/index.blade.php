@extends('layouts.parent')

@section('title', 'Attendance Records')
@section('page-title', 'Attendance Records')

@section('content')
<div class="space-y-6">
    <!-- Header & Child Selector -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-calendar-check text-xs"></i>
                    </div>
                    Attendance Tracker
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review your child's daily attendance records.</p>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                @if($selectedChildId)
                <a href="{{ route('parent.attendance.export', ['student_id' => $selectedChildId]) }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 transition-all uppercase tracking-wider">
                    <i class="fas fa-file-csv mr-2"></i> Export
                </a>
                @endif
            @if($children->count() > 0)
            <form method="GET" action="{{ route('parent.attendance.index') }}" class="w-full sm:w-64" x-data="{
                init() {
                    $(this.$refs.select).on('change', () => {
                        this.$el.submit();
                    });
                }
            }">
                <select name="student_id" x-ref="select" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Child...</option>
                    @foreach($children as $child)
                        <option value="{{ $child->id }}" {{ $selectedChildId == $child->id ? 'selected' : '' }}>
                            {{ $child->full_name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
            </div>
        </div>
    </div>

    @if($selectedChildId)
    <!-- Stats Section -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Present Rate -->
        @php
            $rateColor = $stats['percentage'] >= 75 ? 'emerald' : ($stats['percentage'] >= 60 ? 'amber' : 'rose');
        @endphp
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 bg-{{ $rateColor }}-50 dark:bg-{{ $rateColor }}-900/30 text-{{ $rateColor }}-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-pie text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Attendance Rate</p>
                <p class="text-2xl font-black text-{{ $rateColor }}-600">{{ $stats['percentage'] }}%</p>
            </div>
        </div>
        
        <!-- Total Days -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-day text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Days</p>
                <p class="text-2xl font-black text-gray-800 dark:text-white">{{ $stats['total'] }}</p>
            </div>
        </div>

        <!-- Present Days -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Present</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['present'] }}</p>
            </div>
        </div>

        <!-- Absent Days -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/30 text-rose-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Absent</p>
                <p class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $stats['absent'] }}</p>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    @php
        $tableColumns = [
            [
                'key' => 'date',
                'label' => 'Date',
                'sortable' => true,
                'render' => function($row) {
                    $date = \Carbon\Carbon::parse($row->date);
                    return '
                        <div>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">'.$date->format('M d, Y').'</div>
                            <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase">'.$date->format('l').'</div>
                        </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'sortable' => true,
                'render' => function($row) {
                    $statusValue = $row->status?->value ?? null;
                    return match ($statusValue) {
                        \App\Enums\AttendanceStatus::Present->value =>
                            '<span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-bold rounded-lg uppercase tracking-wider"><i class="fas fa-check mr-1"></i>Present</span>',
                        \App\Enums\AttendanceStatus::Absent->value =>
                            '<span class="px-2.5 py-1 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 text-xs font-bold rounded-lg uppercase tracking-wider"><i class="fas fa-times mr-1"></i>Absent</span>',
                        \App\Enums\AttendanceStatus::Late->value =>
                            '<span class="px-2.5 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-xs font-bold rounded-lg uppercase tracking-wider"><i class="fas fa-clock mr-1"></i>Late</span>',
                        \App\Enums\AttendanceStatus::Excused->value =>
                            '<span class="px-2.5 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-bold rounded-lg uppercase tracking-wider"><i class="fas fa-hospital mr-1"></i>Excused</span>',
                        \App\Enums\AttendanceStatus::HalfDay->value =>
                            '<span class="px-2.5 py-1 bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 text-xs font-bold rounded-lg uppercase tracking-wider"><i class="fas fa-star-half-alt mr-1"></i>Half Day</span>',
                        default =>
                            '<span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg uppercase tracking-wider">Unknown</span>',
                    };
                }
            ],
            [
                'key' => 'remarks',
                'label' => 'Remarks',
                'sortable' => false,
                'render' => function($row) {
                    return $row->remarks ? '<span class="text-sm text-gray-600 dark:text-gray-400">'.e($row->remarks).'</span>' : '<span class="text-gray-400 text-xs italic">-</span>';
                }
            ],
        ];
    @endphp

    <div class="mt-4">
        <x-data-table
            :columns="$tableColumns"
            :data="$attendanceLogs"
            :actions="[]"
            :searchable="false"
            :show-per-page="false"
            :exportable="false"
            empty-message="No attendance records found for the selected child."
            empty-icon="fas fa-calendar-times"
        >
            Daily Attendance Logs
        </x-data-table>
        @if(method_exists($attendanceLogs, 'hasPages') && $attendanceLogs->hasPages())
            <div class="mt-4">{{ $attendanceLogs->links() }}</div>
        @endif
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-12 text-center border border-indigo-100/50">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-child text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">No Child Selected</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm max-w-md mx-auto">Please select a child from the dropdown above to view their attendance records.</p>
    </div>
    @endif
</div>
@endsection
