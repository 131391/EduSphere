@extends('layouts.admin')

@section('title', 'Edit School')

@section('content')
<div class="w-full space-y-6" x-data="adminSchoolEdit()">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit School</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                <i class="fas fa-edit mr-2 text-amber-500"></i>
                Update school profile and configuration
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-xl border border-gray-200 dark:border-gray-600 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to List
            </a>
        </div>
    </div>

    <form @submit.prevent="submitForm" action="{{ route('admin.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="schoolEditForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Primary Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center mr-4">
                            <i class="fas fa-university text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Basic Information</h3>
                    </div>
                    <div class="p-8 dark:bg-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">School Name <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-school text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="name" id="name" x-model="formData.name" required
                                        @input="clearError('name')"
                                        :class="hasError('name') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                                        placeholder="Enter school name">
                                </div>
                                <template x-if="hasError('name')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('name')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="code" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">School Code <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-fingerprint text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="code" id="code" x-model="formData.code" required
                                        @input="clearError('code')"
                                        :class="hasError('code') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                                        placeholder="e.g. SCH001">
                                </div>
                                <template x-if="hasError('code')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('code')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="subdomain" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Subdomain <span class="text-red-500">*</span></label>
                                <div class="flex group">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="fas fa-link text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                        </div>
                                        <input type="text" name="subdomain" id="subdomain" x-model="formData.subdomain" required
                                            @input="clearError('subdomain')"
                                            :class="hasError('subdomain') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                            class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-l-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                                            placeholder="subdomain">
                                    </div>
                                    <span class="inline-flex items-center px-4 bg-gray-100 dark:bg-gray-600 border border-l-0 border-gray-200 dark:border-gray-600 rounded-r-2xl text-gray-500 dark:text-gray-300 text-sm font-semibold">
                                        .edusphere.local
                                    </span>
                                </div>
                                <template x-if="hasError('subdomain')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('subdomain')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="domain" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Custom Domain (Optional)</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-globe text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="domain" id="domain" x-model="formData.domain"
                                        @input="clearError('domain')"
                                        :class="hasError('domain') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                                        placeholder="e.g. school.com">
                                </div>
                                <template x-if="hasError('domain')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('domain')"></p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center mr-4">
                            <i class="fas fa-address-book text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Contact & Location</h3>
                    </div>
                    <div class="p-8 dark:bg-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Email Address <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="email" id="email" x-model="formData.email"
                                        @input="clearError('email')"
                                        :class="hasError('email') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                        placeholder="school@example.com">
                                </div>
                                <template x-if="hasError('email')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('email')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Phone Number <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <x-phone-input name="phone" id="phone" x-model="formData.phone" 
                                        @input="clearError('phone')"
                                        :class="hasError('phone') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-[70px] pr-4 py-3 bg-white border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                    />
                                </div>
                                <template x-if="hasError('phone')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('phone')"></p>
                                </template>
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label for="address" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Physical Address</label>
                                <div class="relative group">
                                    <div class="absolute top-3 left-4 pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <textarea name="address" id="address" rows="3" x-model="formData.address"
                                        @input="clearError('address')"
                                        :class="hasError('address') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                        placeholder="Enter full address"></textarea>
                                </div>
                                <template x-if="hasError('address')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('address')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="country_id" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Country</label>
                                <div class="relative">
                                    <select name="country_id" id="country_id" x-model="formData.country_id"
                                        @change="clearError('country_id'); $nextTick(() => { formData.state_id = ''; formData.city_id = ''; })"
                                        :class="hasError('country_id') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none appearance-none cursor-pointer no-select2"
                                        data-location-cascade="true"
                                        data-country-select="true"
                                        :data-selected="formData.country_id">
                                        <option value="">Select Country</option>
                                    </select>

                                </div>
                                <template x-if="hasError('country_id')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('country_id')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="state_id" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">State</label>
                                <select name="state_id" id="state_id" x-model="formData.state_id"
                                    @change="clearError('state_id'); $nextTick(() => { formData.city_id = ''; })"
                                    :class="hasError('state_id') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none no-select2"
                                    data-state-select="true"
                                    :data-selected="formData.state_id">
                                    <option value="">Select State</option>
                                </select>
                                <template x-if="hasError('state_id')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('state_id')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="city_id" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">City</label>
                                <select name="city_id" id="city_id" x-model="formData.city_id"
                                    @change="clearError('city_id')"
                                    :class="hasError('city_id') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none no-select2"
                                    data-city-select="true"
                                    :data-selected="formData.city_id">
                                    <option value="">Select City</option>
                                </select>
                                <template x-if="hasError('city_id')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('city_id')"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label for="pincode" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Pincode</label>
                                <input type="text" name="pincode" id="pincode" x-model="formData.pincode"
                                    @input="clearError('pincode')"
                                    :class="hasError('pincode') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                    placeholder="Enter pincode">
                                <template x-if="hasError('pincode')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('pincode')"></p>
                                </template>
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label for="website" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Website URL</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-link text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                                    </div>
                                    <input type="url" name="website" id="website" x-model="formData.website"
                                        @input="clearError('website')"
                                        :class="hasError('website') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none"
                                        placeholder="https://www.school.com">
                                </div>
                                <template x-if="hasError('website')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('website')"></p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Media -->
            <div class="space-y-6">
                <!-- Status & Subscription Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mr-4">
                            <i class="fas fa-toggle-on text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Status & Plan</h3>
                    </div>
                    <div class="p-6 dark:bg-gray-800 space-y-6">
                        <div class="space-y-2">
                            <label for="status" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Account Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" x-model="formData.status" required
                                @change="clearError('status')"
                                :class="hasError('status') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none appearance-none cursor-pointer no-select2">
                                @foreach(\App\Enums\SchoolStatus::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <template x-if="hasError('status')">
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('status')"></p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label for="subscription_start_date" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Subscription Start</label>
                            <input type="date" name="subscription_start_date" id="subscription_start_date" x-model="formData.subscription_start_date"
                                @input="clearError('subscription_start_date')"
                                :class="hasError('subscription_start_date') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none">
                            <template x-if="hasError('subscription_start_date')">
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('subscription_start_date')"></p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label for="subscription_end_date" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Subscription End</label>
                            <input type="date" name="subscription_end_date" id="subscription_end_date" x-model="formData.subscription_end_date"
                                @input="clearError('subscription_end_date')"
                                :class="hasError('subscription_end_date') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none">
                             <template x-if="hasError('subscription_end_date')">
                                <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('subscription_end_date')"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Administrator Account Settings Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center mr-4">
                            <i class="fas fa-user-shield text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Administrator Account</h3>
                    </div>
                    <div class="p-6 dark:bg-gray-800 space-y-4">
                        @if($admin)
                            <input type="hidden" name="admin_id" value="{{ $admin->id }}">
                            <div class="space-y-2">
                                <label for="admin_name" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Admin Name</label>
                                <input type="text" name="admin_name" id="admin_name" x-model="formData.admin_name"
                                    @input="clearError('admin_name')"
                                    :class="hasError('admin_name') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none">
                                <template x-if="hasError('admin_name')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('admin_name')"></p>
                                </template>
                            </div>
                            <div class="space-y-2">
                                <label for="admin_email" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Admin Email</label>
                                <input type="email" name="admin_email" id="admin_email" x-model="formData.admin_email"
                                    @input="clearError('admin_email')"
                                    :class="hasError('admin_email') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none">
                                <template x-if="hasError('admin_email')">
                                    <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('admin_email')"></p>
                                </template>
                            </div>
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Change Password</p>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="space-y-2">
                                        <label for="admin_password" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">New Password</label>
                                        <input type="password" name="admin_password" id="admin_password" placeholder="Leave blank to keep current"
                                            x-model="formData.admin_password"
                                            @input="clearError('admin_password')"
                                            :class="hasError('admin_password') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200'"
                                            class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none">
                                        <template x-if="hasError('admin_password')">
                                            <p class="text-xs text-red-600 ml-1" x-text="getError('admin_password')"></p>
                                        </template>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="admin_password_confirmation" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Confirm Password</label>
                                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" placeholder="Confirm new password"
                                            x-model="formData.admin_password_confirmation"
                                            @input="clearError('admin_password')"
                                            class="w-full px-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all outline-none">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Logo Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center mr-4">
                            <i class="fas fa-image text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">School Branding</h3>
                    </div>
                    <div class="p-6 dark:bg-gray-800 space-y-6">
                        <div class="space-y-4">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">School Logo</label>
                            
                            <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-3xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer relative group"
                                :class="hasError('logo') ? 'border-red-500' : 'border-gray-200'">
                                @if($school->logo)
                                <img id="logo-preview" src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-32 h-32 rounded-2xl object-cover shadow-md mb-4">
                                @else
                                <div id="logo-placeholder" class="w-32 h-32 rounded-2xl bg-white flex items-center justify-center shadow-sm mb-4">
                                    <i class="fas fa-school text-gray-300 text-4xl"></i>
                                </div>
                                <img id="logo-preview" src="#" class="hidden w-32 h-32 rounded-2xl object-cover shadow-md mb-4">
                                @endif
                                
                                <div class="text-center">
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200">Change Logo</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">JPG, PNG or SVG (Max 2MB)</p>
                                </div>
                                <input type="file" name="logo" id="logo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" @change="previewLogo($event)">
                            </div>
                            
                            <template x-if="hasError('logo')">
                                <p class="mt-1 text-xs text-red-600 font-medium text-center" x-text="getError('logo')"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Form Actions Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <button type="submit" 
                        :disabled="loading"
                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-500/20 transition-all duration-300 transform hover:-translate-y-1 active:scale-95 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!loading">
                            <span class="flex items-center">
                                <i class="fas fa-save mr-3"></i>Save Changes
                            </span>
                        </template>
                        <template x-if="loading">
                            <span class="flex items-center">
                                <i class="fas fa-spinner fa-spin mr-3"></i>Updating...
                            </span>
                        </template>
                    </button>
                    <a href="{{ route('admin.schools.index') }}" class="w-full mt-3 py-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-2xl transition-all flex items-center justify-center">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function adminSchoolEdit() {
    return {
        formData: {
            name: @json($school->name),
            code: @json($school->code),
            subdomain: @json($school->subdomain),
            domain: @json($school->domain),
            email: @json($school->email),
            phone: @json($school->phone),
            address: @json($school->address),
            city_id: @json($school->city_id),
            state_id: @json($school->state_id),
            country_id: @json($school->country_id),
            pincode: @json($school->pincode),
            website: @json($school->website),
            status: @json($school->status instanceof \UnitEnum ? $school->status->value : $school->status),
            subscription_start_date: @json($school->subscription_start_date ? $school->subscription_start_date->format('Y-m-d') : ''),
            subscription_end_date: @json($school->subscription_end_date ? $school->subscription_end_date->format('Y-m-d') : ''),
            admin_name: @json($admin->name ?? ''),
            admin_email: @json($admin->email ?? ''),
            admin_password: '',
            admin_password_confirmation: ''
        },
        errors: {},
        loading: false,

        init() {
            // Synchronize cascading selects after external script loads options
            this.$nextTick(() => {
                // We use a small delay to ensure the location-cascade.js and select2 are fully initialized
                setTimeout(() => {
                    // Force update Alpine state from DOM for location-cascade fields if they changed
                    if (document.getElementById('country_id')) this.formData.country_id = document.getElementById('country_id').value;
                    if (document.getElementById('state_id')) this.formData.state_id = document.getElementById('state_id').value;
                    if (document.getElementById('city_id')) this.formData.city_id = document.getElementById('city_id').value;
                    
                    // Trigger native input event for custom components like phone-input
                    const phoneInput = document.getElementById('phone');
                    if (phoneInput) {
                        phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }, 800);
            });
        },

        submitForm() {
            this.loading = true;
            this.errors = {};

            let form = document.getElementById('schoolEditForm');
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
                    showToast('success', data.message || 'School updated successfully');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                        showToast('error', 'Please fix the errors below');
                    } else {
                        showToast('error', data.message || 'An error occurred while updating the school');
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
                    const placeholder = document.getElementById('logo-placeholder');
                    
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                    
                    this.clearError('logo');
                };
                reader.readAsDataURL(file);
            }
        }
    }
}
</script>
@endsection
