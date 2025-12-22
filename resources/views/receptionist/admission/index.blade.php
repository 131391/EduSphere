@extends('layouts.receptionist')

@section('title', 'Admission Confirmation')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Total Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalRegistration }}</h3>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Admission Done</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $admissionDone }}</h3>
                </div>
                <div class="text-green-500">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Pending Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $pendingRegistration }}</h3>
                </div>
                <div class="text-yellow-500">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Cancelled Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $cancelledRegistration }}</h3>
                </div>
                <div class="text-red-500">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Total Enquiry</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalEnquiry }}</h3>
                </div>
                <div class="text-purple-500">
                    <i class="fas fa-question-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions and Filters -->
    <div class="bg-white p-4 rounded-lg shadow flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-1">
            <input type="text" placeholder="Search here" class="border border-gray-300 rounded px-3 py-2 w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-center gap-2">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="bg-gray-700 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-gray-800">
                    Search By Criteria <i class="fas fa-chevron-down"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg z-10 py-1 border">
                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-sm">Class wise</a>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-sm">Gender Wise</a>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-sm">Category Wise</a>
                </div>
            </div>
            <button class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('receptionist.admission.create') }}" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600">
                New Admission
            </a>
            <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                Send SMS
            </button>
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Send Email
            </button>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admission No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student's Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class-Section</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Father's Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admission Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->admission_no }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ $student->photo ? asset('storage/'.$student->photo) : 'https://ui-avatars.com/api/?name='.urlencode($student->full_name) }}" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $student->full_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->father_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->registration_no ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $student->admission_date ? $student->admission_date->format('M. d, Y, h:i a') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('receptionist.admission.show', $student->id) }}" class="text-blue-600 hover:text-blue-900"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('receptionist.admission.edit', $student->id) }}" class="text-indigo-600 hover:text-indigo-900"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('receptionist.admission.destroy', $student->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No students found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
