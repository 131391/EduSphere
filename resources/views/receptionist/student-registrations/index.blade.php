@extends('layouts.receptionist')

@section('content')
<div class="p-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Student Registration</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage student registrations and admissions</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
            <a href="{{ route('receptionist.student-registrations.create') }}" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Add Student Registration</span>
            </a>
            <button class="bg-orange-400 hover:bg-orange-500 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="fas fa-sms"></i>
                <span>Send SMS</span>
            </button>
            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="fas fa-envelope"></i>
                <span>Send Email</span>
            </button>
            <button class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors border border-gray-200 dark:border-gray-600">
                <i class="fas fa-file-export"></i>
                <span>Export</span>
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        {{-- Total Registration --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <i class="fas fa-user-plus text-blue-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Total</span>
            </div>
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">TOTAL REGISTRATION</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total'] }}</p>
        </div>

        {{-- Admission Done --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i class="fas fa-graduation-cap text-teal-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-teal-600 bg-teal-50 px-2 py-1 rounded-full">Done</span>
            </div>
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">ADMISSION DONE</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['admitted'] }}</p>
        </div>

        {{-- Pending Registration --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <i class="fas fa-clock text-orange-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">PENDING REGISTRATION</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['pending'] }}</p>
        </div>

        {{-- Cancelled Registration --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <i class="fas fa-user-times text-red-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">Cancelled</span>
            </div>
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">CANCELLED REGISTRATION</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['cancelled'] }}</p>
        </div>

        {{-- Total Enquiry --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <i class="fas fa-question-circle text-purple-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Enquiry</span>
            </div>
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">TOTAL ENQUIRY</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total_enquiry'] }}</p>
        </div>
    </div>

    {{-- Search and Table Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <form action="{{ route('receptionist.student-registrations.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Reg No, Name or Mobile..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                </div>
                <select name="class_id" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
                <select name="admission_status" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('admission_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Admitted" {{ request('admission_status') == 'Admitted' ? 'selected' : '' }}>Admitted</option>
                    <option value="Cancelled" {{ request('admission_status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="bg-teal-500 text-white px-6 py-2 rounded-lg hover:bg-teal-600 transition-colors">
                    Search
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Registration No.</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Student's Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Class</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Reg. Form Fee</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Registration Date</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Admission Status</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($registrations as $registration)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-800 dark:text-gray-200 font-medium">
                            {{ $registration->registration_no }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                    @if($registration->student_photo)
                                        <img src="{{ asset('storage/' . $registration->student_photo) }}" alt="Student" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-gray-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $registration->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $registration->mobile_no }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $registration->class->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ number_format($registration->registration_fee, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $registration->registration_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = [
                                    'Pending' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                                    'Admitted' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400',
                                    'Cancelled' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $statusClass = $statusClasses[$registration->admission_status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ $registration->admission_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('receptionist.student-registrations.show', $registration->id) }}" class="text-blue-500 hover:text-blue-600 transition-colors" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('receptionist.student-registrations.edit', $registration->id) }}" class="text-teal-500 hover:text-teal-600 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('receptionist.student-registrations.destroy', $registration->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this registration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 transition-colors" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-folder-open text-4xl text-gray-200 dark:text-gray-700"></i>
                                <p>No registrations found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-700">
            {{ $registrations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
