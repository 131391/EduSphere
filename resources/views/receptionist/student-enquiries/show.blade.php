@extends('layouts.receptionist')

@section('title', 'Enquiry Details')
@section('page-title', 'Student Enquiry Details')
@section('page-description', 'View student enquiry information')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8">
    {{-- Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Student Enquiry Details</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View student enquiry information</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('receptionist.student-enquiries.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    {{-- Hero Section --}}
    <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-xl shadow-lg p-8 mb-8 text-white">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            {{-- Student Photo --}}
            <div class="flex-shrink-0">
                @if($studentEnquiry->student_photo)
                    <img src="{{ asset('storage/' . $studentEnquiry->student_photo) }}" 
                         alt="Student Photo" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-white/20 flex items-center justify-center border-4 border-white shadow-lg">
                        <i class="fas fa-user-graduate text-6xl text-white/60"></i>
                    </div>
                @endif
            </div>

            {{-- Student Info --}}
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-3xl font-bold mb-2">{{ $studentEnquiry->student_name }}</h1>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start mb-4">
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm font-medium">
                        <i class="fas fa-id-card mr-1"></i>
                        {{ $studentEnquiry->enquiry_no }}
                    </span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm font-medium capitalize">
                        @php
                            $status = $studentEnquiry->form_status instanceof \App\Enums\EnquiryStatus 
                                ? $studentEnquiry->form_status 
                                : \App\Enums\EnquiryStatus::Pending;
                            $statusColor = match($status) {
                                \App\Enums\EnquiryStatus::Completed => 'text-blue-300',
                                \App\Enums\EnquiryStatus::Admitted => 'text-green-300',
                                \App\Enums\EnquiryStatus::Cancelled => 'text-red-300',
                                default => 'text-yellow-300',
                            };
                            $statusLabel = $status->label();
                        @endphp
                        <i class="fas fa-circle mr-1 {{ $statusColor }}"></i>
                        {{ $statusLabel }}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <i class="fas fa-graduation-cap mr-2"></i>
                        <span class="font-semibold">Class:</span> {{ $studentEnquiry->class->name ?? 'N/A' }}
                    </div>
                    <div>
                        <i class="fas fa-calendar mr-2"></i>
                        <span class="font-semibold">Enquiry Date:</span> {{ $studentEnquiry->enquiry_date->format('d M, Y') }}
                    </div>
                    <div>
                        <i class="fas fa-calendar-check mr-2"></i>
                        <span class="font-semibold">Follow Up:</span> {{ $studentEnquiry->follow_up_date ? $studentEnquiry->follow_up_date->format('d M, Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Academic Year --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Academic Year</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $studentEnquiry->academicYear->name ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Gender --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-venus-mars text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Gender</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $studentEnquiry->gender instanceof \App\Enums\Gender ? $studentEnquiry->gender->label() : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Contact Number --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-phone text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Contact</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $studentEnquiry->contact_no }}</p>
                </div>
            </div>
        </div>

        {{-- WhatsApp --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fab fa-whatsapp text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">WhatsApp</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $studentEnquiry->whatsapp_no }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Information --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Father's Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user-tie text-blue-600 mr-3"></i>
                    Father's Details
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start">
                    <i class="fas fa-user w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Name</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->father_name }}</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-phone w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Contact</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->father_contact }}</p>
                    </div>
                </div>
                @if($studentEnquiry->father_email)
                <div class="flex items-start">
                    <i class="fas fa-envelope w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->father_email }}</p>
                    </div>
                </div>
                @endif
                @if($studentEnquiry->father_occupation)
                <div class="flex items-start">
                    <i class="fas fa-briefcase w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Occupation</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->father_occupation }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Mother's Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-pink-50 to-pink-100 px-6 py-4 border-b border-pink-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user text-pink-600 mr-3"></i>
                    Mother's Details
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start">
                    <i class="fas fa-user w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Name</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->mother_name }}</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-phone w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Contact</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->mother_contact }}</p>
                    </div>
                </div>
                @if($studentEnquiry->mother_email)
                <div class="flex items-start">
                    <i class="fas fa-envelope w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->mother_email }}</p>
                    </div>
                </div>
                @endif
                @if($studentEnquiry->mother_occupation)
                <div class="flex items-start">
                    <i class="fas fa-briefcase w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Occupation</p>
                        <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->mother_occupation }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Personal Information --}}
    @if($studentEnquiry->dob || $studentEnquiry->aadhar_no || $studentEnquiry->category || $studentEnquiry->religion)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-teal-50 to-teal-100 px-6 py-4 border-b border-teal-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-info-circle text-teal-600 mr-3"></i>
                Personal Information
            </h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if($studentEnquiry->dob)
            <div class="flex items-start">
                <i class="fas fa-birthday-cake w-5 text-gray-400 mr-3 mt-1"></i>
                <div>
                    <p class="text-xs text-gray-500">Date of Birth</p>
                    <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->dob->format('d M, Y') }}</p>
                </div>
            </div>
            @endif
            @if($studentEnquiry->aadhar_no)
            <div class="flex items-start">
                <i class="fas fa-id-card w-5 text-gray-400 mr-3 mt-1"></i>
                <div>
                    <p class="text-xs text-gray-500">Aadhar Number</p>
                    <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->aadhar_no }}</p>
                </div>
            </div>
            @endif
            @if($studentEnquiry->category)
            <div class="flex items-start">
                <i class="fas fa-tags w-5 text-gray-400 mr-3 mt-1"></i>
                <div>
                    <p class="text-xs text-gray-500">Category</p>
                    <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->category }}</p>
                </div>
            </div>
            @endif
            @if($studentEnquiry->religion)
            <div class="flex items-start">
                <i class="fas fa-pray w-5 text-gray-400 mr-3 mt-1"></i>
                <div>
                    <p class="text-xs text-gray-500">Religion</p>
                    <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->religion }}</p>
                </div>
            </div>
            @endif
            @if($studentEnquiry->permanent_address)
            <div class="flex items-start md:col-span-2">
                <i class="fas fa-map-marker-alt w-5 text-gray-400 mr-3 mt-1"></i>
                <div>
                    <p class="text-xs text-gray-500">Address</p>
                    <p class="text-sm font-medium text-gray-900">{{ $studentEnquiry->permanent_address }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Photos Section --}}
    @if($studentEnquiry->student_photo || $studentEnquiry->father_photo || $studentEnquiry->mother_photo)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-images text-purple-600 mr-3"></i>
                Photos
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @if($studentEnquiry->student_photo)
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Student Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        <img src="{{ asset('storage/' . $studentEnquiry->student_photo) }}" 
                             alt="Student Photo" 
                             class="w-48 h-48 object-cover rounded">
                    </div>
                </div>
                @endif

                @if($studentEnquiry->father_photo)
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Father's Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        <img src="{{ asset('storage/' . $studentEnquiry->father_photo) }}" 
                             alt="Father Photo" 
                             class="w-48 h-48 object-cover rounded">
                    </div>
                </div>
                @endif

                @if($studentEnquiry->mother_photo)
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Mother's Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        <img src="{{ asset('storage/' . $studentEnquiry->mother_photo) }}" 
                             alt="Mother Photo" 
                             class="w-48 h-48 object-cover rounded">
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
