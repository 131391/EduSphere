@php
    use App\Enums\StaffPost;
    use App\Enums\Gender;
@endphp
@extends('layouts.receptionist')

@section('title', 'Staff Management - Receptionist')
@section('page-title', 'Staff Management')
@section('page-description', 'Manage school staff records')

@section('content')
<div class="space-y-6" x-data="staffManagement" x-init="init()">
    {{-- Success/Error Messages --}}


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
                        'state_id' => $row->state_id,
                        'city_id' => $row->city_id,
                        'zip_code' => $row->zip_code,
                        'address' => $row->address,
                        'aadhar_no' => $row->aadhar_no,
                        'aadhar_card' => $row->aadhar_card,
                        'staff_image' => $row->staff_image,
                        'joining_date' => $row->joining_date ? $row->joining_date->format('Y-m-d') : '',
                        'higher_qualification_id' => $row->higher_qualification_id,
                        'previous_school_company_name' => $row->previous_school_company_name,
                    ];
                    $data = json_encode($staffData);
                    return "window.dispatchEvent(new CustomEvent('open-edit-staff', { detail: $data }))";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-staff', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                },
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

            <div class="space-y-6">
                <div class="bg-teal-50 dark:bg-teal-900 p-6 rounded-xl border border-teal-100 dark:border-teal-800">
                    <h4 class="text-lg font-bold text-teal-800 dark:text-teal-200 mb-6 flex items-center">
                        <i class="fas fa-id-card-clip mr-2"></i>
                        Staff Information
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            {{-- Select Post --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-briefcase mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Select Post <span class="text-red-500">*</span>
                                </label>
                                <select name="post" 
                                        id="post"
                                        x-model="formData.post"
                                        class="modal-input-premium"
                                        :class="errors.post ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Post</option>
                                    @foreach(StaffPost::cases() as $post)
                                        <option value="{{ $post->value }}" {{ old('post') == $post->value ? 'selected' : '' }}>
                                            {{ $post->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('post')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Class --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-chalkboard-user mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Class
                                </label>
                                <select name="class_id" 
                                        id="class_id"
                                        x-model="formData.class_id"
                                        @change="loadSections()"
                                        :disabled="!canSelectClass"
                                        :class="!canSelectClass ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : ''"
                                        class="modal-input-premium"
                                        :class="errors.class_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Name --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-user mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       x-model="formData.name"
                                       value="{{ old('name') }}"
                                       placeholder="Enter Name"
                                       class="modal-input-premium"
                                       :class="errors.name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('name')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Mobile --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-phone mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mobile
                                </label>
                                <input type="tel" 
                                       name="mobile" 
                                       x-model="formData.mobile"
                                       value="{{ old('mobile') }}"
                                       placeholder="Enter Mobile"
                                       pattern="[0-9]{10,15}" 
                                       inputmode="numeric"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       class="modal-input-premium"
                                       :class="errors.mobile ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('mobile')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-venus-mars mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Gender
                                </label>
                                <select name="gender" 
                                        x-model="formData.gender"
                                        class="modal-input-premium"
                                        :class="errors.gender ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Choose Gender</option>
                                    @foreach(Gender::cases() as $gender)
                                        <option value="{{ $gender->value }}" {{ old('gender') == $gender->value ? 'selected' : '' }}>
                                            {{ $gender->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Total Experience --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-history mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Total Experience
                                </label>
                                <input type="number" 
                                       name="total_experience" 
                                       x-model="formData.total_experience"
                                       value="{{ old('total_experience') }}"
                                       placeholder="Enter Total Experience in Year"
                                       min="0"
                                       class="modal-input-premium"
                                       :class="errors.total_experience ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('total_experience')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Previous School Salary --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-sack-dollar mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Previous School Salary (Month)
                                </label>
                                <input type="number" 
                                       name="previous_school_salary" 
                                       x-model="formData.previous_school_salary"
                                       value="{{ old('previous_school_salary') }}"
                                       placeholder="Enter Previous School Salary (Month)"
                                       step="0.01"
                                       min="0"
                                       class="modal-input-premium"
                                       :class="errors.previous_school_salary ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('previous_school_salary')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Select Country --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-globe mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Select Country
                                </label>
                                <select name="country_id" 
                                        x-model="formData.country_id"
                                        class="modal-input-premium"
                                        :class="errors.country_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''"
                                        data-location-cascade="true"
                                        data-country-select="true">
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Select City --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-city mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Select City
                                </label>
                                <select name="city_id" 
                                        x-model="formData.city_id"
                                        class="modal-input-premium"
                                        :class="errors.city_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''"
                                        data-city-select="true">
                                    <option value="">Select City</option>
                                </select>
                                @error('city_id')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-home mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Address
                                </label>
                                <textarea name="address" 
                                          x-model="formData.address"
                                          rows="3"
                                          placeholder="Enter Address"
                                          class="modal-input-premium"
                                          :class="errors.address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Aadhar Card --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-id-card mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Aadhar Card
                                </label>
                                <input type="file" 
                                       name="aadhar_card" 
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       @change="previewAadharCard($event)"
                                       class="modal-input-premium file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                                       :class="errors.aadhar_card ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('aadhar_card')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                                <template x-if="formData.aadhar_card_preview">
                                    <div class="mt-2">
                                        <img :src="formData.aadhar_card_preview" alt="Aadhar Card Preview" class="max-w-xs h-auto rounded shadow-sm border border-gray-200">
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-6">
                            {{-- Joining Date --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-calendar-check mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Joining Date
                                </label>
                                <div class="relative">
                                    <input type="date" 
                                           name="joining_date" 
                                           x-model="formData.joining_date"
                                           value="{{ old('joining_date') }}"
                                           class="modal-input-premium"
                                           :class="errors.joining_date ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                </div>
                                @error('joining_date')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Section --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-layer-group mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Section
                                </label>
                                <select name="section_id" 
                                        id="section_id"
                                        x-model="formData.section_id"
                                        :disabled="!canSelectSection"
                                        :class="!canSelectSection ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : ''"
                                        class="modal-input-premium"
                                        :class="errors.section_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                                @error('section_id')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email ID --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-at mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Email ID
                                </label>
                                <input type="email" 
                                       name="email" 
                                       x-model="formData.email"
                                       value="{{ old('email') }}"
                                       placeholder="Enter Email Id"
                                       class="modal-input-premium"
                                       :class="errors.email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('email')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Aadhar No --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-barcode mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Aadhar No
                                </label>
                                <input type="text" 
                                       name="aadhar_no" 
                                       x-model="formData.aadhar_no"
                                       value="{{ old('aadhar_no') }}"
                                       placeholder="Enter Aadhar No"
                                       class="modal-input-premium"
                                       :class="errors.aadhar_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('aadhar_no')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Higher Qualification --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-graduation-cap mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Higher Qualification
                                </label>
                                <select name="higher_qualification_id" 
                                        x-model="formData.higher_qualification_id"
                                        class="modal-input-premium"
                                        :class="errors.higher_qualification_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Higher Qualification</option>
                                    @foreach($qualifications as $qualification)
                                        <option value="{{ $qualification->id }}" {{ old('higher_qualification_id') == $qualification->id ? 'selected' : '' }}>
                                            {{ $qualification->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('higher_qualification_id')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Previous School / Company Name --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-building-columns mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Previous School / Company Name
                                </label>
                                <input type="text" 
                                       name="previous_school_company_name" 
                                       x-model="formData.previous_school_company_name"
                                       value="{{ old('previous_school_company_name') }}"
                                       placeholder="Enter Previous School / Company Name"
                                       class="modal-input-premium"
                                       :class="errors.previous_school_company_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('previous_school_company_name')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Current Salary --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-money-bill-wave mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Current Salary (Month)
                                </label>
                                <input type="number" 
                                       name="current_salary" 
                                       x-model="formData.current_salary"
                                       value="{{ old('current_salary') }}"
                                       placeholder="Enter current Salary (Month)"
                                       step="0.01"
                                       min="0"
                                       class="modal-input-premium"
                                       :class="errors.current_salary ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('current_salary')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Select State --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-map mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Select State
                                </label>
                                <select name="state_id" 
                                        x-model="formData.state_id"
                                        class="modal-input-premium"
                                        :class="errors.state_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''"
                                        data-state-select="true">
                                    <option value="">Select State</option>
                                </select>
                                @error('state_id')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Zip code --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-location-dot mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Zip code
                                </label>
                                <input type="text" 
                                       name="zip_code" 
                                       x-model="formData.zip_code"
                                       value="{{ old('zip_code') }}"
                                       placeholder="zip code"
                                       class="modal-input-premium"
                                       :class="errors.zip_code ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('zip_code')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Staff Image --}}
                            <div class="group">
                                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                                    <i class="fas fa-image mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Staff Image
                                </label>
                                <input type="file" 
                                       name="staff_image" 
                                       accept=".jpg,.jpeg,.png"
                                       @change="previewStaffImage($event)"
                                       class="modal-input-premium file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                                       :class="errors.staff_image ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                                @error('staff_image')
                                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                @enderror
                                <template x-if="formData.staff_image_preview">
                                    <div class="mt-2">
                                        <img :src="formData.staff_image_preview" alt="Staff Image Preview" class="max-w-xs h-auto rounded shadow-sm border border-gray-200">
                                    </div>
                                </template>
                            </div>
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
        get canSelectClass() {
            return this.isTeacher;
        },
        get canSelectSection() {
            return this.isTeacher && !!this.formData.class_id;
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
            state_id: '',
            city_id: '',
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
            window.addEventListener('open-edit-staff', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-staff', (e) => this.confirmDelete(e.detail));

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
            
            // Watch for canSelectClass changes
            this.$watch('canSelectClass', (newValue) => {
                this.$nextTick(() => {
                    this.updateSelect2DisabledState();
                });
            });
            
            // Watch for canSelectSection changes
            this.$watch('canSelectSection', (newValue) => {
                this.$nextTick(() => {
                    this.updateSelect2DisabledState();
                });
            });
            
            // Watch for class_id changes to update section state
            this.$watch('formData.class_id', (newValue, oldValue) => {
                // Clear section when class changes
                if (newValue !== oldValue) {
                    this.formData.section_id = '';
                }
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
                    state_id: '{{ old('state_id') }}',
                    city_id: '{{ old('city_id') }}',
                    zip_code: '{{ old('zip_code') }}',
                    address: '{{ old('address') }}',
                    aadhar_no: '{{ old('aadhar_no') }}',
                    joining_date: '{{ old('joining_date') }}',
                    higher_qualification_id: '{{ old('higher_qualification_id') }}',
                    previous_school_company_name: '{{ old('previous_school_company_name') }}',
                };
                // Load sections if class is selected and is teacher
                if (this.formData.class_id && this.isTeacher) {
                    this.loadSections();
                }
                this.$nextTick(() => {
                    this.updateSelect2DisabledState();
                    this.$dispatch('open-modal', 'staff-modal');
                });
            @endif
            
            // Sync Select2 with Alpine.js formData
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    // All select elements in the form
                    $('select[name="post"], select[name="class_id"], select[name="section_id"], select[name="gender"], select[name="country_id"], select[name="state_id"], select[name="city_id"], select[name="higher_qualification_id"]').on('change', (e) => {
                        const field = e.target.getAttribute('name');
                        if (field && this.formData.hasOwnProperty(field)) {
                            this.formData[field] = e.target.value;
                            
                            // Specific logic for class_id to load sections
                            if (field === 'class_id') {
                                this.loadSections();
                            }
                        }
                    });
                }
            });
        },
        
        updateSelect2DisabledState() {
            // Update Select2 disabled state for class and section
            if (typeof $ !== 'undefined') {
                const $classSelect = $('#class_id');
                const $sectionSelect = $('#section_id');
                
                // Update class select disabled state
                if ($classSelect.length) {
                    const shouldDisableClass = !this.canSelectClass;
                    $classSelect.prop('disabled', shouldDisableClass);
                    if ($classSelect.hasClass('select2-hidden-accessible')) {
                        $classSelect.trigger('change.select2');
                    }
                }
                
                // Update section select disabled state
                if ($sectionSelect.length) {
                    const shouldDisableSection = !this.canSelectSection;
                    $sectionSelect.prop('disabled', shouldDisableSection);
                    if ($sectionSelect.hasClass('select2-hidden-accessible')) {
                        $sectionSelect.trigger('change.select2');
                    }
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
                post: staff.post ? String(staff.post) : '',
                class_id: staff.class_id ? String(staff.class_id) : '',
                section_id: staff.section_id ? String(staff.section_id) : '',
                name: staff.name || '',
                mobile: staff.mobile || '',
                email: staff.email || '',
                gender: staff.gender ? String(staff.gender) : '',
                total_experience: staff.total_experience || '',
                previous_school_salary: staff.previous_school_salary || '',
                current_salary: staff.current_salary || '',
                country_id: staff.country_id ? String(staff.country_id) : '',
                state_id: staff.state_id ? String(staff.state_id) : '',
                city_id: staff.city_id ? String(staff.city_id) : '',
                zip_code: staff.zip_code || '',
                address: staff.address || '',
                aadhar_no: staff.aadhar_no || '',
                joining_date: staff.joining_date || '',
                higher_qualification_id: staff.higher_qualification_id ? String(staff.higher_qualification_id) : '',
                previous_school_company_name: staff.previous_school_company_name || '',
            };
            
            // Load sections if class is selected and is teacher
            if (this.formData.class_id && this.isTeacher) {
                this.loadSections();
            }

            // Sync Select2 display
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        const fields = ['post', 'class_id', 'section_id', 'gender', 'country_id', 'state_id', 'city_id', 'higher_qualification_id'];
                        fields.forEach(field => {
                            if (this.formData[field]) {
                                $(`select[name="${field}"]`).val(this.formData[field]).trigger('change');
                            }
                        });
                        this.updateSelect2DisabledState();
                    }
                }, 150);
            });
            
            confirmDelete(staff) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Staff Record',
                    message: `Are you sure you want to delete the staff record for "${staff.name}"? This action cannot be undone.`,
                    callback: async () => {
                        this.submitting = true;
                        try {
                            const response = await fetch(`/receptionist/staff/${staff.id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) {
                                    window.Toast.fire({ icon: 'success', title: result.message });
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                throw new Error(result.message || 'Deletion failed');
                            }
                        } catch (error) {
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: error.message });
                            }
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }));
        },
            if (staff.staff_image) {
                this.formData.staff_image_preview = `/storage/${staff.staff_image}`;
            }
            
            this.$nextTick(() => {
                this.updateSelect2DisabledState();
                this.$dispatch('open-modal', 'staff-modal');
                
                // Update Select2 values after modal opens
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        // Update all Select2 dropdowns with the form data values
                        $('#post').val(this.formData.post).trigger('change');
                        $('#class_id').val(this.formData.class_id).trigger('change');
                        $('select[name="gender"]').val(this.formData.gender).trigger('change');
                        $('select[name="higher_qualification_id"]').val(this.formData.higher_qualification_id).trigger('change');
                        
                        // Handle Location Cascade manually for Edit Mode
                        const countrySelect = document.querySelector('select[name="country_id"]');
                        const stateSelect = document.querySelector('select[name="state_id"]');
                        const citySelect = document.querySelector('select[name="city_id"]');

                        if (window.locationCascade && this.formData.country_id) {
                            window.locationCascade.setValue(countrySelect, this.formData.country_id);
                            
                            if (this.formData.state_id) {
                                window.locationCascade.loadStates(stateSelect, this.formData.country_id, this.formData.state_id);
                                
                                if (this.formData.city_id) {
                                    window.locationCascade.loadCities(citySelect, this.formData.state_id, this.formData.city_id);
                                }
                            } else {
                                // Just trigger change to load states if no state selected
                                window.locationCascade.triggerChange(countrySelect);
                            }
                        } else {
                             $('select[name="country_id"]').val(this.formData.country_id).trigger('change');
                        }
                        
                        // Update section after a slight delay to ensure sections are loaded
                        if (this.formData.section_id) {
                            setTimeout(() => {
                                $('#section_id').val(this.formData.section_id).trigger('change');
                            }, 200);
                        }
                    }
                }, 100);
            });
        },
        
        loadSections() {
            const classId = this.formData.class_id;
            // Store current section_id before clearing
            const currentSectionId = this.formData.section_id;
            this.sections = [];
            this.formData.section_id = '';
            
            if (classId && this.isTeacher) {
                fetch(`/receptionist/staff/get-sections/${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        this.sections = data.sections || [];
                        // Restore section_id if it was set and exists in the loaded sections
                        if (currentSectionId && this.sections.find(s => s.id == currentSectionId)) {
                            this.formData.section_id = currentSectionId;
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
                state_id: '',
                city_id: '',
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
            
            // Reset Select2 dropdowns
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#post').val('').trigger('change');
                    $('#class_id').val('').trigger('change');
                    $('#section_id').val('').trigger('change');
                    $('select[name="gender"]').val('').trigger('change');
                    $('select[name="country_id"]').val('').trigger('change');
                    $('select[name="higher_qualification_id"]').val('').trigger('change');
                }
            });
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
    console.log('openEditModal called with staff:', staff);
    const element = document.querySelector('[x-data*="staffManagement"]');
    console.log('Found element:', element);
    
    if (!element) {
        console.error('Could not find staffManagement element');
        return;
    }
    
    const component = Alpine.$data(element);
    console.log('Found component:', component);
    
    if (component && typeof component.openEditModal === 'function') {
        component.openEditModal(staff);
    } else {
        console.error('Component or openEditModal function not found');
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

