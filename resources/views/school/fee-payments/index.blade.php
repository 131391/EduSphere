@extends('layouts.school')

@section('title', 'Collect Fees')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Collect Student Fees</h1>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('fee-payments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or Admission No" 
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Filter by Class</label>
            <select name="class_id" id="class_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 mr-2">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            <a href="{{ route('fee-payments.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Students List -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ADMISSION NO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STUDENT NAME</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLASS & SECTION</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTION</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->admission_no }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->full_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('fee-payments.collect', $student) }}" 
                           class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-200 transition-colors">
                            <i class="fas fa-money-bill-wave mr-2"></i> Collect Fees
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No students found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $students->links() }}
    </div>
    @endif
</div>
@endsection
