@php use App\Enums\AdmissionStatus; @endphp
@extends('layouts.receptionist')

@section('title', 'Edit Student Registration')

@section('content')
<div class="p-6" x-data="registrationManagement()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Student Registration</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                Updating registration for
                <span class="text-teal-600 font-bold">{{ $studentRegistration->full_name }}</span>
                &middot;
                <span class="text-gray-400 font-mono text-xs">{{ $studentRegistration->registration_no }}</span>
            </p>
        </div>
        <a href="{{ route('receptionist.student-registrations.index') }}"
           class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div x-cloak>

        @include('receptionist.student-registrations.partials.stepper_header')

        <form id="registrationForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Enquiry carry-over hidden fields: always in DOM --}}
            <input type="hidden" name="enquiry_father_photo"      x-model="formData.enquiry_father_photo">
            <input type="hidden" name="enquiry_mother_photo"      x-model="formData.enquiry_mother_photo">
            <input type="hidden" name="enquiry_student_photo"     x-model="formData.enquiry_student_photo">
            <input type="hidden" name="enquiry_father_signature"  x-model="formData.enquiry_father_signature">
            <input type="hidden" name="enquiry_mother_signature"  x-model="formData.enquiry_mother_signature">
            <input type="hidden" name="enquiry_student_signature" x-model="formData.enquiry_student_signature">

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-6 md:p-8 min-h-[480px]">

                    <template x-if="currentStep === 1">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step1_reg')
                        </div>
                    </template>

                    <template x-if="currentStep === 2">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step2_student')
                        </div>
                    </template>

                    <template x-if="currentStep === 3">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step3_parents')
                        </div>
                    </template>

                    {{-- Step 4: x-show keeps DOM alive for location-cascade.js --}}
                    <div x-show="currentStep === 4" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('receptionist.student-registrations.partials.step4_address')
                    </div>

                    <template x-if="currentStep === 5">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step5_media')
                        </div>
                    </template>

                </div>

                <div class="px-6 md:px-8 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl flex items-center justify-between gap-4">
                    <template x-if="currentStep > 1">
                        <button type="button" @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left text-xs"></i> Previous
                        </button>
                    </template>
                    <template x-if="currentStep === 1">
                        <span aria-hidden="true"></span>
                    </template>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('receptionist.student-registrations.index') }}"
                           class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            Cancel
                        </a>

                        <template x-if="currentStep < 5">
                            <button type="button" @click="nextStep()"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                                Next <i class="fas fa-arrow-right text-xs"></i>
                            </button>
                        </template>

                        <template x-if="currentStep === 5">
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-75 cursor-wait' : ''"
                                    class="inline-flex items-center gap-2 px-8 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-bold rounded-lg shadow-md transition-all">
                                <template x-if="!submitting"><i class="fas fa-save text-xs"></i></template>
                                <template x-if="submitting"><i class="fas fa-circle-notch animate-spin text-xs"></i></template>
                                <span x-text="submitting ? 'Saving...' : 'Save Changes'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
function registrationManagement() {
    return {
        currentStep: 1,
        submitting: false,
        errors: {},
        autofillBanner: false,
        fatherExpanded: false,
        motherExpanded: false,
        correspondenceExpanded: {{ $studentRegistration->correspondence_address ? 'true' : 'false' }},
        stepLabels: ['Registration Info', 'Student Details', 'Parent Details', 'Address', 'Photos & Signatures'],

        registrationFees: {
            @foreach($classes as $class)
                '{{ $class->id }}': '{{ $class->registrationFee->amount ?? 0 }}',
            @endforeach
        },

        previews: {
            father_photo:      '{{ $studentRegistration->father_photo      ? "/storage/".$studentRegistration->father_photo      : "" }}',
            mother_photo:      '{{ $studentRegistration->mother_photo      ? "/storage/".$studentRegistration->mother_photo      : "" }}',
            student_photo:     '{{ $studentRegistration->student_photo     ? "/storage/".$studentRegistration->student_photo     : "" }}',
            father_signature:  '{{ $studentRegistration->father_signature  ? "/storage/".$studentRegistration->father_signature  : "" }}',
            mother_signature:  '{{ $studentRegistration->mother_signature  ? "/storage/".$studentRegistration->mother_signature  : "" }}',
            student_signature: '{{ $studentRegistration->student_signature ? "/storage/".$studentRegistration->student_signature : "" }}',
        },

        formData: {
            admission_status: '{{ $studentRegistration->admission_status instanceof AdmissionStatus ? $studentRegistration->admission_status->value : $studentRegistration->admission_status }}',
            enquiry_id: '{{ $studentRegistration->enquiry_id }}',
            academic_year_id: '{{ $studentRegistration->academic_year_id }}',
            class_id: '{{ $studentRegistration->class_id }}',
            registration_fee: '{{ $studentRegistration->registration_fee }}',
            first_name: @js($studentRegistration->first_name),
            middle_name: @js($studentRegistration->middle_name),
            last_name: @js($studentRegistration->last_name),
            gender: '{{ $studentRegistration->gender instanceof \App\Enums\Gender ? $studentRegistration->gender->value : $studentRegistration->gender }}',
            dob: '{{ $studentRegistration->dob ? \Carbon\Carbon::parse($studentRegistration->dob)->format('Y-m-d') : '' }}',
            email: @js($studentRegistration->email),
            mobile_no: @js($studentRegistration->mobile_no),
            student_type_id: '{{ $studentRegistration->student_type_id }}',
            aadhaar_no: @js($studentRegistration->aadhaar_no),
            place_of_birth: @js($studentRegistration->place_of_birth),
            nationality: '{{ $studentRegistration->nationality ?? 'Indian' }}',
            religion_id: '{{ $studentRegistration->religion_id }}',
            category_id: '{{ $studentRegistration->category_id }}',
            blood_group_id: '{{ $studentRegistration->blood_group_id }}',
            special_needs: @js($studentRegistration->special_needs),
            mother_tongue: @js($studentRegistration->mother_tongue),
            remarks: @js($studentRegistration->remarks),
            number_of_brothers: '{{ $studentRegistration->number_of_brothers ?? 0 }}',
            number_of_sisters: '{{ $studentRegistration->number_of_sisters ?? 0 }}',
            is_single_parent: '{{ $studentRegistration->is_single_parent ?? 0 }}',
            corresponding_relative_id: '{{ $studentRegistration->corresponding_relative_id }}',
            is_transport_required: '{{ $studentRegistration->is_transport_required ?? 0 }}',
            bus_stop: @js($studentRegistration->bus_stop),
            other_stop: @js($studentRegistration->other_stop),
            boarding_type_id: '{{ $studentRegistration->boarding_type_id }}',
            father_name_prefix: '{{ $studentRegistration->father_name_prefix ?? 'Mr' }}',
            father_first_name: @js($studentRegistration->father_first_name),
            father_middle_name: @js($studentRegistration->father_middle_name),
            father_last_name: @js($studentRegistration->father_last_name),
            father_mobile_no: @js($studentRegistration->father_mobile_no),
            father_email: @js($studentRegistration->father_email),
            father_occupation: @js($studentRegistration->father_occupation),
            father_organization: @js($studentRegistration->father_organization),
            father_office_address: @js($studentRegistration->father_office_address),
            father_qualification_id: '{{ $studentRegistration->father_qualification_id }}',
            father_department: @js($studentRegistration->father_department),
            father_designation: @js($studentRegistration->father_designation),
            father_annual_income: '{{ $studentRegistration->father_annual_income }}',
            father_aadhaar_no: @js($studentRegistration->father_aadhaar_no),
            father_age: '{{ $studentRegistration->father_age }}',
            father_landline_no: @js($studentRegistration->father_landline_no),
            mother_name_prefix: '{{ $studentRegistration->mother_name_prefix ?? 'Mrs' }}',
            mother_first_name: @js($studentRegistration->mother_first_name),
            mother_middle_name: @js($studentRegistration->mother_middle_name),
            mother_last_name: @js($studentRegistration->mother_last_name),
            mother_mobile_no: @js($studentRegistration->mother_mobile_no),
            mother_email: @js($studentRegistration->mother_email),
            mother_occupation: @js($studentRegistration->mother_occupation),
            mother_organization: @js($studentRegistration->mother_organization),
            mother_office_address: @js($studentRegistration->mother_office_address),
            mother_qualification_id: '{{ $studentRegistration->mother_qualification_id }}',
            mother_department: @js($studentRegistration->mother_department),
            mother_designation: @js($studentRegistration->mother_designation),
            mother_annual_income: '{{ $studentRegistration->mother_annual_income }}',
            mother_aadhaar_no: @js($studentRegistration->mother_aadhaar_no),
            mother_age: '{{ $studentRegistration->mother_age }}',
            mother_landline_no: @js($studentRegistration->mother_landline_no),
            permanent_address: @js($studentRegistration->permanent_address),
            permanent_country_id: '{{ $studentRegistration->permanent_country_id ?? 102 }}',
            permanent_state_id: '{{ $studentRegistration->permanent_state_id }}',
            permanent_city_id: '{{ $studentRegistration->permanent_city_id }}',
            permanent_pin: @js($studentRegistration->permanent_pin),
            permanent_state_of_domicile: @js($studentRegistration->permanent_state_of_domicile),
            permanent_railway_airport: @js($studentRegistration->permanent_railway_airport),
            permanent_correspondence_address: @js($studentRegistration->permanent_correspondence_address),
            correspondence_address: @js($studentRegistration->correspondence_address),
            correspondence_country_id: '{{ $studentRegistration->correspondence_country_id ?? 102 }}',
            correspondence_state_id: '{{ $studentRegistration->correspondence_state_id }}',
            correspondence_city_id: '{{ $studentRegistration->correspondence_city_id }}',
            correspondence_pin: @js($studentRegistration->correspondence_pin),
            correspondence_location: @js($studentRegistration->correspondence_location),
            distance_from_school: @js($studentRegistration->distance_from_school),
            correspondence_landmark: @js($studentRegistration->correspondence_landmark),
            enquiry_father_photo: '',
            enquiry_mother_photo: '',
            enquiry_student_photo: '',
            enquiry_father_signature: '',
            enquiry_mother_signature: '',
            enquiry_student_signature: '',
        },

        // Bug 7 fix: validate all intermediate steps when jumping forward via step indicator
        goToStep(n) {
            if (n > this.currentStep) {
                for (let s = this.currentStep; s < n; s++) {
                    const saved = this.currentStep;
                    this.currentStep = s;
                    if (!this.validateCurrentStep()) { return; }
                    this.currentStep = saved;
                }
            }
            this.currentStep = n;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        nextStep() {
            if (this.validateCurrentStep()) { this.currentStep++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },
        prevStep() {
            if (this.currentStep > 1) { this.currentStep--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },

        // Bug 4 fix: clear current step's own errors before re-validating so stale red borders don't persist
        validateCurrentStep() {
            const stepFields = {
                1: ['academic_year_id','class_id','admission_status'],
                2: ['first_name','last_name','gender','mobile_no'],
                3: ['father_first_name','father_last_name','father_mobile_no','mother_first_name','mother_last_name','mother_mobile_no'],
                4: ['permanent_address','permanent_pin'],
                5: [],
            };
            (stepFields[this.currentStep] || []).forEach(f => delete this.errors[f]);

            const e = {};
            if (this.currentStep === 1) {
                if (!this.formData.academic_year_id) e.academic_year_id = ['Academic year is required.'];
                if (!this.formData.class_id) e.class_id = ['Class is required.'];
            }
            if (this.currentStep === 2) {
                if (!this.formData.first_name?.trim()) e.first_name = ['First name is required.'];
                if (!this.formData.last_name?.trim()) e.last_name = ['Last name is required.'];
                if (!this.formData.gender) e.gender = ['Gender is required.'];
                if (!this.formData.mobile_no?.trim()) e.mobile_no = ['Mobile number is required.'];
            }
            if (this.currentStep === 3) {
                if (!this.formData.father_first_name?.trim()) e.father_first_name = ['Father first name is required.'];
                if (!this.formData.father_last_name?.trim()) e.father_last_name = ['Father last name is required.'];
                if (!this.formData.father_mobile_no?.trim()) e.father_mobile_no = ['Father mobile is required.'];
                if (!this.formData.mother_first_name?.trim()) e.mother_first_name = ['Mother first name is required.'];
                if (!this.formData.mother_last_name?.trim()) e.mother_last_name = ['Mother last name is required.'];
                if (!this.formData.mother_mobile_no?.trim()) e.mother_mobile_no = ['Mother mobile is required.'];
            }
            if (this.currentStep === 4) {
                if (!this.formData.permanent_address?.trim()) e.permanent_address = ['Address is required.'];
                if (!this.formData.permanent_pin?.trim()) e.permanent_pin = ['Pin code is required.'];
            }
            if (Object.keys(e).length > 0) {
                this.errors = { ...this.errors, ...e };
                if (this.currentStep === 3) {
                    if (e.father_first_name || e.father_last_name || e.father_mobile_no) this.fatherExpanded = true;
                    if (e.mother_first_name || e.mother_last_name || e.mother_mobile_no) this.motherExpanded = true;
                }
                if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Please fill in the required fields.' });
                return false;
            }
            return true;
        },

        // Bug 6 fix: use !== undefined so zero-fee classes correctly update the fee field
        updateFee() {
            const id = this.formData.class_id;
            if (id && this.registrationFees[id] !== undefined) {
                this.formData.registration_fee = this.registrationFees[id];
            } else {
                this.formData.registration_fee = '';
            }
        },

        // Edit page: enquiry fetch not needed (enquiry_id is locked), but keep for consistency
        async fetchEnquiryData() {},

        handlePhotoUpload(event, field) {
            const file = event.target.files[0];
            if (file) { this.previews[field] = URL.createObjectURL(file); this.clearError(field); }
        },

        removePhoto(field) {
            this.previews[field] = '';
            const input = document.querySelector(`input[name="${field}"]`);
            if (input) input.value = '';
            this.formData[`enquiry_${field}`] = '';
        },

        async submitForm() {
            if (!this.validateCurrentStep()) return;
            this.submitting = true;
            this.errors = {};

            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);
            fd.append('_method', 'PUT');

            const locationFields = new Set(['permanent_country_id','permanent_state_id','permanent_city_id',
                                            'correspondence_country_id','correspondence_state_id','correspondence_city_id']);
            Object.entries(this.formData).forEach(([key, value]) => {
                if (locationFields.has(key)) return;
                if (value === null || value === undefined || value === '') return;
                fd.append(key, value === true ? '1' : value === false ? '0' : value);
            });

            locationFields.forEach(name => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el && el.value) fd.append(name, el.value);
            });

            ['student_photo','father_photo','mother_photo','student_signature','father_signature','mother_signature'].forEach(name => {
                const input = document.querySelector(`input[name="${name}"]`);
                if (input && input.files && input.files[0]) fd.append(name, input.files[0]);
            });

            try {
                const response = await fetch("{{ route('receptionist.student-registrations.update', $studentRegistration->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: fd
                });
                const result = await response.json();
                if (response.status === 422) {
                    this.errors = result.errors;
                    this.handleValidationErrors(result.errors);
                } else if (response.ok) {
                    if (window.Toast) await window.Toast.fire({ icon: 'success', title: result.message || 'Registration updated' });
                    window.location.href = result.redirect || "{{ route('receptionist.student-registrations.index') }}";
                } else {
                    throw new Error(window.resolveApiMessage(result, 'Something went wrong'));
                }
            } catch (err) {
                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(err.response?.data || { message: err.message }, err.message || 'Failed to update registration') });
            } finally {
                this.submitting = false;
            }
        },

        // Bug 3 fix: evaluate step3 fields at call time, not at map-creation time
        handleValidationErrors(errors) {
            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage({ errors }, 'Please check the form for errors') });
            const step3Fields = Object.keys(errors).filter(f => f.startsWith('father_') || f.startsWith('mother_'));
            const map = {
                1: ['academic_year_id','class_id','registration_fee','admission_status'],
                2: ['first_name','last_name','gender','mobile_no','dob','email','aadhaar_no','religion','category'],
                3: step3Fields,
                4: ['permanent_address','permanent_country_id','permanent_state_id','permanent_city_id','permanent_pin'],
                5: ['student_photo','father_photo','mother_photo','student_signature','father_signature','mother_signature'],
            };
            for (let step = 1; step <= 5; step++) {
                const fields = map[step];
                if (Object.keys(errors).some(f => fields.includes(f))) {
                    this.currentStep = step;
                    if (step === 3) {
                        if (step3Fields.some(f => f.startsWith('father_'))) this.fatherExpanded = true;
                        if (step3Fields.some(f => f.startsWith('mother_'))) this.motherExpanded = true;
                    }
                    return;
                }
            }
        },

        clearError(field) {
                if (this.errors && this.errors[field]) { const e = { ...this.errors }; delete e[field]; this.errors = e; }
            },
    }
}
</script>
@endpush
