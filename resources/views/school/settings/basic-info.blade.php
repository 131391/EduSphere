@extends('layouts.school')

@section('title', 'Basic Information - ' . $school->name)

@section('content')
<div class="w-full space-y-8 animate-in fade-in duration-700">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-2">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-semibold uppercase tracking-wider">
                    <li class="inline-flex items-center text-gray-400">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li>
                        <div class="flex items-center text-gray-400">
                            <i class="fas fa-chevron-right mx-2 text-[10px]"></i>
                            <span class="hover:text-indigo-600 transition-colors">Settings</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[10px]"></i>
                            <span>Basic Info</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Basic Information</h1>
            <p class="text-gray-500 mt-2 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2 animate-pulse"></span>
                Manage your institution's core identity and contact details
            </p>
        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative group">
        <div class="absolute -inset-1 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl blur opacity-20"></div>
        <div class="relative bg-white border border-green-100 p-4 rounded-2xl flex items-center shadow-sm">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mr-4">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <p class="text-green-800 font-bold">{{ session('success') }}</p>
            <button @click="show = false" class="ml-auto text-green-400 hover:text-green-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Premium Hero Section -->
    <div class="relative group">
        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-[2.5rem] blur opacity-15 group-hover:opacity-25 transition duration-1000"></div>
        <div class="relative overflow-hidden bg-white rounded-[2rem] shadow-sm border border-gray-100 min-h-[280px]">
            <!-- Dynamic Background Pattern -->
            <div class="absolute top-0 left-0 w-full h-40 bg-gradient-to-br from-indigo-700 via-blue-600 to-indigo-800">
                <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <div class="relative px-8 pt-20 pb-8">
                <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                    <!-- Brand Identity -->
                    <div class="relative">
                        <div class="absolute -inset-3 bg-white/50 backdrop-blur-xl rounded-[2.5rem] shadow-2xl"></div>
                        @if($school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="relative w-32 h-32 rounded-[2rem] object-cover border-4 border-white shadow-xl bg-white">
                        @else
                            <div class="relative w-32 h-32 bg-white rounded-[2rem] flex items-center justify-center border-4 border-white shadow-xl">
                                <i class="fas fa-book text-indigo-600 text-5xl"></i>
                            </div>
                        @endif
                        
                        <a href="{{ route('school.settings.logo') }}" class="absolute -bottom-2 -right-2 p-2 bg-indigo-600 text-white rounded-xl shadow-lg hover:bg-indigo-700 transition-transform hover:scale-110 border-2 border-white">
                            <i class="fas fa-camera text-xs"></i>
                        </a>
                    </div>

                    <div class="flex-1 text-center md:text-left">
                        <div class="inline-flex items-center px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-white/90 text-[10px] font-black uppercase tracking-[0.2em] mb-2 border border-white/20">
                            Institution Dashboard
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tighter">{{ $school->name }}</h2>
                        <div class="mt-3 flex flex-wrap justify-center md:justify-start gap-2">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-[11px] font-bold border border-indigo-100">
                                <i class="fas fa-fingerprint mr-1.5 opacity-60"></i>{{ $school->code }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100">
                                <i class="fas fa-link mr-1.5 opacity-60"></i>{{ $school->subdomain }}.edusphere.local
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Form -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Left: Basic Details Form -->
        <div class="xl:col-span-2 space-y-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center bg-gray-50/50">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center mr-4">
                        <i class="fas fa-info-circle text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">General Information</h3>
                        <p class="text-xs text-gray-400 font-medium">Update institutional name and core details</p>
                    </div>
                </div>

                <form action="{{ route('school.settings.basic-info.update') }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- School Name -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Institutional Name <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-school text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                </div>
                                <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                    placeholder="Enter school name">
                            </div>
                            @error('name') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Official Email</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                </div>
                                <input type="email" name="email" value="{{ old('email', $school->email) }}"
                                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                    placeholder="school@example.com">
                            </div>
                            @error('email') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Phone Number</label>
                            <x-phone-input name="phone" :value="$school->phone" />
                        </div>

                        <!-- Website -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Website URL</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-globe text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                </div>
                                <input type="url" name="website" value="{{ old('website', $school->website) }}"
                                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                    placeholder="https://www.school.edu">
                            </div>
                            @error('website') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Separator -->
                        <div class="md:col-span-2 pt-4 pb-2">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                                <span class="mr-3">Location Details</span>
                                <span class="flex-1 h-px bg-gray-100"></span>
                            </h4>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Mailing Address</label>
                            <div class="relative group">
                                <div class="absolute top-3 left-4 flex items-start pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                </div>
                                <textarea name="address" rows="3"
                                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium resize-none"
                                    placeholder="Enter full postal address">{{ old('address', $school->address) }}</textarea>
                            </div>
                            @error('address') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Country -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Country</label>
                            <select name="country_id" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                data-location-cascade="true"
                                data-country-select="true">
                                <option value="">Select Country</option>
                                @if($school->country_id)
                                    <option value="{{ $school->country_id }}" selected>{{ $school->country->name ?? 'Selected Country' }}</option>
                                @endif
                            </select>
                        </div>

                        <!-- State -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">State</label>
                            <select name="state_id" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                data-state-select="true"
                                data-selected="{{ old('state_id', $school->state_id) }}">
                                <option value="">Select State</option>
                            </select>
                        </div>

                        <!-- City -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">City</label>
                            <select name="city_id" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                data-city-select="true"
                                data-selected="{{ old('city_id', $school->city_id) }}">
                                <option value="">Select City</option>
                            </select>
                        </div>

                        <!-- Pincode -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">PIN / Postal Code</label>
                            <input type="text" name="pincode" value="{{ old('pincode', $school->pincode) }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                placeholder="Enter pincode">
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-12 gap-4">
                        <button type="reset" class="px-8 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-black rounded-2xl transition-all duration-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-2xl transition-all duration-300 shadow-lg shadow-indigo-200 hover:-translate-y-1">
                            Save Institutional Details
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Meta Info & Help -->
        <div class="space-y-8">
            <!-- Institutional Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-[2rem] p-8 text-white shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-colors duration-700"></div>
                
                <div class="relative z-10">
                    <h3 class="text-xl font-bold mb-6">Quick Overview</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl"></i>
                            </div>
                            <div>
                                <p class="text-white/60 text-[10px] font-black uppercase tracking-widest">Active Since</p>
                                <p class="text-sm font-bold">{{ $school->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                                <i class="fas fa-shield-alt text-xl"></i>
                            </div>
                            <div>
                                <p class="text-white/60 text-[10px] font-black uppercase tracking-widest">Plan Type</p>
                                <p class="text-sm font-bold">Premium Enterprise</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                                <p class="text-white/60 text-[10px] font-black uppercase tracking-widest">User Capacity</p>
                                <p class="text-sm font-bold">{{ $school->users_count ?? $school->users()->count() }} Active Users</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Documentation -->
            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Note</h3>
                <div class="space-y-4">
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <div class="flex gap-3">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-1"></i>
                            <p class="text-xs text-amber-800 font-medium leading-relaxed">
                                Changes to institution name or subdomain may affect existing login sessions. Ensure all staff are notified of major updates.
                            </p>
                        </div>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                        <div class="flex gap-3">
                            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                            <p class="text-xs text-blue-800 font-medium leading-relaxed">
                                Your subdomain is currently set to <span class="font-bold underline">{{ $school->subdomain }}</span>. Support for custom domains is available in enterprise plans.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
