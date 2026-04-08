@extends('layouts.school')

@section('title', 'School Settings - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500">
    <!-- Page Header (Optimized Legibility) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-1.5 text-[10px] text-gray-300"></i>
                            <span>Settings</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Basic Information</h1>
            <p class="text-sm text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Institutional identity and contact parameters
            </p>
        </div>
    </div>



    <div class="w-full">
        <form action="{{ route('school.settings.basic-info.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Main Identity Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-blue-500 to-indigo-500"></div>
                
                <div class="p-8">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-university text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Institutional Identity</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Core school details</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">School Name</label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}" 
                                   class="w-full px-5 py-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-base" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Official Email</label>
                            <input type="email" name="email" value="{{ old('email', $school->email) }}" 
                                   class="w-full px-5 py-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-base" required>
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Contact Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" 
                                   class="w-full px-5 py-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-base">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Geography -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Location Details</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Geographical information</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Full Address</label>
                            <input type="text" name="address" value="{{ old('address', $school->address) }}" 
                                   class="w-full px-5 py-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-base">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">City</label>
                            <input type="text" name="city_name" value="{{ old('city_name', $school->city->name ?? '') }}" readonly
                                   class="w-full px-5 py-3 bg-gray-100 border-transparent rounded-xl text-gray-500 font-bold text-base cursor-not-allowed">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">ZIP/Pincode</label>
                            <input type="text" name="pincode" value="{{ old('pincode', $school->pincode) }}" 
                                   class="w-full px-5 py-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-base">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Web Appearance -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
                <div class="p-8">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mr-4 flex-shrink-0 group-hover:bg-teal-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-globe text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Web Presence</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Online identity</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Official Website</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-gray-400">
                                    <i class="fas fa-link text-sm"></i>
                                </span>
                                <input type="url" name="website" value="{{ old('website', $school->website) }}" 
                                       class="w-full pl-12 pr-5 py-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-base" placeholder="https://">
                            </div>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full md:w-auto px-10 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-xl transition-all duration-300 shadow-lg shadow-indigo-100 hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <i class="fas fa-save text-xs"></i>
                                Update Institutional Records
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
