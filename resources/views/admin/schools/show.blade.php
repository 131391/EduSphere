@extends('layouts.admin')

@section('title', 'School Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $school->name }}</h1>
            <p class="text-gray-600 mt-1">School Details & Information</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.schools.edit', $school->id) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ route('admin.schools.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- School Info Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-blue-600 text-white">
            <div class="flex items-center">
                @if($school->logo)
                <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-20 h-20 rounded-full mr-4 border-4 border-white">
                @else
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mr-4 border-4 border-white">
                    <i class="fas fa-school text-blue-600 text-3xl"></i>
                </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold">{{ $school->name }}</h2>
                    <p class="text-blue-100">{{ $school->code }} | {{ $school->subdomain }}.edusphere.local</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Basic Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">School Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">School Code</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subdomain</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->subdomain }}.edusphere.local</dd>
                        </div>
                        @if($school->domain)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Custom Domain</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->domain }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($school->status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @elseif($school->status === 'inactive')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Suspended</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Contact Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->phone }}</dd>
                        </div>
                        @if($school->website)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="mt-1 text-sm text-blue-600">
                                <a href="{{ $school->website }}" target="_blank" class="hover:underline">{{ $school->website }}</a>
                            </dd>
                        </div>
                        @endif
                        @if($school->address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $school->address }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($school->city || $school->state)
                                {{ $school->city }}{{ $school->city && $school->state ? ', ' : '' }}{{ $school->state }}
                                @endif
                                @if($school->pincode)
                                - {{ $school->pincode }}
                                @endif
                                @if($school->country)
                                <br>{{ $school->country }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Subscription Information -->
            @if($school->subscription_start_date || $school->subscription_end_date)
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Subscription Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($school->subscription_start_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $school->subscription_start_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                    @if($school->subscription_end_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">End Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $school->subscription_end_date->format('M d, Y') }}
                            @if($school->isSubscriptionActive())
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $school->users()->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Students</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $school->students()->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-graduate text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Teachers</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $school->teachers()->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Classes</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $school->classes()->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

