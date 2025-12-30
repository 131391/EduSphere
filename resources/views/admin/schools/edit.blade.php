@extends('layouts.admin')

@section('title', 'Edit School')

@section('content')
<div class="w-full space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Edit School</h1>
            <p class="text-gray-500 mt-1 flex items-center">
                <i class="fas fa-edit mr-2 text-amber-500"></i>
                Update school profile and configuration
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to List
            </a>
        </div>
    </div>

    <form action="{{ route('admin.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Primary Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mr-4">
                            <i class="fas fa-university text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Basic Information</h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name" class="text-sm font-bold text-gray-700 ml-1">School Name <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-school text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name', $school->name) }}" required
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none @error('name') border-red-500 @enderror"
                                        placeholder="Enter school name">
                                </div>
                                @error('name')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="code" class="text-sm font-bold text-gray-700 ml-1">School Code <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-fingerprint text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="code" id="code" value="{{ old('code', $school->code) }}" required
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none @error('code') border-red-500 @enderror"
                                        placeholder="e.g. SCH001">
                                </div>
                                @error('code')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="subdomain" class="text-sm font-bold text-gray-700 ml-1">Subdomain <span class="text-red-500">*</span></label>
                                <div class="flex group">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="fas fa-link text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                        </div>
                                        <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain', $school->subdomain) }}" required
                                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-l-2xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none @error('subdomain') border-red-500 @enderror"
                                            placeholder="subdomain">
                                    </div>
                                    <span class="inline-flex items-center px-4 bg-gray-100 border border-l-0 border-gray-200 rounded-r-2xl text-gray-500 text-sm font-semibold">
                                        .edusphere.local
                                    </span>
                                </div>
                                @error('subdomain')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="domain" class="text-sm font-bold text-gray-700 ml-1">Custom Domain (Optional)</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-globe text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="domain" id="domain" value="{{ old('domain', $school->domain) }}"
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none @error('domain') border-red-500 @enderror"
                                        placeholder="e.g. school.com">
                                </div>
                                @error('domain')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center mr-4">
                            <i class="fas fa-address-book text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Contact & Location</h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-bold text-gray-700 ml-1">Email Address <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="email" id="email" value="{{ old('email', $school->email) }}"
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none @error('email') border-red-500 @enderror"
                                        placeholder="school@example.com">
                                </div>
                                @error('email')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-bold text-gray-700 ml-1">Phone Number <span class="text-red-500">*</span></label>
                                <x-phone-input name="phone" id="phone" :value="$school->phone" />
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label for="address" class="text-sm font-bold text-gray-700 ml-1">Physical Address</label>
                                <div class="relative group">
                                    <div class="absolute top-3 left-4 pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <textarea name="address" id="address" rows="3"
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none @error('address') border-red-500 @enderror"
                                        placeholder="Enter full address">{{ old('address', $school->address) }}</textarea>
                                </div>
                                @error('address')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="city" class="text-sm font-bold text-gray-700 ml-1">City</label>
                                <input type="text" name="city" id="city" value="{{ old('city', $school->city) }}"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                    placeholder="Enter city">
                            </div>

                            <div class="space-y-2">
                                <label for="state" class="text-sm font-bold text-gray-700 ml-1">State</label>
                                <input type="text" name="state" id="state" value="{{ old('state', $school->state) }}"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                    placeholder="Enter state">
                            </div>

                            <div class="space-y-2">
                                <label for="country_id" class="text-sm font-bold text-gray-700 ml-1">Country</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-globe text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <select name="country_id" id="country_id"
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none appearance-none cursor-pointer">
                                        <option value="">Select Country</option>
                                        @foreach(config('countries') as $id => $name)
                                            <option value="{{ $id }}" {{ old('country_id', $school->country_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                @error('country_id')
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="pincode" class="text-sm font-bold text-gray-700 ml-1">Pincode</label>
                                <input type="text" name="pincode" id="pincode" value="{{ old('pincode', $school->pincode) }}"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                    placeholder="Enter pincode">
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label for="website" class="text-sm font-bold text-gray-700 ml-1">Website URL</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-link text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <input type="url" name="website" id="website" value="{{ old('website', $school->website) }}"
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                        placeholder="https://www.school.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Media -->
            <div class="space-y-6">
                <!-- Status & Subscription Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mr-4">
                            <i class="fas fa-toggle-on text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Status & Plan</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <label for="status" class="text-sm font-bold text-gray-700 ml-1">Account Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none appearance-none cursor-pointer">
                                <option value="active" {{ old('status', $school->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $school->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $school->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="subscription_start_date" class="text-sm font-bold text-gray-700 ml-1">Subscription Start</label>
                            <input type="date" name="subscription_start_date" id="subscription_start_date" 
                                value="{{ old('subscription_start_date', $school->subscription_start_date ? $school->subscription_start_date->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none">
                        </div>

                        <div class="space-y-2">
                            <label for="subscription_end_date" class="text-sm font-bold text-gray-700 ml-1">Subscription End</label>
                            <input type="date" name="subscription_end_date" id="subscription_end_date" 
                                value="{{ old('subscription_end_date', $school->subscription_end_date ? $school->subscription_end_date->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <!-- Logo Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mr-4">
                            <i class="fas fa-image text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">School Branding</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="space-y-4">
                            <label class="text-sm font-bold text-gray-700 ml-1">School Logo</label>
                            
                            <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-200 rounded-3xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative group">
                                @if($school->logo)
                                <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-32 h-32 rounded-2xl object-cover shadow-md mb-4">
                                @else
                                <div class="w-32 h-32 rounded-2xl bg-white flex items-center justify-center shadow-sm mb-4">
                                    <i class="fas fa-school text-gray-300 text-4xl"></i>
                                </div>
                                @endif
                                
                                <div class="text-center">
                                    <p class="text-sm font-bold text-gray-700">Change Logo</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG or SVG (Max 2MB)</p>
                                </div>
                                <input type="file" name="logo" id="logo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            
                            @error('logo')
                            <p class="mt-1 text-xs text-red-600 font-medium text-center">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-500/20 transition-all duration-300 transform hover:-translate-y-1 active:scale-95 flex items-center justify-center">
                        <i class="fas fa-save mr-3"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.schools.index') }}" class="w-full mt-3 py-4 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold rounded-2xl transition-all flex items-center justify-center">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

