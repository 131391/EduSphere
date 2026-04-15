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


    {{-- Staff Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Staff</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users-gear text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-emerald-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Teaching Staff</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['teaching'] }}</p>
                </div>
                <div class="bg-emerald-100 p-3 rounded-full">
                    <i class="fas fa-chalkboard-user text-emerald-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-amber-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Non-Teaching</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['non_teaching'] }}</p>
                </div>
                <div class="bg-amber-100 p-3 rounded-full">
                    <i class="fas fa-user-tie text-amber-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-indigo-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Recent Joiners</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['recent'] }}</p>
                </div>
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-user-plus text-indigo-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800">Staff List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Create Staff
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export
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
    <x-modal name="staff-modal" alpineTitle="editMode ? 'Modify Staff Information' : 'Register New Staff'" maxWidth="6xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

                <!-- Designated Post -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Designated Post <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select name="post" x-model="formData.post" @change="clearError('post')"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.post}">
                            <option value="">Select Designation</option>
                            @foreach(StaffPost::cases() as $post)
                                <option value="{{ $post->value }}">{{ $post->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <template x-if="errors.post">
                        <p class="modal-error-message" x-text="errors.post[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Full Staff Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name"
                                @input="clearError('name')" placeholder="Legal full name"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.name}">
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <!-- Mobile -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Primary Contact No <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="tel" name="mobile" x-model="formData.mobile"
                                @input="clearError('mobile')" placeholder="Active mobile number"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.mobile}">
                        </div>
                        <template x-if="errors.mobile">
                            <p class="modal-error-message" x-text="errors.mobile[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Email Address</label>
                        <div class="relative group">
                            <input type="email" name="email" x-model="formData.email"
                                @input="clearError('email')" placeholder="staff@school.com"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.email}">
                        </div>
                        <template x-if="errors.email">
                            <p class="modal-error-message" x-text="errors.email[0]"></p>
                        </template>
                    </div>

                    <!-- Gender -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Gender <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="gender" x-model="formData.gender" @change="clearError('gender')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.gender}">
                                <option value="">Select Gender</option>
                                @foreach(Gender::cases() as $gender)
                                    <option value="{{ $gender->value }}">{{ $gender->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.gender">
                            <p class="modal-error-message" x-text="errors.gender[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Joining Date -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Joining Date <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="date" name="joining_date" x-model="formData.joining_date"
                                @input="clearError('joining_date')"
                                class="modal-input-premium !pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.joining_date}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i class="fas fa-calendar-alt text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.joining_date">
                            <p class="modal-error-message" x-text="errors.joining_date[0]"></p>
                        </template>
                    </div>

                    <!-- Higher Qualification -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Higher Qualification</label>
                        <div class="relative group">
                            <select name="higher_qualification_id" x-model="formData.higher_qualification_id"
                                @change="clearError('higher_qualification_id')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.higher_qualification_id}">
                                <option value="">Select Qualification</option>
                                @foreach($qualifications as $qualification)
                                    <option value="{{ $qualification->id }}">{{ $qualification->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.higher_qualification_id">
                            <p class="modal-error-message" x-text="errors.higher_qualification_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Teacher Assignment (Conditional) -->
                <div x-show="isTeacher" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <!-- Class Assignment -->
                        <div class="space-y-2">
                            <label class="modal-label-premium">Class Assignment</label>
                            <div class="relative group">
                                <select name="class_id" id="class_id" x-model="formData.class_id"
                                    @change="loadSections(); clearError('class_id')"
                                    class="modal-input-premium" :disabled="!canSelectClass"
                                    :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <template x-if="errors.class_id">
                                <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                            </template>
                        </div>

                        <!-- Section Assignment -->
                        <div class="space-y-2">
                            <label class="modal-label-premium">Section Assignment</label>
                            <div class="relative group">
                                <select name="section_id" id="section_id" x-model="formData.section_id"
                                    @change="clearError('section_id')"
                                    class="modal-input-premium" :disabled="!canSelectSection"
                                    :class="{'border-red-500 ring-red-500/10': errors.section_id}">
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                            <template x-if="errors.section_id">
                                <p class="modal-error-message" x-text="errors.section_id[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Total Experience -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Total Experience (Years)</label>
                        <div class="relative group">
                            <input type="number" name="total_experience" x-model="formData.total_experience"
                                @input="clearError('total_experience')" min="0" placeholder="0"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.total_experience}">
                        </div>
                        <template x-if="errors.total_experience">
                            <p class="modal-error-message" x-text="errors.total_experience[0]"></p>
                        </template>
                    </div>

                    <!-- Previous Institution -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Previous Institution / Company</label>
                        <div class="relative group">
                            <input type="text" name="previous_school_company_name" x-model="formData.previous_school_company_name"
                                @input="clearError('previous_school_company_name')" placeholder="Name of last employer"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.previous_school_company_name}">
                        </div>
                        <template x-if="errors.previous_school_company_name">
                            <p class="modal-error-message" x-text="errors.previous_school_company_name[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Previous Salary -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Previous Salary (Monthly)</label>
                        <div class="relative group">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                            <input type="number" name="previous_school_salary" x-model="formData.previous_school_salary"
                                @input="clearError('previous_school_salary')" step="0.01" min="0" placeholder="0.00"
                                class="modal-input-premium !pr-8" :class="{'border-red-500 ring-red-500/10': errors.previous_school_salary}">
                        </div>
                        <template x-if="errors.previous_school_salary">
                            <p class="modal-error-message" x-text="errors.previous_school_salary[0]"></p>
                        </template>
                    </div>

                    <!-- Current Salary -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Current Salary (Monthly) <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                            <input type="number" name="current_salary" x-model="formData.current_salary"
                                @input="clearError('current_salary')" step="0.01" min="0" placeholder="0.00"
                                class="modal-input-premium !pr-8" :class="{'border-red-500 ring-red-500/10': errors.current_salary}">
                        </div>
                        <template x-if="errors.current_salary">
                            <p class="modal-error-message" x-text="errors.current_salary[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-6 mb-6">
                    <!-- Country -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Country</label>
                        <div class="relative group">
                            <select name="country_id" x-model="formData.country_id" @change="clearError('country_id')"
                                class="modal-input-premium" data-location-cascade="true" data-country-select="true"
                                :class="{'border-red-500 ring-red-500/10': errors.country_id}">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.country_id">
                            <p class="modal-error-message" x-text="errors.country_id[0]"></p>
                        </template>
                    </div>

                    <!-- State -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">State</label>
                        <div class="relative group">
                            <select name="state_id" x-model="formData.state_id" @change="clearError('state_id')"
                                class="modal-input-premium" data-state-select="true"
                                :class="{'border-red-500 ring-red-500/10': errors.state_id}">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <template x-if="errors.state_id">
                            <p class="modal-error-message" x-text="errors.state_id[0]"></p>
                        </template>
                    </div>

                    <!-- City -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">City</label>
                        <div class="relative group">
                            <select name="city_id" x-model="formData.city_id" @change="clearError('city_id')"
                                class="modal-input-premium" data-city-select="true"
                                :class="{'border-red-500 ring-red-500/10': errors.city_id}">
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <template x-if="errors.city_id">
                            <p class="modal-error-message" x-text="errors.city_id[0]"></p>
                        </template>
                    </div>

                    <!-- Zip Code -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Zip Code</label>
                        <div class="relative group">
                            <input type="text" name="zip_code" x-model="formData.zip_code"
                                @input="clearError('zip_code')" placeholder="PIN / ZIP"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.zip_code}">
                        </div>
                        <template x-if="errors.zip_code">
                            <p class="modal-error-message" x-text="errors.zip_code[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Address -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Permanent Address</label>
                    <div class="relative group">
                        <textarea name="address" x-model="formData.address"
                            @input="clearError('address')" rows="2" placeholder="House no, Street, Landmark..."
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.address}"></textarea>
                    </div>
                    <template x-if="errors.address">
                        <p class="modal-error-message" x-text="errors.address[0]"></p>
                    </template>
                </div>

                <!-- Aadhar Number -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Aadhar Card Number</label>
                    <div class="relative group">
                        <input type="text" name="aadhar_no" x-model="formData.aadhar_no"
                            @input="clearError('aadhar_no')" placeholder="12-digit Aadhar number"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.aadhar_no}">
                    </div>
                    <template x-if="errors.aadhar_no">
                        <p class="modal-error-message" x-text="errors.aadhar_no[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Aadhar Card Upload -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Aadhar Card Document</label>
                        <div class="relative group">
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
                                    <img :src="formData.aadhar_card_preview" x-show="formData.aadhar_card_preview" class="w-full h-full object-cover">
                                    <i x-show="!formData.aadhar_card_preview" class="fas fa-id-card text-xl text-slate-300"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors shadow-sm">
                                        <i class="fas fa-upload mr-2 text-slate-400"></i> Choose File
                                        <input type="file" x-ref="aadharCardInput" accept=".pdf,.jpg,.jpeg,.png"
                                            @change="previewAadharCard($event); clearError('aadhar_card')" class="hidden">
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1">PDF, JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <template x-if="errors.aadhar_card">
                            <p class="modal-error-message" x-text="errors.aadhar_card[0]"></p>
                        </template>
                    </div>

                    <!-- Staff Image Upload -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Staff Photograph</label>
                        <div class="relative group">
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
                                    <img :src="formData.staff_image_preview" x-show="formData.staff_image_preview" class="w-full h-full object-cover">
                                    <i x-show="!formData.staff_image_preview" class="fas fa-camera text-xl text-slate-300"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors shadow-sm">
                                        <i class="fas fa-upload mr-2 text-slate-400"></i> Choose File
                                        <input type="file" x-ref="staffImageInput" accept=".jpg,.jpeg,.png"
                                            @change="previewStaffImage($event); clearError('staff_image')" class="hidden">
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1">JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <template x-if="errors.staff_image">
                            <p class="modal-error-message" x-text="errors.staff_image[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Notice Card (same style as Academic Year toggle card) -->
                <div class="mb-8 flex items-center justify-between bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 leading-tight">Procedural Audit Compliance</span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80">Ensure all salary and qualification data is verified against physical documentation.</span>
                    </div>
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-check text-indigo-600 text-sm"></i>
                    </div>
                </div>

                <!-- Footer -->
                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'staff-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Record' : 'Register Staff'"></span>
                    </button>
                </x-slot>
        </form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('staffManagement', () => ({
        showModal: false,
        editMode: false,
        submitting: false,
        staffId: null,
        sections: [],
        errors: {},
        
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

        get isTeacher() {
            return String(this.formData.post) === '2';
        },
        get canSelectClass() {
            return this.isTeacher;
        },
        get canSelectSection() {
            return this.isTeacher && !!this.formData.class_id;
        },
        
        init() {
            window.addEventListener('open-edit-staff', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-staff', (e) => this.confirmDelete(e.detail));

            this.$watch('formData.post', (newValue) => {
                if (String(newValue) !== '2') {
                    this.formData.class_id = '';
                    this.formData.section_id = '';
                    this.sections = [];
                }
                this.$nextTick(() => this.updateSelect2DisabledState());
            });

            this.$watch('formData.class_id', (newValue, oldValue) => {
                if (newValue !== oldValue) {
                    this.formData.section_id = '';
                }
                this.$nextTick(() => this.updateSelect2DisabledState());
            });
            
            // Sync Select2 with Alpine.js
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    const selectors = 'select[name="post"], select[name="class_id"], select[name="section_id"], select[name="gender"], select[name="country_id"], select[name="state_id"], select[name="city_id"], select[name="higher_qualification_id"]';
                    $(selectors).on('change', (e) => {
                        const field = e.target.getAttribute('name');
                        if (field && this.formData.hasOwnProperty(field)) {
                            this.formData[field] = e.target.value;
                            this.clearError(field);
                            if (field === 'class_id') this.loadSections();
                        }
                    });
                }
            });
        },

        // ── AJAX Form Submission (Academic Year Pattern) ──
        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            const url = this.editMode
                ? `/receptionist/staff/${this.staffId}`
                : '{{ route("receptionist.staff.store") }}';

            try {
                // Use FormData because this form includes file uploads
                const fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                if (this.editMode) fd.append('_method', 'PUT');

                // Append all text fields
                const fields = [
                    'post', 'class_id', 'section_id', 'name', 'mobile', 'email',
                    'gender', 'total_experience', 'previous_school_salary', 'current_salary',
                    'country_id', 'state_id', 'city_id', 'zip_code', 'address',
                    'aadhar_no', 'joining_date', 'higher_qualification_id', 'previous_school_company_name'
                ];
                fields.forEach(f => {
                    if (this.formData[f] !== '' && this.formData[f] !== null && this.formData[f] !== undefined) {
                        fd.append(f, this.formData[f]);
                    }
                });

                // Append file inputs (if user selected new files)
                const aadharInput = this.$refs.aadharCardInput;
                if (aadharInput && aadharInput.files.length > 0) {
                    fd.append('aadhar_card', aadharInput.files[0]);
                }
                const staffImageInput = this.$refs.staffImageInput;
                if (staffImageInput && staffImageInput.files.length > 0) {
                    fd.append('staff_image', staffImageInput.files[0]);
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: fd
                });

                const result = await response.json();

                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message
                        });
                    }
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message
                    });
                }
            } finally {
                this.submitting = false;
            }
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },
        
        updateSelect2DisabledState() {
            if (typeof $ === 'undefined') return;
            const $classSelect = $('#class_id');
            const $sectionSelect = $('#section_id');
            
            if ($classSelect.length) {
                $classSelect.prop('disabled', !this.canSelectClass).trigger('change.select2');
            }
            if ($sectionSelect.length) {
                $sectionSelect.prop('disabled', !this.canSelectSection).trigger('change.select2');
            }
        },
        
        openAddModal() {
            this.editMode = false;
            this.staffId = null;
            this.errors = {};
            this.resetForm();
            this.$nextTick(() => {
                this.updateSelect2DisabledState();
                this.$dispatch('open-modal', 'staff-modal');
            });
        },
        
        openEditModal(staff) {
            this.editMode = true;
            this.staffId = staff.id;
            this.errors = {};
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
                aadhar_card_preview: staff.aadhar_card ? `/storage/${staff.aadhar_card}` : '',
                staff_image_preview: staff.staff_image ? `/storage/${staff.staff_image}` : '',
            };
            
            if (this.formData.class_id && this.isTeacher) this.loadSections();

            this.$nextTick(() => {
                this.$dispatch('open-modal', 'staff-modal');
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        const selectFields = ['post', 'class_id', 'gender', 'country_id', 'higher_qualification_id'];
                        selectFields.forEach(f => $(`select[name="${f}"]`).val(this.formData[f]).trigger('change'));
                        
                        // Handle Location Cascade
                        if (window.locationCascade && this.formData.country_id) {
                            window.locationCascade.loadStates(document.querySelector('select[name="state_id"]'), this.formData.country_id, this.formData.state_id);
                            setTimeout(() => {
                                if (this.formData.state_id) {
                                    window.locationCascade.loadCities(document.querySelector('select[name="city_id"]'), this.formData.state_id, this.formData.city_id);
                                }
                            }, 300);
                        }
                        
                        if (this.formData.section_id) {
                            setTimeout(() => $('#section_id').val(this.formData.section_id).trigger('change'), 400);
                        }
                    }
                }, 100);
            });
        },
        
        async confirmDelete(staff) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Staff Record',
                    message: `Are you sure you want to delete the profile for "${staff.name}"? This action is permanent.`,
                    callback: async () => {
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
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                throw new Error(result.message || 'Delete failed');
                            }
                        } catch (error) {
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: error.message });
                            }
                        }
                    }
                }
            }));
        },

        loadSections() {
            if (!this.formData.class_id || !this.isTeacher) return;
            fetch(`/receptionist/staff/get-sections/${this.formData.class_id}`)
                .then(res => res.json())
                .then(data => { this.sections = data.sections || []; })
                .catch(err => console.error('Error loading sections:', err));
        },
        
        previewAadharCard(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => this.formData.aadhar_card_preview = ev.target.result;
            reader.readAsDataURL(file);
        },
        
        previewStaffImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => this.formData.staff_image_preview = ev.target.result;
            reader.readAsDataURL(file);
        },
        
        resetForm() {
            this.formData = {
                post: '', class_id: '', section_id: '', name: '', mobile: '', email: '', gender: '',
                total_experience: '', previous_school_salary: '', current_salary: '', country_id: '',
                state_id: '', city_id: '', zip_code: '', address: '', aadhar_no: '',
                aadhar_card_preview: '', staff_image_preview: '', joining_date: '',
                higher_qualification_id: '', previous_school_company_name: '',
            };
            this.sections = [];
            this.errors = {};
            
            // Reset file inputs
            if (this.$refs.aadharCardInput) this.$refs.aadharCardInput.value = '';
            if (this.$refs.staffImageInput) this.$refs.staffImageInput.value = '';
            
            if (typeof $ !== 'undefined') {
                $('select').val('').trigger('change');
            }
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'staff-modal');
            this.resetForm();
        }
    }));
});
</script>
@endpush
@endsection

