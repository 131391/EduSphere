@extends('layouts.receptionist')

@section('title', 'Staff Management - Receptionist')
@section('page-title', 'Staff Management')
@section('page-description', 'Manage school staff records')

@section('content')
<div class="space-y-6" x-data="staffManagement" x-init="init()">
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800">Staff List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Staff
                </button>
            </div>
        </div>
    </div>

    {{-- Staff Table --}}
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'sortable' => false,
                'render' => function($row, $index, $data) {
                    return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'name',
                'label' => 'NAME',
                'sortable' => true,
            ],
            [
                'key' => 'post',
                'label' => 'POST',
                'sortable' => true,
                'render' => function($row) {
                    return $row->post->label();
                }
            ],
            [
                'key' => 'class',
                'label' => 'CLASS',
                'sortable' => false,
                'render' => function($row) {
                    return $row->class ? $row->class->name : 'N/A';
                }
            ],
            [
                'key' => 'section',
                'label' => 'SECTION',
                'sortable' => false,
                'render' => function($row) {
                    return $row->section ? $row->section->name : 'N/A';
                }
            ],
            [
                'key' => 'mobile',
                'label' => 'MOBILE',
                'sortable' => true,
            ],
            [
                'key' => 'email',
                'label' => 'EMAIL',
                'sortable' => true,
            ],
            [
                'key' => 'joining_date',
                'label' => 'JOINING DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->joining_date ? $row->joining_date->format('d/m/Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-staff'))))";
                },
                'data-staff' => function($row) {
                    $staffData = [
                        'id' => $row->id,
                        'post' => $row->post->value,
                        'class_id' => $row->class_id,
                        'section_id' => $row->section_id,
                        'name' => $row->name,
                        'mobile' => $row->mobile,
                        'email' => $row->email,
                        'gender' => $row->gender ? $row->gender->value : null,
                        'total_experience' => $row->total_experience,
                        'previous_school_salary' => $row->previous_school_salary,
                        'current_salary' => $row->current_salary,
                        'country_id' => $row->country_id,
                        'state' => $row->state,
                        'city' => $row->city,
                        'zip_code' => $row->zip_code,
                        'address' => $row->address,
                        'aadhar_no' => $row->aadhar_no,
                        'aadhar_card' => $row->aadhar_card,
                        'staff_image' => $row->staff_image,
                        'joining_date' => $row->joining_date ? $row->joining_date->format('Y-m-d') : '',
                        'higher_qualification_id' => $row->higher_qualification_id,
                        'previous_school_company_name' => $row->previous_school_company_name,
                    ];
                    return base64_encode(json_encode($staffData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => function($row) {
                    return route('receptionist.staff.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this staff?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$staff"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No staff found"
        empty-icon="fas fa-users"
    >
        Staff List
    </x-data-table>

    {{-- Add/Edit Staff Modal --}}
    <x-modal name="staff-modal" alpineTitle="editMode ? 'Edit Staff' : 'Create Staff'" maxWidth="6xl">
        <form :action="editMode ? `/receptionist/staff/${staffId}` : '{{ route('receptionist.staff.store') }}'" 
              method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>

            <div class="bg-teal-50 dark:bg-teal-900 p-4 rounded-lg mb-6">
                <h4 class="font-bold text-gray-800 dark:text-white mb-4">Staff Information</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    {{-- Left Column --}}
                    <div class="space-y-4">
                        {{-- Select Post --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Select Post <span class="text-red-500">*</span>
                            </label>
                            <select name="post" 
                                    id="post"
                                    x-model="formData.post"
                                    class="w-full px-4 py-2 border {{ $errors->has('post') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Post</option>
                                @foreach(\App\Enums\StaffPost::cases() as $post)
                                    <option value="{{ $post->value }}" {{ old('post') == $post->value ? 'selected' : '' }}>
                                        {{ $post->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('post')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Class --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Class
                            </label>
                            <select name="class_id" 
                                    id="class_id"
                                    x-model="formData.class_id"
                                    @change="loadSections()"
                                    :disabled="!isTeacher"
                                    :class="!isTeacher ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : ''"
                                    class="w-full px-4 py-2 border {{ $errors->has('class_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="formData.name"
                                   value="{{ old('name') }}"
                                   placeholder="Enter Name"
                                   class="w-full px-4 py-2 border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Mobile --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Mobile
                            </label>
                            <input type="text" 
                                   name="mobile" 
                                   x-model="formData.mobile"
                                   value="{{ old('mobile') }}"
                                   placeholder="Enter Mobile"
                                   class="w-full px-4 py-2 border {{ $errors->has('mobile') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('mobile')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Gender --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Gender
                            </label>
                            <select name="gender" 
                                    x-model="formData.gender"
                                    class="w-full px-4 py-2 border {{ $errors->has('gender') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Choose Gender</option>
                                @foreach(\App\Enums\Gender::cases() as $gender)
                                    <option value="{{ $gender->value }}" {{ old('gender') == $gender->value ? 'selected' : '' }}>
                                        {{ $gender->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Total Experience --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Total Experience
                            </label>
                            <input type="number" 
                                   name="total_experience" 
                                   x-model="formData.total_experience"
                                   value="{{ old('total_experience') }}"
                                   placeholder="Enter Total Experience in Year"
                                   min="0"
                                   class="w-full px-4 py-2 border {{ $errors->has('total_experience') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('total_experience')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Previous School Salary --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Previous School Salary (Month)
                            </label>
                            <input type="number" 
                                   name="previous_school_salary" 
                                   x-model="formData.previous_school_salary"
                                   value="{{ old('previous_school_salary') }}"
                                   placeholder="Enter Previous School Salary (Month)"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-4 py-2 border {{ $errors->has('previous_school_salary') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('previous_school_salary')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Select Country --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Select Country
                            </label>
                            <select name="country_id" 
                                    x-model="formData.country_id"
                                    class="w-full px-4 py-2 border {{ $errors->has('country_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Country</option>
                                @foreach(config('countries') as $id => $name)
                                    <option value="{{ $id }}" {{ old('country_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Select City --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Select City
                            </label>
                            <input type="text" 
                                   name="city" 
                                   x-model="formData.city"
                                   value="{{ old('city') }}"
                                   placeholder="Enter City"
                                   class="w-full px-4 py-2 border {{ $errors->has('city') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('city')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Address
                            </label>
                            <textarea name="address" 
                                      x-model="formData.address"
                                      rows="3"
                                      placeholder="Enter Address"
                                      class="w-full px-4 py-2 border {{ $errors->has('address') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Aadhar Card --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Aadhar Card
                            </label>
                            <input type="file" 
                                   name="aadhar_card" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   @change="previewAadharCard($event)"
                                   class="w-full px-4 py-2 border {{ $errors->has('aadhar_card') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('aadhar_card')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <template x-if="formData.aadhar_card_preview">
                                <div class="mt-2">
                                    <img :src="formData.aadhar_card_preview" alt="Aadhar Card Preview" class="max-w-xs h-auto rounded">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-4">
                        {{-- Joining Date --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Joining Date
                            </label>
                            <div class="relative">
                                <input type="date" 
                                       name="joining_date" 
                                       x-model="formData.joining_date"
                                       value="{{ old('joining_date') }}"
                                       class="w-full px-4 py-2 border {{ $errors->has('joining_date') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                            </div>
                            @error('joining_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Section --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Section
                            </label>
                            <select name="section_id" 
                                    id="section_id"
                                    x-model="formData.section_id"
                                    :disabled="!isTeacher"
                                    :class="!isTeacher ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : ''"
                                    class="w-full px-4 py-2 border {{ $errors->has('section_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Section</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                            @error('section_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email ID --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Email ID
                            </label>
                            <input type="email" 
                                   name="email" 
                                   x-model="formData.email"
                                   value="{{ old('email') }}"
                                   placeholder="Enter Email Id"
                                   class="w-full px-4 py-2 border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Aadhar No --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Aadhar No
                            </label>
                            <input type="text" 
                                   name="aadhar_no" 
                                   x-model="formData.aadhar_no"
                                   value="{{ old('aadhar_no') }}"
                                   placeholder="Enter Aadhar No"
                                   class="w-full px-4 py-2 border {{ $errors->has('aadhar_no') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('aadhar_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Higher Qualification --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Higher Qualification
                            </label>
                            <select name="higher_qualification_id" 
                                    x-model="formData.higher_qualification_id"
                                    class="w-full px-4 py-2 border {{ $errors->has('higher_qualification_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Higher Qualification</option>
                                @foreach($qualifications as $qualification)
                                    <option value="{{ $qualification->id }}" {{ old('higher_qualification_id') == $qualification->id ? 'selected' : '' }}>
                                        {{ $qualification->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('higher_qualification_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Previous School / Company Name --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Previous School / Company Name
                            </label>
                            <input type="text" 
                                   name="previous_school_company_name" 
                                   x-model="formData.previous_school_company_name"
                                   value="{{ old('previous_school_company_name') }}"
                                   placeholder="Enter Previous School / Company Name"
                                   class="w-full px-4 py-2 border {{ $errors->has('previous_school_company_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('previous_school_company_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Salary --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Current Salary (Month)
                            </label>
                            <input type="number" 
                                   name="current_salary" 
                                   x-model="formData.current_salary"
                                   value="{{ old('current_salary') }}"
                                   placeholder="Enter current Salary (Month)"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-4 py-2 border {{ $errors->has('current_salary') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('current_salary')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Select State --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Select State
                            </label>
                            <input type="text" 
                                   name="state" 
                                   x-model="formData.state"
                                   value="{{ old('state') }}"
                                   placeholder="Enter State"
                                   class="w-full px-4 py-2 border {{ $errors->has('state') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('state')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Zip code --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Zip code
                            </label>
                            <input type="text" 
                                   name="zip_code" 
                                   x-model="formData.zip_code"
                                   value="{{ old('zip_code') }}"
                                   placeholder="zip code"
                                   class="w-full px-4 py-2 border {{ $errors->has('zip_code') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('zip_code')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Staff Image --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Staff Image
                            </label>
                            <input type="file" 
                                   name="staff_image" 
                                   accept=".jpg,.jpeg,.png"
                                   @change="previewStaffImage($event)"
                                   class="w-full px-4 py-2 border {{ $errors->has('staff_image') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            @error('staff_image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <template x-if="formData.staff_image_preview">
                                <div class="mt-2">
                                    <img :src="formData.staff_image_preview" alt="Staff Image Preview" class="max-w-xs h-auto rounded">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-center gap-4">
                <button type="button" @click="closeModal()"
                        class="px-8 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold">
                    Cancel
                </button>
                <button type="submit"
                        class="px-8 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md">
                    <span x-text="editMode ? 'Update' : 'Submit'"></span>
                </button>
            </div>
        </form>
    </x-modal>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('staffManagement', () => ({
        showModal: false,
        editMode: false,
        staffId: null,
        sections: [],
        get isTeacher() {
            return this.formData.post == '2'; // Teacher enum value
        },
        formData: {
            post: '',
            class_id: '',
            section_id: '',
            name: '',
            mobile: '',
            email: '',
            gender: '',
            total_experience: '',
            previous_school_salary: '',
            current_salary: '',
            country_id: '',
            state: '',
            city: '',
            zip_code: '',
            address: '',
            aadhar_no: '',
            aadhar_card_preview: '',
            staff_image_preview: '',
            joining_date: '',
            higher_qualification_id: '',
            previous_school_company_name: '',
        },
        
        init() {
            // Watch for post changes to enable/disable class and section
            this.$watch('formData.post', (newValue, oldValue) => {
                if (newValue != '2') { // Not Teacher
                    // Clear class and section when not teacher
                    this.formData.class_id = '';
                    this.formData.section_id = '';
                    this.sections = [];
                }
                // Update Select2 disabled state
                this.$nextTick(() => {
                    this.updateSelect2DisabledState();
                });
            });
            
            // Watch for isTeacher computed property changes
            this.$watch('isTeacher', (newValue) => {
                this.$nextTick(() => {
                    this.updateSelect2DisabledState();
                });
            });
            
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.staffId = '{{ old('staff_id') }}';
                this.formData = {
                    post: '{{ old('post') }}',
                    class_id: '{{ old('class_id') }}',
                    section_id: '{{ old('section_id') }}',
                    name: '{{ old('name') }}',
                    mobile: '{{ old('mobile') }}',
                    email: '{{ old('email') }}',
                    gender: '{{ old('gender') }}',
                    total_experience: '{{ old('total_experience') }}',
                    previous_school_salary: '{{ old('previous_school_salary') }}',
                    current_salary: '{{ old('current_salary') }}',
                    country_id: '{{ old('country_id') }}',
                    state: '{{ old('state') }}',
                    city: '{{ old('city') }}',
                    zip_code: '{{ old('zip_code') }}',
                    address: '{{ old('address') }}',
                    aadhar_no: '{{ old('aadhar_no') }}',
                    joining_date: '{{ old('joining_date') }}',
                    higher_qualification_id: '{{ old('higher_qualification_id') }}',
                    previous_school_company_name: '{{ old('previous_school_company_name') }}',
                };
                if (this.formData.class_id && this.isTeacher) {
                    this.loadSections();
                }
                this.$nextTick(() => {
                    this.updateSelect2DisabledState();
                    this.$dispatch('open-modal', 'staff-modal');
                });
            @endif
        },
        
        updateSelect2DisabledState() {
            // Update Select2 disabled state for class and section
            if (typeof $ !== 'undefined') {
                const $classSelect = $('#class_id');
                const $sectionSelect = $('#section_id');
                
                if ($classSelect.hasClass('select2-hidden-accessible')) {
                    $classSelect.prop('disabled', !this.isTeacher);
                    $classSelect.trigger('change.select2');
                }
                
                if ($sectionSelect.hasClass('select2-hidden-accessible')) {
                    $sectionSelect.prop('disabled', !this.isTeacher);
                    $sectionSelect.trigger('change.select2');
                }
            }
        },
        
        openAddModal() {
            this.editMode = false;
            this.staffId = null;
            this.resetForm();
            this.$nextTick(() => {
                this.updateSelect2DisabledState();
                this.$dispatch('open-modal', 'staff-modal');
            });
        },
        
        openEditModal(staff) {
            this.editMode = true;
            this.staffId = staff.id;
            this.formData = {
                post: staff.post || '',
                class_id: staff.class_id || '',
                section_id: staff.section_id || '',
                name: staff.name || '',
                mobile: staff.mobile || '',
                email: staff.email || '',
                gender: staff.gender || '',
                total_experience: staff.total_experience || '',
                previous_school_salary: staff.previous_school_salary || '',
                current_salary: staff.current_salary || '',
                country_id: staff.country_id || '',
                state: staff.state || '',
                city: staff.city || '',
                zip_code: staff.zip_code || '',
                address: staff.address || '',
                aadhar_no: staff.aadhar_no || '',
                joining_date: staff.joining_date || '',
                higher_qualification_id: staff.higher_qualification_id || '',
                previous_school_company_name: staff.previous_school_company_name || '',
            };
            
            // Load sections if class is selected and is teacher
            if (this.formData.class_id && this.isTeacher) {
                this.loadSections();
            }
            
            // Set preview images if they exist
            if (staff.aadhar_card) {
                this.formData.aadhar_card_preview = `/storage/${staff.aadhar_card}`;
            }
            if (staff.staff_image) {
                this.formData.staff_image_preview = `/storage/${staff.staff_image}`;
            }
            
            this.$nextTick(() => {
                this.updateSelect2DisabledState();
                this.$dispatch('open-modal', 'staff-modal');
            });
        },
        
        loadSections() {
            const classId = this.formData.class_id;
            this.sections = [];
            this.formData.section_id = '';
            
            if (classId && this.isTeacher) {
                fetch(`/receptionist/staff/get-sections/${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        this.sections = data.sections || [];
                        // Restore section_id if it was set
                        if (this.formData.section_id && this.sections.find(s => s.id == this.formData.section_id)) {
                            // Section is already set, keep it
                        }
                    })
                    .catch(error => {
                        console.error('Error loading sections:', error);
                    });
            }
        },
        
        previewAadharCard(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.formData.aadhar_card_preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        
        previewStaffImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.formData.staff_image_preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        
        resetForm() {
            this.formData = {
                post: '',
                class_id: '',
                section_id: '',
                name: '',
                mobile: '',
                email: '',
                gender: '',
                total_experience: '',
                previous_school_salary: '',
                current_salary: '',
                country_id: '',
                state: '',
                city: '',
                zip_code: '',
                address: '',
                aadhar_no: '',
                aadhar_card_preview: '',
                staff_image_preview: '',
                joining_date: '',
                higher_qualification_id: '',
                previous_school_company_name: '',
            };
            this.sections = [];
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'staff-modal');
            this.resetForm();
            this.editMode = false;
            this.staffId = null;
        }
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(staff) {
    const component = Alpine.$data(document.querySelector('[x-data*="staffManagement"]'));
    if (component) {
        component.openEditModal(staff);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    const clearFieldError = function(field) {
        field.classList.remove('border-red-500');
        let errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('text-red-500')) {
            errorElement.remove();
        }
        const parentDiv = field.closest('div');
        if (parentDiv) {
            const errorInParent = parentDiv.querySelector('p.text-red-500');
            if (errorInParent) {
                errorInParent.remove();
            }
        }
    };
    
    const modal = document.querySelector('[x-data*="staffManagement"]');
    if (modal) {
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                clearFieldError(e.target);
            }
        });
        
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                clearFieldError(e.target);
            }
        });
    }
});
</script>
@endpush
@endsection

