@extends('layouts.receptionist')

@section('title', 'Student Details')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header with Actions --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Student Admission Details</h1>
                <p class="mt-1 text-sm text-gray-500">Complete admission information</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('receptionist.admission.pdf', $student->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Download PDF
                </a>
                <a href="{{ route('receptionist.admission.edit', $student->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('receptionist.admission.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    {{-- Student Profile Hero Section --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="px-8 py-10">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                {{-- Student Photo --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-full bg-white p-1 shadow-lg">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" 
                                 alt="{{ $student->full_name }}" 
                                 class="w-full h-full rounded-full object-cover">
                        @else
                            <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-5xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Student Info --}}
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl font-bold text-white mb-2">{{ $student->full_name }}</h2>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                            <i class="fas fa-id-card mr-2"></i>
                            {{ $student->admission_no }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500 text-white">
                            <i class="fas fa-check-circle text-xs mr-2"></i>
                            Admitted
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-white/90">
                        <div class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span>{{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>{{ $student->academicYear->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-clock mr-2"></i>
                            <span>{{ $student->admission_date ? $student->admission_date->format('d M, Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hashtag text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Roll Number</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->roll_no ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-birthday-cake text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Date of Birth</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tint text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Blood Group</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->blood_group ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-phone text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Mobile</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Information --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Personal Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-3"></i>
                    Personal Information
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center">
                    <i class="fas fa-venus-mars w-5 text-gray-400 mr-3"></i>
                    <div>
                        <p class="text-xs text-gray-500">Gender</p>
                        <p class="text-sm font-medium text-gray-900">{{ $student->gender_label }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-envelope w-5 text-gray-400 mr-3"></i>
                    <div>
                        <p class="text-xs text-gray-500">Email Address</p>
                        <p class="text-sm font-medium text-gray-900">{{ $student->email ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-id-badge w-5 text-gray-400 mr-3"></i>
                    <div>
                        <p class="text-xs text-gray-500">Registration Number</p>
                        <p class="text-sm font-medium text-gray-900">{{ $student->registration_no ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admission Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-green-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-graduation-cap text-green-600 mr-3"></i>
                    Admission Information
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center">
                    <i class="fas fa-calendar-check w-5 text-gray-400 mr-3"></i>
                    <div>
                        <p class="text-xs text-gray-500">Admission Date</p>
                        <p class="text-sm font-medium text-gray-900">{{ $student->admission_date ? $student->admission_date->format('d F, Y') : 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-chalkboard w-5 text-gray-400 mr-3"></i>
                    <div>
                        <p class="text-xs text-gray-500">Class & Section</p>
                        <p class="text-sm font-medium text-gray-900">{{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt w-5 text-gray-400 mr-3"></i>
                    <div>
                        <p class="text-xs text-gray-500">Academic Year</p>
                        <p class="text-sm font-medium text-gray-900">{{ $student->academicYear->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Parent Information --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Father's Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-male text-blue-600 mr-3"></i>
                    Father's Details
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-xl font-bold text-gray-900">{{ $student->father_name }}</p>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone w-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500">Mobile Number</p>
                            <p class="text-sm font-medium text-gray-900">{{ $student->father_mobile ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mother's Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-pink-50 to-pink-100 px-6 py-4 border-b border-pink-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-female text-pink-600 mr-3"></i>
                    Mother's Details
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-xl font-bold text-gray-900">{{ $student->mother_name }}</p>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone w-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500">Mobile Number</p>
                            <p class="text-sm font-medium text-gray-900">{{ $student->mother_mobile ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Photos and Signatures Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-images text-purple-600 mr-3"></i>
                Photos & Signatures
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Student Photo --}}
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Student Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" 
                                 alt="Student Photo" 
                                 class="w-32 h-32 object-cover rounded">
                        @else
                            <div class="w-32 h-32 bg-gray-200 rounded flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Father Photo --}}
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Father Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        @if($student->father_photo ?? false)
                            <img src="{{ asset('storage/' . $student->father_photo) }}" 
                                 alt="Father Photo" 
                                 class="w-32 h-32 object-cover rounded">
                        @else
                            <div class="w-32 h-32 bg-gray-200 rounded flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Mother Photo --}}
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Mother Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        @if($student->mother_photo ?? false)
                            <img src="{{ asset('storage/' . $student->mother_photo) }}" 
                                 alt="Mother Photo" 
                                 class="w-32 h-32 object-cover rounded">
                        @else
                            <div class="w-32 h-32 bg-gray-200 rounded flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-signature text-indigo-600 mr-2"></i>
                    Signatures
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Student Signature --}}
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-2">Student Signature</p>
                        @if($student->student_signature ?? false)
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 inline-block bg-gray-50">
                                <img src="{{ asset('storage/' . $student->student_signature) }}" 
                                     alt="Student Signature" 
                                     class="h-16 object-contain">
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Not available</p>
                        @endif
                    </div>

                    {{-- Father Signature --}}
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-2">Father Signature</p>
                        @if($student->father_signature ?? false)
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 inline-block bg-gray-50">
                                <img src="{{ asset('storage/' . $student->father_signature) }}" 
                                     alt="Father Signature" 
                                     class="h-16 object-contain">
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Not available</p>
                        @endif
                    </div>

                    {{-- Mother Signature --}}
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-2">Mother Signature</p>
                        @if($student->mother_signature ?? false)
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 inline-block bg-gray-50">
                                <img src="{{ asset('storage/' . $student->mother_signature) }}" 
                                     alt="Mother Signature" 
                                     class="h-16 object-contain">
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Not available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Address Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-green-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-map-marker-alt text-green-600 mr-3"></i>
                Address Information
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-3 pb-2 border-b-2 border-blue-500 inline-block">
                        Permanent Address
                    </h4>
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-900">{{ $student->permanent_address }}</p>
                        <p class="text-sm text-gray-600">{{ $student->permanent_city }}, {{ $student->permanent_state }}</p>
                        <p class="text-sm text-gray-600">PIN: {{ $student->permanent_pin }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-3 pb-2 border-b-2 border-purple-500 inline-block">
                        Correspondence Address
                    </h4>
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-900">{{ $student->correspondence_address }}</p>
                        <p class="text-sm text-gray-600">{{ $student->correspondence_city }}, {{ $student->correspondence_state }}</p>
                        <p class="text-sm text-gray-600">PIN: {{ $student->correspondence_pin }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
