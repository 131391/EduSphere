@extends('layouts.receptionist')

@section('title', 'Visitor Details')
@section('page-title', 'Visitor Details')
@section('page-description', 'View visitor information and meeting details')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header Actions --}}
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('receptionist.visitors.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to List
        </a>
        <div class="flex gap-3">
            @if(!$visitor->check_out)
            <button onclick="document.getElementById('checkout-form').submit()" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Check Out
            </button>
            <form id="checkout-form" action="{{ route('receptionist.visitors.check-out', $visitor->id) }}" method="POST" class="hidden">
                @csrf
            </form>
            @endif
        </div>
    </div>

    {{-- Hero Section --}}
    <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-xl shadow-lg p-8 mb-8 text-white">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            {{-- Visitor Photo --}}
            <div class="flex-shrink-0">
                @if($visitor->visitor_photo)
                    <img src="{{ asset('storage/' . $visitor->visitor_photo) }}" 
                         alt="Visitor Photo" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-white/20 flex items-center justify-center border-4 border-white shadow-lg">
                        <i class="fas fa-user text-6xl text-white/60"></i>
                    </div>
                @endif
            </div>

            {{-- Visitor Info --}}
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-3xl font-bold mb-2">{{ $visitor->name }}</h1>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start mb-4">
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm font-medium">
                        <i class="fas fa-id-card mr-1"></i>
                        {{ $visitor->visitor_no }}
                    </span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm font-medium capitalize">
                        @php
                            $status = $visitor->status instanceof \App\Enums\VisitorStatus 
                                ? $visitor->status 
                                : \App\Enums\VisitorStatus::Scheduled;
                            $statusColor = match($status) {
                                \App\Enums\VisitorStatus::Completed => 'text-green-300',
                                \App\Enums\VisitorStatus::CheckedIn => 'text-blue-300',
                                \App\Enums\VisitorStatus::Cancelled => 'text-red-300',
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
                        <i class="fas fa-user-tag mr-2"></i>
                        <span class="font-semibold">Type:</span> {{ $visitor->visitor_type }}
                    </div>
                    <div>
                        <i class="fas fa-calendar mr-2"></i>
                        <span class="font-semibold">Visit Date:</span> {{ $visitor->created_at->format('d M, Y') }}
                    </div>
                    <div>
                        <i class="fas fa-clock mr-2"></i>
                        <span class="font-semibold">Visit Time:</span> {{ $visitor->created_at->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Meeting Type --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        @php
                            $meetingTypeValue = $visitor->meeting_type instanceof \App\Enums\VisitorMode 
                                ? $visitor->meeting_type->value 
                                : $visitor->meeting_type;
                            $icon = match($meetingTypeValue) {
                                1 => 'video', // Online
                                3 => 'laptop', // Office
                                default => 'building', // Offline
                            };
                        @endphp
                        <i class="fas fa-{{ $icon }} text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Meeting Type</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $visitor->meeting_type instanceof \App\Enums\VisitorMode ? $visitor->meeting_type->label() : ($visitor->meeting_type ?? 'N/A') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Priority --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-orange-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Priority</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $visitor->priority instanceof \App\Enums\VisitorPriority ? $visitor->priority->label() : ($visitor->priority ?? 'N/A') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Check-in Time --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Check-in</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $visitor->check_in ? $visitor->check_in->format('h:i A') : 'Not checked in' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- No. of Guests --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Guests</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $visitor->no_of_guests ?? 1 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Information --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Visitor Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-teal-50 to-teal-100 px-6 py-4 border-b border-teal-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user-circle text-teal-600 mr-3"></i>
                    Visitor Information
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start">
                    <i class="fas fa-phone w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Mobile Number</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->mobile }}</p>
                    </div>
                </div>
                @if($visitor->email)
                <div class="flex items-start">
                    <i class="fas fa-envelope w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Email Address</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->email }}</p>
                    </div>
                </div>
                @endif
                @if($visitor->address)
                <div class="flex items-start">
                    <i class="fas fa-map-marker-alt w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->address }}</p>
                    </div>
                </div>
                @endif
                <div class="flex items-start">
                    <i class="fas fa-clipboard-list w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Visit Purpose</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->visit_purpose }}</p>
                    </div>
                </div>
                @if($visitor->source)
                <div class="flex items-start">
                    <i class="fas fa-info-circle w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Source</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->source }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Meeting Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-handshake text-blue-600 mr-3"></i>
                    Meeting Details
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start">
                    <i class="fas fa-user-tie w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Meeting With</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->meeting_with }}</p>
                    </div>
                </div>
                @if($visitor->meeting_purpose)
                <div class="flex items-start">
                    <i class="fas fa-bullseye w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Meeting Purpose</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->meeting_purpose }}</p>
                    </div>
                </div>
                @endif
                @if($visitor->meeting_scheduled)
                <div class="flex items-start">
                    <i class="fas fa-calendar-check w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Scheduled Time</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->meeting_scheduled->format('d M, Y h:i A') }}</p>
                    </div>
                </div>
                @endif
                @if($visitor->check_out)
                <div class="flex items-start">
                    <i class="fas fa-sign-out-alt w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Check-out Time</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->check_out->format('d M, Y h:i A') }}</p>
                    </div>
                </div>
                @endif
                @if($visitor->notes)
                <div class="flex items-start">
                    <i class="fas fa-sticky-note w-5 text-gray-400 mr-3 mt-1"></i>
                    <div>
                        <p class="text-xs text-gray-500">Notes</p>
                        <p class="text-sm font-medium text-gray-900">{{ $visitor->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Photos Section --}}
    @if($visitor->visitor_photo || $visitor->id_proof)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-images text-purple-600 mr-3"></i>
                Photos & Documents
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Visitor Photo --}}
                @if($visitor->visitor_photo)
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Visitor Photo</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        <img src="{{ asset('storage/' . $visitor->visitor_photo) }}" 
                             alt="Visitor Photo" 
                             class="w-48 h-48 object-cover rounded">
                    </div>
                </div>
                @endif

                {{-- ID Proof --}}
                @if($visitor->id_proof)
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700 mb-3">ID Proof</p>
                    <div class="border-2 border-gray-300 rounded-lg p-3 bg-gray-50 inline-block">
                        @if(str_ends_with($visitor->id_proof, '.pdf'))
                            <a href="{{ asset('storage/' . $visitor->id_proof) }}" target="_blank" class="block">
                                <i class="fas fa-file-pdf text-red-600 text-8xl"></i>
                                <p class="text-sm text-gray-600 mt-2">View PDF</p>
                            </a>
                        @else
                            <img src="{{ asset('storage/' . $visitor->id_proof) }}" 
                                 alt="ID Proof" 
                                 class="w-48 h-48 object-cover rounded">
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
