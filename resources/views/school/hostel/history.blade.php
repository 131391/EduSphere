@extends('layouts.school')

@section('content')
<div x-data="ajaxDataTable({
    endpoint: '{{ route('school.hostel.assignments.history') }}',
    entityName: 'History'
})" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Assignment History</h1>
            <p class="text-gray-600">Audit trail of past hostel assignments</p>
        </div>
        <a href="{{ route('school.hostel.assignments.index') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Assignments</span>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" x-model="search" @input.debounce.500ms="fetchData()" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                    placeholder="Search by student name or ID...">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <x-data-table>
        <x-slot name="head">
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hostel Location</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room/Bed</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
        </x-slot>
        <x-slot name="body">
            <template x-for="item in items" :key="item.id">
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col text-sm">
                            <span class="font-medium text-gray-900" x-text="item.student_name"></span>
                            <span class="text-xs text-gray-500" x-text="item.admission_no"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col text-sm">
                            <span class="text-gray-900 font-medium" x-text="item.hostel_name"></span>
                            <span class="text-gray-500 text-xs" x-text="item.floor_name"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col text-sm">
                            <span class="text-indigo-600 font-medium" x-text="item.room_name"></span>
                            <span class="text-gray-500 text-xs" x-text="'Bed: ' + item.bed_no"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col text-sm">
                            <span class="text-gray-900" x-text="'Started: ' + item.start_date"></span>
                            <span class="text-gray-500 text-xs" x-text="'Ended: ' + item.end_date"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="{
                            'px-2 py-1 rounded-full text-xs font-medium': true,
                            'bg-gray-100 text-gray-700': item.status === 'inactive' || item.status === 'deleted',
                            'bg-blue-100 text-blue-700': item.status === 'active'
                        }" x-text="item.status.charAt(0).toUpperCase() + item.status.slice(1)"></span>
                    </td>
                </tr>
            </template>
        </x-slot>
    </x-data-table>
</div>
@endsection
