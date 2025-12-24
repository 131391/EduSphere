@extends('layouts.admin')

@section('title', 'Create New School')

@section('content')
<div class="w-full mx-auto px-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New School</h1>
        <p class="text-gray-600 mt-2 text-lg">Add a new school to the system and set up its administrator.</p>
    </div>

    <!-- Form Card -->
    <form action="{{ route('admin.schools.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-8">
            <!-- School Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-school text-blue-500 mr-2"></i> School Details
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- School Name -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">School Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 @enderror"
                            placeholder="e.g. Springfield High School">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- School Code -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">School Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('code') border-red-500 @enderror"
                            placeholder="e.g. SCH-001">
                        @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subdomain -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">Subdomain <span class="text-red-500">*</span></label>
                        <div class="flex rounded-lg shadow-sm">
                            <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}"
                                class="flex-1 min-w-0 block w-full px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subdomain') border-red-500 @enderror"
                                placeholder="springfield">
                            <span class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-r-lg">
                                .edusphere.local
                            </span>
                        </div>
                        @error('subdomain')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Custom Domain -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">Custom Domain <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="text" name="domain" id="domain" value="{{ old('domain') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('domain') border-red-500 @enderror"
                            placeholder="e.g. school.com">
                        @error('domain')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Logo -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">School Logo</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors relative">
                            <div class="space-y-1 text-center">
                                <!-- Preview Container -->
                                <div class="mb-4 flex justify-center">
                                    <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden relative">
                                        <img id="logo-preview" src="#" alt="Logo Preview" class="hidden w-full h-full object-contain">
                                        <i class="fas fa-image text-gray-400 text-4xl" id="logo-icon"></i>
                                        
                                        <button type="button" 
                                                id="logo-remove" 
                                                onclick="removeImage(event)"
                                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg z-10">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="logo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="logo" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                            </div>
                        </div>
                        @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Administrator Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-user-shield text-green-500 mr-2"></i> Administrator Details
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Admin Name -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">Admin Name <span class="text-red-500">*</span></label>
                        <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors @error('admin_name') border-red-500 @enderror"
                            placeholder="e.g. John Doe">
                        @error('admin_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Admin Email -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Admin Email <span class="text-red-500">*</span></label>
                        <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors @error('admin_email') border-red-500 @enderror"
                            placeholder="e.g. admin@school.com">
                        @error('admin_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="admin_password" id="admin_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors @error('admin_password') border-red-500 @enderror">
                        @error('admin_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-address-card text-purple-500 mr-2"></i> Contact Information
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- School Email -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">School Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('email') border-red-500 @enderror"
                            placeholder="e.g. contact@school.com">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('phone') border-red-500 @enderror"
                            placeholder="e.g. +1 234 567 8900">
                        @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" id="address" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('address') border-red-500 @enderror"
                            placeholder="Enter full address">{{ old('address') }}</textarea>
                        @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('city') border-red-500 @enderror">
                    </div>

                    <!-- State -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input type="text" name="state" id="state" value="{{ old('state') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('state') border-red-500 @enderror">
                    </div>

                    <!-- Country -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <select name="country_id" id="country_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('country_id') border-red-500 @enderror">
                            <option value="">Select Country</option>
                            @foreach(config('countries') as $id => $name)
                                <option value="{{ $id }}" {{ old('country_id', 1) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('country_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pincode -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                        <input type="text" name="pincode" id="pincode" value="{{ old('pincode') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('pincode') border-red-500 @enderror">
                        @error('pincode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Website -->
                    <div class="col-span-2">
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" name="website" id="website" value="{{ old('website') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('website') border-red-500 @enderror"
                            placeholder="https://example.com">
                        @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearError(this);
        });
        input.addEventListener('change', function() {
            clearError(this);
        });
    });
});

function clearError(element) {
    if (element.classList.contains('border-red-500')) {
        element.classList.remove('border-red-500');
        // Find the error message element (p tag with text-red-600) immediately following the input
        let nextSibling = element.nextElementSibling;
        while(nextSibling) {
            if (nextSibling.tagName === 'P' && nextSibling.classList.contains('text-red-600')) {
                nextSibling.remove();
                break;
            }
            nextSibling = nextSibling.nextElementSibling;
        }
    }
}

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            const icon = document.getElementById('logo-icon');
            const removeBtn = document.getElementById('logo-remove');
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) icon.classList.add('hidden');
            if (removeBtn) removeBtn.classList.remove('hidden');
            
            // Clear error for logo input
            clearError(document.getElementById('logo'));
        };
        reader.readAsDataURL(file);
    }
}

function removeImage(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const input = document.getElementById('logo');
    const preview = document.getElementById('logo-preview');
    const icon = document.getElementById('logo-icon');
    const removeBtn = document.getElementById('logo-remove');
    
    if (input) input.value = '';
    
    if (preview) {
        preview.src = '#';
        preview.classList.add('hidden');
    }
    if (icon) icon.classList.remove('hidden');
    if (removeBtn) removeBtn.classList.add('hidden');
}
</script>

            <!-- Subscription & Status Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-cog text-gray-500 mr-2"></i> Settings & Subscription
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status -->
                    <div class="col-span-3 md:col-span-1">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="col-span-3 md:col-span-1">
                        <label for="subscription_start_date" class="block text-sm font-medium text-gray-700 mb-1">Subscription Start Date</label>
                        <input type="date" name="subscription_start_date" id="subscription_start_date" value="{{ old('subscription_start_date') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors @error('subscription_start_date') border-red-500 @enderror">
                    </div>

                    <!-- End Date -->
                    <div class="col-span-3 md:col-span-1">
                        <label for="subscription_end_date" class="block text-sm font-medium text-gray-700 mb-1">Subscription End Date</label>
                        <input type="date" name="subscription_end_date" id="subscription_end_date" value="{{ old('subscription_end_date') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors @error('subscription_end_date') border-red-500 @enderror">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.schools.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                <i class="fas fa-save mr-2"></i> Create School
            </button>
        </div>
    </form>
</div>
@endsection

