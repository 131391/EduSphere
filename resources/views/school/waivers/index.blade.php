@extends('layouts.school')

@section('title', 'Fee Waivers')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Fee Waivers</h1>
            <p class="text-sm text-gray-600">Managing student fee concessions</p>
        </div>
        <div>
            <a href="{{ route('school.waivers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center shadow-sm">
                <i class="fas fa-plus mr-2 text-sm"></i>
                Apply Waiver
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('school.waivers.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search Student</label>
                <select name="student_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Students</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }} ({{ $student->admission_no }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-medium text-sm">
                Filter
            </button>
        </form>
    </div>

    <!-- Waivers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waiver Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session/Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied On</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($waivers as $waiver)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $waiver->student->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $waiver->student->admission_no }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-green-600">₹ {{ number_format($waiver->waiver_amount, 2) }}</span>
                                    @if($waiver->waiver_percentage)
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $waiver->waiver_percentage }}% of ₹ {{ number_format($waiver->actual_fee, 2) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $waiver->academicYear->name }}</div>
                                <div class="text-xs text-blue-600 font-medium">{{ $waiver->fee_period }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 line-clamp-1 max-w-xs" title="{{ $waiver->reason }}">
                                    {{ $waiver->reason }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $waiver->created_at->format('d M, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-percentage text-4xl mb-4 opacity-20"></i>
                                    <p class="text-lg font-medium">No waivers found</p>
                                    <p class="text-sm opacity-60">Apply a new waiver to see it here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $waivers->links() }}
        </div>
    </div>
</div>
@endsection
