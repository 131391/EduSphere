@extends('layouts.admin')

@section('title', 'Create New School')

@section('content')
<div class="w-full mx-auto" x-data="adminSchoolCreate()">
    <!-- Page Header -->
    <x-page-header title="Create New School" description="Add a new school to the system and set up its administrator." icon="fas fa-school">
        <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-xl border border-gray-200 dark:border-gray-600 transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2 text-gray-400 text-xs"></i> Back to List
        </a>
    </x-page-header>

    <!-- Form Card -->
    <form @submit.prevent="submitForm" action="{{ route('admin.schools.store') }}" method="POST" enctype="multipart/form-data" id="schoolCreateForm">
        @csrf
        
        <div class="space-y-8">
            <!-- School Details Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-school text-blue-500 mr-2"></i> School Details
                    </h3>
                </div>
                <div class="p-6 dark:bg-gray-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- School Name -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" x-model="formData.name"
                            @input="clearError('name')"
                            :class="hasError('name') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="e.g. Springfield High School">
                        <template x-if="hasError('name')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('name')"></p>
                        </template>
                    </div>

                    <!-- School Code -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" x-model="formData.code"
                            @input="clearError('code')"
                            :class="hasError('code') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="e.g. SCH-001">
                        <template x-if="hasError('code')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('code')"></p>
                        </template>
                    </div>

                    <!-- Subdomain -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="subdomain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subdomain <span class="text-red-500">*</span></label>
                        <div class="flex rounded-lg shadow-sm">
                            <input type="text" name="subdomain" id="subdomain" x-model="formData.subdomain"
                                @input="clearError('subdomain')"
                                :class="hasError('subdomain') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                                class="flex-1 min-w-0 block w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 border rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="springfield">
                            <span class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-sm rounded-r-lg">
                                .edusphere.local
                            </span>
                        </div>
                        <template x-if="hasError('subdomain')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('subdomain')"></p>
                        </template>
                    </div>

                    <!-- Custom Domain -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Domain <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="text" name="domain" id="domain" x-model="formData.domain"
                            @input="clearError('domain')"
                            :class="hasError('domain') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="e.g. school.com">
                        <template x-if="hasError('domain')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('domain')"></p>
                        </template>
                    </div>

                    <!-- Logo -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Logo</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors relative"
                             :class="hasError('logo') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            <div class="space-y-1 text-center">
                                <!-- Preview Container -->
                                <div class="mb-4 flex justify-center">
                                    <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden relative">
                                        <img id="logo-preview" src="#" alt="Logo Preview" class="hidden w-full h-full object-contain">
                                        <i class="fas fa-image text-gray-400 text-4xl" id="logo-icon"></i>
                                        
                                        <button type="button" 
                                                id="logo-remove" 
                                                @click="removeLogo($event)"
                                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg z-10">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="logo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="logo" name="logo" type="file" class="sr-only" accept="image/*" @change="previewLogo($event)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                            </div>
                        </div>
                        <template x-if="hasError('logo')">
                            <p class="mt-1 text-xs text-red-600 font-medium text-center" x-text="getError('logo')"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Administrator Details Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-user-shield text-green-500 mr-2"></i> Administrator Details
                    </h3>
                </div>
                <div class="p-6 dark:bg-gray-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Admin Name -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Name <span class="text-red-500">*</span></label>
                        <input type="text" name="admin_name" id="admin_name" x-model="formData.admin_name"
                            @input="clearError('admin_name')"
                            :class="hasError('admin_name') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="e.g. John Doe">
                        <template x-if="hasError('admin_name')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('admin_name')"></p>
                        </template>
                    </div>

                    <!-- Admin Email -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Email <span class="text-red-500">*</span></label>
                        <input type="text" name="admin_email" id="admin_email" x-model="formData.admin_email"
                            @input="clearError('admin_email')"
                            :class="hasError('admin_email') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors outline-none"
                            placeholder="e.g. admin@school.com">
                        <template x-if="hasError('admin_email')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('admin_email')"></p>
                        </template>
                    </div>

                    <!-- Password -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="admin_password" id="admin_password" x-model="formData.admin_password"
                            @input="clearError('admin_password')"
                            :class="hasError('admin_password') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors outline-none">
                        <template x-if="hasError('admin_password')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('admin_password')"></p>
                        </template>
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" x-model="formData.admin_password_confirmation"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors outline-none">
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-address-card text-purple-500 mr-2"></i> Contact Information
                    </h3>
                </div>
                <div class="p-6 dark:bg-gray-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- School Email -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Email <span class="text-red-500">*</span></label>
                        <input type="text" name="email" id="email" x-model="formData.email"
                            @input="clearError('email')"
                            :class="hasError('email') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            placeholder="e.g. contact@school.com">
                        <template x-if="hasError('email')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('email')"></p>
                        </template>
                    </div>

                    <!-- Phone -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <x-phone-input name="phone" id="phone" x-model="formData.phone" 
                                @input="clearError('phone')"
                                :class="hasError('phone') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                                class="w-full pl-[70px] pr-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            />
                        </div>
                        <template x-if="hasError('phone')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('phone')"></p>
                        </template>
                    </div>

                    <!-- Address -->
                    <div class="col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                        <textarea name="address" id="address" rows="3" x-model="formData.address"
                            @input="clearError('address')"
                            :class="hasError('address') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            placeholder="Enter full address"></textarea>
                        <template x-if="hasError('address')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('address')"></p>
                        </template>
                    </div>

                    <!-- Country -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="country_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                        <select name="country_id" id="country_id" x-model="formData.country_id"
                            @change="clearError('country_id')"
                            :class="hasError('country_id') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors no-select2"
                            data-location-cascade="true"
                            data-country-select="true">
                            <option value="">Select Country</option>
                        </select>
                        <template x-if="hasError('country_id')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('country_id')"></p>
                        </template>
                    </div>

                    <!-- State -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="state_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State</label>
                        <select name="state_id" id="state_id" x-model="formData.state_id"
                            @change="clearError('state_id')"
                            :class="hasError('state_id') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors no-select2"
                            data-state-select="true"
                            :data-selected="formData.state_id">
                            <option value="">Select State</option>
                        </select>
                        <template x-if="hasError('state_id')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('state_id')"></p>
                        </template>
                    </div>

                    <!-- City -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                        <select name="city_id" id="city_id" x-model="formData.city_id"
                            @change="clearError('city_id')"
                            :class="hasError('city_id') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors no-select2"
                            data-city-select="true"
                            :data-selected="formData.city_id">
                            <option value="">Select City</option>
                        </select>
                        <template x-if="hasError('city_id')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('city_id')"></p>
                        </template>
                    </div>

                    <!-- Pincode -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="pincode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pincode</label>
                        <input type="text" name="pincode" id="pincode" x-model="formData.pincode"
                            @input="clearError('pincode')"
                            :class="hasError('pincode') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            placeholder="e.g. 123456">
                        <template x-if="hasError('pincode')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('pincode')"></p>
                        </template>
                    </div>

                    <!-- Website -->
                    <div class="col-span-2">
                        <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                        <input type="url" name="website" id="website" x-model="formData.website"
                            @input="clearError('website')"
                            :class="hasError('website') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            placeholder="https://example.com">
                        <template x-if="hasError('website')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('website')"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Subscription & Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-cog text-gray-500 mr-2"></i> Settings & Subscription
                    </h3>
                </div>
                <div class="p-6 dark:bg-gray-800 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status -->
                    <div class="col-span-3 md:col-span-1">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" x-model="formData.status"
                            @change="clearError('status')"
                            :class="hasError('status') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors no-select2">
                            @foreach(\App\Enums\SchoolStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <template x-if="hasError('status')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('status')"></p>
                        </template>
                    </div>

                    <!-- Start Date -->
                    <div class="col-span-3 md:col-span-1">
                        <label for="subscription_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Start Date</label>
                        <input type="date" name="subscription_start_date" id="subscription_start_date" x-model="formData.subscription_start_date"
                            @input="clearError('subscription_start_date')"
                            :class="hasError('subscription_start_date') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors">
                        <template x-if="hasError('subscription_start_date')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('subscription_start_date')"></p>
                        </template>
                    </div>

                    <!-- End Date -->
                    <div class="col-span-3 md:col-span-1">
                        <label for="subscription_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription End Date</label>
                        <input type="date" name="subscription_end_date" id="subscription_end_date" x-model="formData.subscription_end_date"
                            @input="clearError('subscription_end_date')"
                            :class="hasError('subscription_end_date') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-300 dark:border-gray-600'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors">
                        <template x-if="hasError('subscription_end_date')">
                            <p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('subscription_end_date')"></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.schools.index') }}" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    :disabled="loading"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg active:scale-95 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                <template x-if="!loading">
                    <span class="flex items-center"><i class="fas fa-save mr-2"></i> Create School</span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Processing...</span>
                </template>
            </button>
        </div>
    </form>
</div>

<script>
function adminSchoolCreate() {
    return {
        formData: {
            name: '',
            code: '',
            subdomain: '',
            domain: '',
            email: '',
            phone: '',
            address: '',
            city_id: '',
            state_id: '',
            country_id: '102', // Default to India
            pincode: '',
            website: '',
            status: '{{ \App\Enums\SchoolStatus::Active->value }}',
            subscription_start_date: '',
            subscription_end_date: '',
            admin_name: '',
            admin_email: '',
            admin_password: '',
            admin_password_confirmation: ''
        },
        errors: {},
        loading: false,

        init() {
            // Force sync for custom components on init
            this.$nextTick(() => {
                setTimeout(() => {
                    const phoneInput = document.getElementById('phone');
                    if (phoneInput) {
                        phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }, 500);
            });
        },

        submitForm() {
            this.loading = true;
            this.errors = {};

            let form = document.getElementById('schoolCreateForm');
            let formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok) {
                    showToast('success', data.message || 'School created successfully');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                        showToast('error', 'Please fix the errors below');
                    } else {
                        showToast('error', data.message || 'An error occurred while creating the school');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An unexpected error occurred');
            })
            .finally(() => {
                this.loading = false;
            });
        },

        clearError(field) {
            if (this.errors[field]) {
                const nextErrors = { ...this.errors };
                delete nextErrors[field];
                this.errors = nextErrors;
            }
        },

        hasError(field) {
            return !!(this.errors && this.errors[field] && this.errors[field].length > 0);
        },

        getError(field) {
            return this.hasError(field) ? this.errors[field][0] : '';
        },

        previewLogo(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = document.getElementById('logo-preview');
                    const icon = document.getElementById('logo-icon');
                    const removeBtn = document.getElementById('logo-remove');
                    
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (icon) icon.classList.add('hidden');
                    if (removeBtn) removeBtn.classList.remove('hidden');
                    
                    this.clearError('logo');
                };
                reader.readAsDataURL(file);
            }
        },

        removeLogo(event) {
            event.preventDefault();
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
    };
}
</script>
@endsection
