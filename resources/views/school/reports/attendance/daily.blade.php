@extends('layouts.school')

@section('title', 'Daily Attendance Summary')

@section('content')
<div class="mb-6 flex items-center justify-between no-print">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Daily Attendance Summary</h1>
        <p class="text-gray-600">Overview of attendance across all classes</p>
    </div>
    <div class="flex space-x-3">
        <form action="{{ route('school.reports.attendance.daily') }}" method="GET" class="flex items-center space-x-2">
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </form>
        <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 flex items-center shadow-sm">
            <i class="fas fa-print mr-2"></i> Print
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($summary as $classData)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Class: {{ $classData['class_name'] }}</h3>
        </div>
        <div class="p-0">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Section</th>
                        <th class="px-5 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Total</th>
                        <th class="px-5 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">P</th>
                        <th class="px-5 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">A</th>
                        <th class="px-5 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">L</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($classData['sections'] as $section)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-5 py-3 text-sm font-medium text-gray-700">{{ $section['section_name'] }}</td>
                        <td class="px-5 py-3 text-sm text-center font-bold text-gray-900">{{ $section['total_students'] }}</td>
                        <td class="px-5 py-3 text-sm text-center text-green-600 font-bold">{{ $section['present'] }}</td>
                        <td class="px-5 py-3 text-sm text-center text-red-600 font-bold">{{ $section['absent'] }}</td>
                        <td class="px-5 py-3 text-sm text-center text-orange-600 font-bold">{{ $section['leave'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100">
            @php
                $totalP = collect($classData['sections'])->sum('present');
                $totalS = collect($classData['sections'])->sum('total_students');
                $percent = $totalS > 0 ? round(($totalP / $totalS) * 100, 1) : 0;
            @endphp
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500 font-medium uppercase">Attendance rate</span>
                <span class="text-sm font-black {{ $percent > 90 ? 'text-green-600' : ($percent > 75 ? 'text-blue-600' : 'text-red-600') }}">
                    {{ $percent }}%
                </span>
            </div>
            <div class="mt-1.5 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .grid { display: block !important; }
    .bg-white { border: 1px solid #eee; margin-bottom: 1rem; page-break-inside: avoid; }
}
</style>
@endsection
