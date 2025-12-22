@extends('layouts.school')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Registration Details</h1>
            <p class="text-gray-600 dark:text-gray-400">Registration No: {{ $studentRegistration->registration_no }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('school.student-registrations.edit', $studentRegistration->id) }}" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="fas fa-edit"></i>
                <span>Edit</span>
            </a>
            <a href="{{ route('school.student-registrations.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    <div class="space-y-8">
        {{-- Registration & Personal Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Basic Information</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Registration No</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 font-semibold">{{ $studentRegistration->registration_no }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Registration Date</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->registration_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Academic Year</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->academicYear->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Class</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->class->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Full Name</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Gender</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->gender }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Date of Birth</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->dob ? $studentRegistration->dob->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Mobile No</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->mobile_no }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Admission Status</p>
                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                        {{ $studentRegistration->admission_status == 'Admitted' ? 'bg-teal-100 text-teal-600' : ($studentRegistration->admission_status == 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600') }}">
                        {{ $studentRegistration->admission_status }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Parent Details --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Father --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Father's Details</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-20 h-20 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                            @if($studentRegistration->father_photo)
                                <img src="{{ asset('storage/' . $studentRegistration->father_photo) }}" alt="Father" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-gray-400 text-3xl"></i>
                            @endif
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $studentRegistration->father_name_prefix }} {{ $studentRegistration->father_first_name }} {{ $studentRegistration->father_last_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $studentRegistration->father_occupation }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Mobile</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->father_mobile_no }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Email</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->father_email ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mother --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Mother's Details</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-20 h-20 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                            @if($studentRegistration->mother_photo)
                                <img src="{{ asset('storage/' . $studentRegistration->mother_photo) }}" alt="Mother" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-gray-400 text-3xl"></i>
                            @endif
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $studentRegistration->mother_name_prefix }} {{ $studentRegistration->mother_first_name }} {{ $studentRegistration->mother_last_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $studentRegistration->mother_occupation }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Mobile</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->mother_mobile_no }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Email</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $studentRegistration->mother_email ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Address Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Address Information</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 border-b pb-2">Permanent Address</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $studentRegistration->permanent_address }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $studentRegistration->permanent_city }}, {{ $studentRegistration->permanent_state }} - {{ $studentRegistration->permanent_pin }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $studentRegistration->permanent_country }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 border-b pb-2">Correspondence Address</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $studentRegistration->correspondence_address ?? 'Same as Permanent' }}</p>
                    @if($studentRegistration->correspondence_address)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $studentRegistration->correspondence_city }}, {{ $studentRegistration->correspondence_state }} - {{ $studentRegistration->correspondence_pin }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $studentRegistration->correspondence_country }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
