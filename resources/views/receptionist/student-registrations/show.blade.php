@extends('layouts.receptionist')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header with Actions --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Student Registration Details</h1>
                <p class="mt-1 text-sm text-gray-500">Complete registration information</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('receptionist.student-registrations.pdf', $studentRegistration->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Download PDF
                </a>
                <a href="{{ route('receptionist.student-registrations.edit', $studentRegistration->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('receptionist.student-registrations.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    {{-- Student Profile Hero Section --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="px-8 py-10">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                {{-- Student Photo --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-full bg-white p-1 shadow-lg">
                        @if($studentRegistration->student_photo)
                            <img src="{{ asset('storage/' . $studentRegistration->student_photo) }}" 
                                 alt="{{ $studentRegistration->full_name }}" 
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
                    <h2 class="text-3xl font-bold text-white mb-2">{{ $studentRegistration->full_name }}</h2>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm">
                            <i class="fas fa-id-card mr-2"></i>
                            {{ $studentRegistration->registration_no }}
                        </span>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-500',
                                'admitted' => 'bg-green-500',
                                'cancelled' => 'bg-red-500',
                            ];
                            $statusColor = $statusColors[$studentRegistration->admission_status->value] ?? 'bg-gray-500';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }} text-white">
                            <i class="fas fa-circle text-xs mr-2"></i>
                            {{ $studentRegistration->admission_status->label() }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-white/90">
                        <div class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span>{{ $studentRegistration->class->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>{{ $studentRegistration->academicYear->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-clock mr-2"></i>
                            <span>{{ $studentRegistration->registration_date->format('d M, Y') }}</span>
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
                        <i class="fas fa-venus-mars text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Gender</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $studentRegistration->gender_label }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">{{ $studentRegistration->dob ? $studentRegistration->dob->format('d/m/Y') : 'N/A' }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">{{ $studentRegistration->mobile_no }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rupee-sign text-orange-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Registration Fee</p>
                    <p class="text-lg font-semibold text-gray-900">₹{{ number_format($studentRegistration->registration_fee, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Student Signature --}}
    @if($studentRegistration->student_signature)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
            <i class="fas fa-signature text-indigo-600 mr-2"></i>
            Student Signature
        </h3>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 inline-block bg-gray-50">
            <img src="{{ asset('storage/' . $studentRegistration->student_signature) }}" 
                 alt="Student Signature" 
                 class="h-20 object-contain">
        </div>
    </div>
    @endif

    {{-- Detailed Information Sections --}}
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
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($studentRegistration->father_photo)
                            <img src="{{ asset('storage/' . $studentRegistration->father_photo) }}" 
                                 alt="Father" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-gray-400 text-3xl"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">
                            {{ $studentRegistration->father_name_prefix }} {{ $studentRegistration->father_first_name }} {{ $studentRegistration->father_last_name }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $studentRegistration->father_occupation }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone w-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500">Mobile Number</p>
                            <p class="text-sm font-medium text-gray-900">{{ $studentRegistration->father_mobile_no }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope w-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500">Email Address</p>
                            <p class="text-sm font-medium text-gray-900">{{ $studentRegistration->father_email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @if($studentRegistration->father_signature)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Signature</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 inline-block bg-gray-50">
                            <img src="{{ asset('storage/' . $studentRegistration->father_signature) }}" 
                                 alt="Father Signature" 
                                 class="h-16 object-contain">
                        </div>
                    </div>
                    @endif
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
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($studentRegistration->mother_photo)
                            <img src="{{ asset('storage/' . $studentRegistration->mother_photo) }}" 
                                 alt="Mother" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-gray-400 text-3xl"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">
                            {{ $studentRegistration->mother_name_prefix }} {{ $studentRegistration->mother_first_name }} {{ $studentRegistration->mother_last_name }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $studentRegistration->mother_occupation }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone w-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500">Mobile Number</p>
                            <p class="text-sm font-medium text-gray-900">{{ $studentRegistration->mother_mobile_no }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope w-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500">Email Address</p>
                            <p class="text-sm font-medium text-gray-900">{{ $studentRegistration->mother_email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @if($studentRegistration->mother_signature)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Signature</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 inline-block bg-gray-50">
                            <img src="{{ asset('storage/' . $studentRegistration->mother_signature) }}" 
                                 alt="Mother Signature" 
                                 class="h-16 object-contain">
                        </div>
                    </div>
                    @endif
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
                        <p class="text-sm text-gray-900">{{ $studentRegistration->permanent_address }}</p>
                        <p class="text-sm text-gray-600">{{ $studentRegistration->permanent_city }}, {{ $studentRegistration->permanent_state }}</p>
                        <p class="text-sm text-gray-600">{{ config('countries')[$studentRegistration->permanent_country_id] ?? 'N/A' }} - {{ $studentRegistration->permanent_pin }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-3 pb-2 border-b-2 border-purple-500 inline-block">
                        Correspondence Address
                    </h4>
                    <div class="mt-4 space-y-2">
                        @if($studentRegistration->correspondence_address)
                            <p class="text-sm text-gray-900">{{ $studentRegistration->correspondence_address }}</p>
                            <p class="text-sm text-gray-600">{{ $studentRegistration->correspondence_city }}, {{ $studentRegistration->correspondence_state }}</p>
                            <p class="text-sm text-gray-600">{{ config('countries')[$studentRegistration->correspondence_country_id] ?? 'N/A' }} - {{ $studentRegistration->correspondence_pin }}</p>
                        @else
                            <p class="text-sm text-gray-500 italic">Same as Permanent Address</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
