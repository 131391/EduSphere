@extends('layouts.admin')

@section('title', 'School Details')

@section('content')
<div class="w-full space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $school->name }}</h1>
            <p class="text-gray-500 mt-1 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                Comprehensive School Profile & Analytics
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.schools.edit', $school->id) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-edit mr-2"></i>Edit Profile
            </a>
            <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to List
            </a>
        </div>
    </div>

    <!-- School Hero Section -->
    <div class="relative overflow-hidden bg-white rounded-3xl shadow-sm border border-gray-100">
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-90"></div>
        <div class="relative px-8 pt-12 pb-8">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="relative">
                    @if($school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-xl bg-white">
                    @else
                    <div class="w-32 h-32 bg-white rounded-2xl flex items-center justify-center border-4 border-white shadow-xl">
                        <i class="fas fa-school text-blue-600 text-5xl"></i>
                    </div>
                    @endif
                    <div class="absolute -bottom-2 -right-2">
                        @if($school->status === 'active')
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 ring-4 ring-white" title="Active">
                            <i class="fas fa-check text-[10px] text-white"></i>
                        </span>
                        @elseif($school->status === 'inactive')
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-400 ring-4 ring-white" title="Inactive">
                            <i class="fas fa-minus text-[10px] text-white"></i>
                        </span>
                        @else
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500 ring-4 ring-white" title="Suspended">
                            <i class="fas fa-exclamation text-[10px] text-white"></i>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl font-bold text-gray-900">{{ $school->name }}</h2>
                    <div class="mt-2 flex flex-wrap justify-center md:justify-start gap-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider border border-blue-100">
                            <i class="fas fa-hashtag mr-1.5 opacity-70"></i>{{ $school->code }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
                            <i class="fas fa-globe mr-1.5 opacity-70"></i>{{ $school->subdomain }}.edusphere.local
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic & Contact Info -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-id-card mr-3 text-blue-600"></i>
                        General Information
                    </h3>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">School Identity</label>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-3 mt-0.5">
                                            <i class="fas fa-university text-blue-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $school->name }}</p>
                                            <p class="text-xs text-gray-500">Official Registered Name</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center mr-3 mt-0.5">
                                            <i class="fas fa-fingerprint text-indigo-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $school->code }}</p>
                                            <p class="text-xs text-gray-500">Unique School Code</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Digital Presence</label>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center mr-3 mt-0.5">
                                            <i class="fas fa-link text-purple-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $school->subdomain }}.edusphere.local</p>
                                            <p class="text-xs text-gray-500">System Subdomain</p>
                                        </div>
                                    </div>
                                    @if($school->domain)
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center mr-3 mt-0.5">
                                            <i class="fas fa-globe-americas text-pink-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $school->domain }}</p>
                                            <p class="text-xs text-gray-500">Custom Domain</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Contact Details</label>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mr-3 mt-0.5">
                                            <i class="fas fa-envelope text-green-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $school->email }}</p>
                                            <p class="text-xs text-gray-500">Primary Email Address</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center mr-3 mt-0.5">
                                            <i class="fas fa-phone-alt text-teal-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $school->phone }}</p>
                                            <p class="text-xs text-gray-500">Contact Number</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($school->address || $school->city)
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Physical Location</label>
                                <div class="mt-2 flex items-start">
                                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center mr-3 mt-0.5">
                                        <i class="fas fa-map-marker-alt text-orange-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 leading-relaxed">
                                            {{ $school->address }}<br>
                                            {{ $school->city }}{{ $school->state ? ', ' . $school->state : '' }} {{ $school->pincode }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $school->country ?? 'India' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Details Column -->
        <div class="space-y-6">
            <!-- Subscription Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-credit-card mr-3 text-emerald-600"></i>
                        Subscription
                    </h3>
                </div>
                <div class="p-6">
                    @if($school->subscription_start_date || $school->subscription_end_date)
                    <div class="space-y-5">
                        <div class="flex items-center justify-between p-4 rounded-2xl {{ $school->isSubscriptionActive() ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full {{ $school->isSubscriptionActive() ? 'bg-emerald-500' : 'bg-red-500' }} flex items-center justify-center text-white mr-3">
                                    <i class="fas {{ $school->isSubscriptionActive() ? 'fa-check' : 'fa-times' }}"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold {{ $school->isSubscriptionActive() ? 'text-emerald-900' : 'text-red-900' }}">
                                        {{ $school->isSubscriptionActive() ? 'Active Plan' : 'Expired' }}
                                    </p>
                                    <p class="text-xs {{ $school->isSubscriptionActive() ? 'text-emerald-600' : 'text-red-600' }}">Premium Access</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Start Date</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ $school->subscription_start_date ? $school->subscription_start_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Expiry Date</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ $school->subscription_end_date ? $school->subscription_end_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No active subscription</p>
                        <button class="mt-4 text-sm font-bold text-blue-600 hover:text-blue-700">Assign Plan <i class="fas fa-arrow-right ml-1"></i></button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-3 text-amber-500"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition-colors group">
                        <div class="flex items-center">
                            <i class="fas fa-user-shield mr-3 text-gray-400 group-hover:text-blue-500"></i>
                            <span class="text-sm font-semibold">Manage Admins</span>
                        </div>
                        <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                    <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 transition-colors group">
                        <div class="flex items-center">
                            <i class="fas fa-cog mr-3 text-gray-400 group-hover:text-indigo-500"></i>
                            <span class="text-sm font-semibold">System Settings</span>
                        </div>
                        <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                    <button class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-red-50 text-gray-700 hover:text-red-700 transition-colors group">
                        <div class="flex items-center">
                            <i class="fas fa-ban mr-3 text-gray-400 group-hover:text-red-500"></i>
                            <span class="text-sm font-semibold">Suspend School</span>
                        </div>
                        <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="space-y-4">
        <h3 class="text-xl font-extrabold text-gray-900 flex items-center px-2">
            <i class="fas fa-chart-pie mr-3 text-indigo-600"></i>
            School Analytics
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Users -->
            <div class="group bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-lg">+12%</span>
                </div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Users</p>
                <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $school->users()->count() }}</h4>
            </div>

            <!-- Total Students -->
            <div class="group bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">Stable</span>
                </div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Students</p>
                <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $school->students()->count() }}</h4>
            </div>

            <!-- Total Teachers -->
            <div class="group bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded-lg">Active</span>
                </div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Teachers</p>
                <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $school->teachers()->count() }}</h4>
            </div>

            <!-- Total Classes -->
            <div class="group bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                        <i class="fas fa-door-open text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-purple-500 bg-purple-50 px-2 py-1 rounded-lg">Full</span>
                </div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Classes</p>
                <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $school->classes()->count() }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection

