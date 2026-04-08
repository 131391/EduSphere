@extends('layouts.school')

@section('title', 'Student Attendance History')

@section('content')
<div class="mb-6 flex items-center justify-between no-print">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Student Attendance History</h1>
        <p class="text-gray-600">Search and view individual student attendance</p>
    </div>
    <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 flex items-center shadow-sm">
        <i class="fas fa-print mr-2"></i> Print History
    </button>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6 no-print">
    <form action="{{ route('school.reports.attendance.student') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Student</label>
            <select name="student_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 select2">
                <option value="">-- Select Student --</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                        {{ $student->admission_no }} - {{ $student->full_name }} ({{ $student->class->name }} {{ $student->section->name }})
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md">
            View History
        </button>
    </form>
</div>

@if($history)
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            @php $student = $students->firstWhere('id', $studentId); @endphp
            <h2 class="text-xl font-bold text-gray-900">{{ $student->full_name }}</h2>
            <p class="text-sm text-gray-500">Admission No: {{ $student->admission_no }} | Class: {{ $student->class->name }}</p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-black text-blue-600">
                {{ $history->where('status.value', 'present')->count() }} / {{ $history->count() }}
            </div>
            <div class="text-xs text-gray-500 uppercase font-bold">Total Presence</div>
        </div>
    </div>
    
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($history as $record)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $record->date->format('d M Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $record->date->format('l') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($record->status->value == 'present')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Present</span>
                    @elseif($record->status->value == 'absent')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Absent</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Leave</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 italic">
                    {{ $record->remarks ?? '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<style>
@media print {
    .no-print { display: none !important; }
}
</style>
@endsection
