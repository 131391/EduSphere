@extends('layouts.admin')

@section('title', $school->name . ' - Profile')

@section('content')
<div class="w-full space-y-8 animate-in fade-in duration-700">
    <!-- Page Header & Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-2">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-semibold uppercase tracking-wider">
                    <li class="inline-flex items-center text-gray-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Admin</a>
                    </li>
                    <li>
                        <div class="flex items-center text-gray-400">
                            <i class="fas fa-chevron-right mx-2 text-[10px]"></i>
                            <a href="{{ route('admin.schools.index') }}" class="hover:text-blue-600 transition-colors">Schools</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-blue-600">
                            <i class="fas fa-chevron-right mx-2 text-[10px]"></i>
                            <span>Profile</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">{{ $school->name }}</h1>
            <p class="text-gray-500 mt-2 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                Comprehensive Institutional Profile & Management Console
            </p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.schools.edit', $school->id) }}" class="inline-flex items-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-900 text-sm font-extrabold rounded-2xl border border-gray-200 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                <i class="fas fa-edit mr-2.5 text-amber-500"></i>Edit Profile
            </a>
            <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-900 hover:bg-black text-white text-sm font-extrabold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-gray-200 hover:-translate-y-1">
                <i class="fas fa-arrow-left mr-2.5"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Premium Hero Section -->
    <div class="relative group">
        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-[2.5rem] blur opacity-15 group-hover:opacity-25 transition duration-1000 group-hover:duration-200"></div>
        <div class="relative overflow-hidden bg-white rounded-[2rem] shadow-sm border border-gray-100 min-h-[320px]">
            <!-- Dynamic Background Pattern -->
            <div class="absolute top-0 left-0 w-full h-48 bg-gradient-to-br from-blue-700 via-indigo-600 to-purple-700">
                <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <div class="relative px-10 pt-28 pb-10">
                <div class="flex flex-col md:flex-row items-center md:items-end gap-8">
                    <!-- Brand Identity -->
                    <div class="relative">
                        <div class="absolute -inset-4 bg-white/50 backdrop-blur-xl rounded-[2.5rem] shadow-2xl"></div>
                        @if($school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="relative w-40 h-40 rounded-[2rem] object-cover border-4 border-white shadow-xl bg-white">
                        @else
                            <div class="relative w-40 h-40 bg-white rounded-[2rem] flex items-center justify-center border-4 border-white shadow-xl">
                                <i class="fas fa-school text-blue-600 text-6xl"></i>
                            </div>
                        @endif
                        
                        <!-- Status Badge Overlay -->
                        <div class="absolute -bottom-2 -right-2">
                            @php $status = $school->status; @endphp
                            <div class="p-1 bg-white rounded-2xl shadow-lg ring-1 ring-gray-100">
                                <span class="flex items-center px-4 py-1.5 rounded-xl {{ $status->color() === 'green' ? 'bg-green-500' : ($status->color() === 'red' ? 'bg-red-500' : 'bg-gray-400') }} text-white text-[10px] font-black uppercase tracking-widest shadow-inner">
                                    <i class="fas fa-circle mr-2 text-[6px] animate-pulse"></i>
                                    {{ $status->label() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 text-center md:text-left">
                        <div class="inline-flex items-center px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-white/90 text-[10px] font-black uppercase tracking-[0.2em] mb-3 border border-white/20">
                            Verified Institution
                        </div>
                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tighter">{{ $school->name }}</h2>
                        <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-3">
                            <span class="inline-flex items-center px-4 py-2 rounded-2xl bg-blue-50 text-blue-700 text-xs font-extra-bold tracking-tight border border-blue-100 shadow-sm transition-transform hover:scale-105">
                                <i class="fas fa-fingerprint mr-2 opacity-60"></i>{{ $school->code }}
                            </span>
                            <span class="inline-flex items-center px-4 py-2 rounded-2xl bg-indigo-50 text-indigo-700 text-xs font-extra-bold tracking-tight border border-indigo-100 shadow-sm transition-transform hover:scale-105">
                                <i class="fas fa-link mr-2 opacity-60"></i>{{ $school->subdomain }}.edusphere.local
                            </span>
                        </div>
                    </div>

                    <!-- Meta Quick Stats -->
                    <div class="hidden lg:flex items-center gap-8 border-l border-gray-100 pl-8 mb-4">
                        <div class="text-center">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Users</p>
                            <p class="text-2xl font-black text-gray-900">{{ $school->users()->count() }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Teachers</p>
                            <p class="text-2xl font-black text-gray-900">{{ $school->teachers()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information Column -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Information Grid -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-gray-50 flex items-center bg-gray-50/30">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center mr-4 shadow-lg shadow-blue-200">
                        <i class="fas fa-info text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">General Information</h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Key Institutional Details</p>
                    </div>
                </div>
                <div class="p-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <!-- Contact Core -->
                        <div class="space-y-8">
                            <div class="space-y-6">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Contact & Reach</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center p-4 rounded-2xl border border-gray-50 bg-gray-50/50 hover:bg-white hover:border-blue-100 hover:shadow-md transition-all duration-300 group">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mr-4 group-hover:bg-blue-600 transition-colors">
                                            <i class="fas fa-envelope text-blue-600 group-hover:text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Official Email</p>
                                            <p class="text-sm font-extrabold text-gray-900 tracking-tight">{{ $school->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center p-4 rounded-2xl border border-gray-50 bg-gray-50/50 hover:bg-white hover:border-teal-100 hover:shadow-md transition-all duration-300 group">
                                        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center mr-4 group-hover:bg-teal-600 transition-colors">
                                            <i class="fas fa-phone-alt text-teal-600 group-hover:text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Contact Number</p>
                                            <p class="text-sm font-extrabold text-gray-900 tracking-tight">{{ $school->phone }}</p>
                                        </div>
                                    </div>
                                    @if($school->website)
                                    <div class="flex items-center p-4 rounded-2xl border border-gray-50 bg-gray-50/50 hover:bg-white hover:border-purple-100 hover:shadow-md transition-all duration-300 group">
                                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mr-4 group-hover:bg-purple-600 transition-colors">
                                            <i class="fas fa-link text-purple-600 group-hover:text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Web Address</p>
                                            <p class="text-sm font-extrabold text-gray-900 tracking-tight">{{ $school->website }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Location & Physical -->
                        <div class="space-y-8">
                            <div class="space-y-6">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Physical Location</h4>
                                <div class="p-6 rounded-3xl border border-gray-50 bg-gray-50/50 relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700">
                                        <i class="fas fa-map-marked-alt text-9xl"></i>
                                    </div>
                                    <div class="relative z-10 space-y-4">
                                        <div class="flex items-start">
                                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center mr-4 shadow-sm">
                                                <i class="fas fa-map-marker-alt text-orange-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-extrabold text-gray-900 leading-relaxed">
                                                    {{ $school->address ?? 'Address not specified' }}
                                                </p>
                                                <p class="text-xs font-bold text-gray-500 mt-2 flex items-center">
                                                    <i class="fas fa-city mr-1.5 opacity-50"></i>
                                                    {{ $school->city->name ?? 'N/A' }}{{ $school->state ? ', ' . $school->state->name : '' }}
                                                </p>
                                                <p class="text-xs font-bold text-blue-600 mt-1 flex items-center">
                                                    <i class="fas fa-globe-asia mr-1.5 opacity-50"></i>
                                                    {{ $school->country->name ?? 'India' }} {{ $school->pincode ? '• ' . $school->pincode : '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administrator Profile Card -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group">
                <div class="px-10 py-8 border-b border-gray-50 flex items-center bg-gray-50/30">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center mr-4 shadow-lg shadow-emerald-200 group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-shield text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Primary Administrator</h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Authorized Account Holder</p>
                    </div>
                </div>
                <div class="p-10">
                    @if($admin)
                    <div class="flex flex-col md:flex-row items-center gap-10">
                        <div class="relative">
                            <div class="w-24 h-24 rounded-3xl bg-emerald-50 border-4 border-white shadow-xl flex items-center justify-center">
                                <i class="fas fa-user text-3xl text-emerald-600"></i>
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-green-500 border-4 border-white flex items-center justify-center shadow-lg">
                                <i class="fas fa-shield-alt text-white text-[10px]"></i>
                            </div>
                        </div>
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Full Name</p>
                                <p class="text-lg font-black text-gray-900 tracking-tight">{{ $admin->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Login Email</p>
                                <p class="text-lg font-black text-blue-600 hover:underline tracking-tight cursor-pointer">{{ $admin->email }}</p>
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-4 py-2 rounded-2xl bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                Global Admin
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-6 text-center">
                        <div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center mb-4 border border-amber-100 shadow-inner">
                            <i class="fas fa-user-slash text-2xl text-amber-500"></i>
                        </div>
                        <h4 class="text-sm font-black text-gray-900 mb-1">No Primary Admin Assigned</h4>
                        <p class="text-xs text-gray-400 max-w-xs mx-auto leading-relaxed italic">This school does not have an active administrator profile. Please create one from the edit section.</p>
                        <a href="{{ route('admin.schools.edit', $school->id) }}" class="mt-4 text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest">
                            Resolve Now <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Components Column -->
        <div class="space-y-8">
            <!-- Subscription & Billing -->
            <div class="bg-gray-900 rounded-[2.5rem] shadow-2xl p-1 overflow-hidden group">
                <div class="p-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mr-3 border border-white/10">
                                <i class="fas fa-gem text-blue-400"></i>
                            </div>
                            <h3 class="text-lg font-black text-white">Subscription</h3>
                        </div>
                        @if($school->isSubscriptionActive())
                            <span class="flex h-3 w-3 rounded-full bg-green-400 ring-4 ring-green-400/20"></span>
                        @endif
                    </div>

                    @if($school->subscription_end_date)
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Plan Status</span>
                            <span class="text-xs font-black text-white uppercase px-3 py-1 bg-blue-600 rounded-lg">Enterprise</span>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Time Remaining</p>
                                    @php $daysRem = now()->diffInDays($school->subscription_end_date, false); @endphp
                                    <p class="text-3xl font-black text-white tracking-tighter">{{ max(0, $daysRem) }} Days</p>
                                </div>
                                <div class="text-right">
                                    <i class="fas fa-hourglass-half text-blue-400 text-3xl opacity-50"></i>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            @php 
                                $total = $school->subscription_start_date->diffInDays($school->subscription_end_date);
                                $current = $school->subscription_start_date->diffInDays(now());
                                $perc = $total > 0 ? min(100, max(0, ($current / $total) * 100)) : 100;
                            @endphp
                            <div class="w-full h-2.5 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(59,130,246,0.5)]" style="width: {{ $perc }}%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-left pt-2">
                                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Expires On</p>
                                <p class="text-xs font-extrabold text-white mt-1">{{ $school->subscription_end_date->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-8 text-center bg-white/5 rounded-3xl border border-white/10 border-dashed">
                        <i class="fas fa-calendar-times text-3xl text-gray-600 mb-4 block"></i>
                        <h4 class="text-sm font-black text-white mb-2">No Active Limit</h4>
                        <p class="text-xs text-gray-500 italic">This school currently has a lifetime or unassigned access plan.</p>
                        <button class="mt-6 w-full py-3 bg-white text-gray-900 rounded-xl font-black text-xs transition duration-300 hover:bg-blue-500 hover:text-white">ASSIGN PLAN</button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Enhanced Quick Actions -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group">
                <div class="px-10 py-6 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-lg font-black text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-3 text-amber-500"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    <button class="w-full flex items-center justify-between p-4 rounded-3xl hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition-all duration-300 group/btn">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mr-4 group-hover/btn:bg-blue-600 transition-colors">
                                <i class="fas fa-users-cog group-hover/btn:text-white transition-colors"></i>
                            </div>
                            <span class="text-sm font-extrabold tracking-tight">Staffing Overview</span>
                        </div>
                        <i class="fas fa-arrow-right text-[10px] transform -translate-x-2 opacity-0 group-hover/btn:translate-x-0 group-hover/btn:opacity-100 transition-all"></i>
                    </button>
                    
                    <button class="w-full flex items-center justify-between p-4 rounded-3xl hover:bg-purple-50 text-gray-700 hover:text-purple-700 transition-all duration-300 group/btn">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mr-4 group-hover/btn:bg-purple-600 transition-colors">
                                <i class="fas fa-cog group-hover/btn:text-white transition-colors"></i>
                            </div>
                            <span class="text-sm font-extrabold tracking-tight">System Config</span>
                        </div>
                        <i class="fas fa-arrow-right text-[10px] transform -translate-x-2 opacity-0 group-hover/btn:translate-x-0 group-hover/btn:opacity-100 transition-all"></i>
                    </button>

                    <button class="w-full flex items-center justify-between p-4 rounded-3xl hover:bg-red-50 text-gray-700 hover:text-red-700 transition-all duration-300 group/btn">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center mr-4 group-hover/btn:bg-red-600 transition-colors">
                                <i class="fas fa-ban group-hover/btn:text-white transition-colors"></i>
                            </div>
                            <span class="text-sm font-extrabold tracking-tight">Account Restriction</span>
                        </div>
                        <i class="fas fa-arrow-right text-[10px] transform -translate-x-2 opacity-0 group-hover/btn:translate-x-0 group-hover/btn:opacity-100 transition-all"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard Snippet -->
    <div class="space-y-6 pt-8">
        <div class="flex items-center justify-between px-2">
            <h3 class="text-2xl font-black text-gray-900 tracking-tighter">Activity Analytics</h3>
            <button class="text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest">
                Full Report <i class="fas fa-external-link-alt ml-1.5"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Growth Card -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-inner">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="px-3 py-1 bg-green-50 rounded-xl text-[10px] font-black text-green-600 flex items-center">
                        <i class="fas fa-caret-up mr-1 text-xs"></i> 12.5%
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Impact</p>
                <h4 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $school->users()->count() + $school->students()->count() }}</h4>
            </div>

            <!-- Students Card -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500 shadow-inner">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                    <div class="px-3 py-1 bg-blue-50 rounded-xl text-[10px] font-black text-blue-600 flex items-center">
                        Active
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Students</p>
                <h4 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $school->students()->count() }}</h4>
            </div>

            <!-- Teacher Card -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all duration-500 shadow-inner">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                    <div class="px-3 py-1 bg-amber-50 rounded-xl text-[10px] font-black text-amber-600 flex items-center">
                        Stable
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Teachers</p>
                <h4 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $school->teachers()->count() }}</h4>
            </div>

            <!-- Classes Card -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all duration-500 shadow-inner">
                        <i class="fas fa-door-open text-xl"></i>
                    </div>
                    <div class="px-3 py-1 bg-red-50 rounded-xl text-[10px] font-black text-red-600 flex items-center">
                        Limit Near
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Active Classes</p>
                <h4 class="text-4xl font-black text-gray-900 tracking-tighter">{{ $school->classes()->count() }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection
