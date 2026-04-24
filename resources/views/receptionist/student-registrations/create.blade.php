@extends('layouts.receptionist')

@section('title', 'Add Student Registration')

@section('content')
<div class="p-6" x-data="registrationManagement()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Student Registration</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Fill in the details to register a new student</p>
        </div>
        <a href="{{ route('receptionist.student-registrations.index') }}"
           class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @include('receptionist.student-registrations.partials.stepper_header')

        <form id="registrationForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
            @csrf

            {{-- Enquiry carry-over hidden fields: always in DOM so FormData can read them --}}
            <input type="hidden" name="enquiry_father_photo"      x-model="formData.enquiry_father_photo">
            <input type="hidden" name="enquiry_mother_photo"      x-model="formData.enquiry_mother_photo">
            <input type="hidden" name="enquiry_student_photo"     x-model="formData.enquiry_student_photo">
            <input type="hidden" name="enquiry_father_signature"  x-model="formData.enquiry_father_signature">
            <input type="hidden" name="enquiry_mother_signature"  x-model="formData.enquiry_mother_signature">
            <input type="hidden" name="enquiry_student_signature" x-model="formData.enquiry_student_signature">

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-6 md:p-8 min-h-[480px]">

                    {{-- Step 1: Registration Info --}}
                    <div x-show="currentStep === 1">
                        @include('receptionist.student-registrations.partials.step1_reg')
                    </div>

                    {{-- Step 2: Student Details --}}
                    <template x-if="currentStep === 2">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step2_student')
                        </div>
                    </template>

                    {{-- Step 3: Parent Details --}}
                    <template x-if="currentStep === 3">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step3_parents')
                        </div>
                    </template>

                    {{-- Step 4: Address — x-show keeps DOM alive for location-cascade.js --}}
                    <div x-show="currentStep === 4" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('receptionist.student-registrations.partials.step4_address')
                    </div>

                    {{-- Step 5: Photos & Signatures --}}
                    <template x-if="currentStep === 5">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.student-registrations.partials.step5_media')
                        </div>
                    </template>

                </div>

                {{-- Footer navigation --}}
                <div class="px-6 md:px-8 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl flex items-center justify-between gap-4">
                    <div class="min-w-[118px]">
                        <button type="button" @click="prevStep()" x-show="currentStep > 1" x-cloak
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left text-xs"></i> Previous
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('receptionist.student-registrations.index') }}"
                           class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            Cancel
                        </a>

                        <button type="button" @click="nextStep()" x-show="currentStep < 5"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                            Next <i class="fas fa-arrow-right text-xs"></i>
                        </button>

                        <button type="submit" x-show="currentStep === 5" x-cloak
                                :disabled="submitting"
                                :class="submitting ? 'opacity-75 cursor-wait' : ''"
                                class="inline-flex items-center gap-2 px-8 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-bold rounded-lg shadow-md transition-all">
                            <template x-if="!submitting"><i class="fas fa-check-circle text-xs"></i></template>
                            <template x-if="submitting"><i class="fas fa-circle-notch animate-spin text-xs"></i></template>
                            <span x-text="submitting ? 'Processing...' : 'Complete Registration'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
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
        correspondenceExpanded: false,
        stepLabels: ['Registration Info', 'Student Details', 'Parent Details', 'Address', 'Photos & Signatures'],

        registrationFees: {
            @foreach($classes as $class)
                '{{ $class->id }}': '{{ $class->registrationFee->amount ?? 0 }}',
            @endforeach
        },

        categoryOptions: @json($categories->pluck('name','id')),
        religionOptions: @json($religions->pluck('name','id')),
        qualificationOptions: @json($qualifications->pluck('name','id')),
        bloodGroupOptions: @json($bloodGroups->pluck('name','id')),
        studentTypeOptions: @json($studentTypes->pluck('name','id')),
        correspondingRelativeOptions: @json($correspondingRelatives->pluck('name','id')),
        boardingTypeOptions: @json($boardingTypes->pluck('name','id')),

        previews: {
            father_photo: '', mother_photo: '', student_photo: '',
            father_signature: '', mother_signature: '', student_signature: ''
        },

        formData: {
            enquiry_id: '',
            academic_year_id: '{{ old('academic_year_id') }}',
            class_id: '{{ old('class_id') }}',
            registration_fee: '{{ old('registration_fee') }}',
            first_name: '{{ old('first_name') }}',
            middle_name: '{{ old('middle_name') }}',
            last_name: '{{ old('last_name') }}',
            gender: '{{ old('gender') }}',
            dob: '{{ old('dob') }}',
            email: '{{ old('email') }}',
            mobile_no: '{{ old('mobile_no') }}',
            aadhaar_no: '{{ old('aadhaar_no') }}',
            place_of_birth: '{{ old('place_of_birth') }}',
            nationality: '{{ old('nationality', 'Indian') }}',
            religion: '{{ old('religion') }}',
            religion_id: '{{ old('religion_id') }}',
            category: '{{ old('category') }}',
            category_id: '{{ old('category_id') }}',
            special_needs: '{{ old('special_needs') }}',
            mother_tongue: '{{ old('mother_tongue') }}',
            remarks: '{{ old('remarks') }}',
            number_of_brothers: '{{ old('number_of_brothers', 0) }}',
            number_of_sisters: '{{ old('number_of_sisters', 0) }}',
            is_single_parent: '{{ old('is_single_parent', 0) }}',
            corresponding_relative: '{{ old('corresponding_relative') }}',
            corresponding_relative_id: '{{ old('corresponding_relative_id') }}',
            is_transport_required: '{{ old('is_transport_required', 0) }}',
            bus_stop: '{{ old('bus_stop') }}',
            other_stop: '{{ old('other_stop') }}',
            boarding_type: '{{ old('boarding_type') }}',
            boarding_type_id: '{{ old('boarding_type_id') }}',
            blood_group: '{{ old('blood_group') }}',
            blood_group_id: '{{ old('blood_group_id') }}',
            student_type: '{{ old('student_type') }}',
            student_type_id: '{{ old('student_type_id') }}',
            father_name_prefix: 'Mr',
            father_first_name: '{{ old('father_first_name') }}',
            father_middle_name: '{{ old('father_middle_name') }}',
            father_last_name: '{{ old('father_last_name') }}',
            father_mobile_no: '{{ old('father_mobile_no') }}',
            father_email: '{{ old('father_email') }}',
            father_occupation: '{{ old('father_occupation') }}',
            father_organization: '{{ old('father_organization') }}',
            father_office_address: '{{ old('father_office_address') }}',
            father_qualification: '{{ old('father_qualification') }}',
            father_qualification_id: '{{ old('father_qualification_id') }}',
            father_department: '{{ old('father_department') }}',
            father_designation: '{{ old('father_designation') }}',
            father_annual_income: '{{ old('father_annual_income') }}',
            father_aadhaar_no: '{{ old('father_aadhaar_no') }}',
            father_age: '{{ old('father_age') }}',
            father_landline_no: '{{ old('father_landline_no') }}',
            mother_name_prefix: 'Mrs',
            mother_first_name: '{{ old('mother_first_name') }}',
            mother_middle_name: '{{ old('mother_middle_name') }}',
            mother_last_name: '{{ old('mother_last_name') }}',
            mother_mobile_no: '{{ old('mother_mobile_no') }}',
            mother_email: '{{ old('mother_email') }}',
            mother_occupation: '{{ old('mother_occupation') }}',
            mother_organization: '{{ old('mother_organization') }}',
            mother_office_address: '{{ old('mother_office_address') }}',
            mother_qualification: '{{ old('mother_qualification') }}',
            mother_qualification_id: '{{ old('mother_qualification_id') }}',
            mother_department: '{{ old('mother_department') }}',
            mother_designation: '{{ old('mother_designation') }}',
            mother_annual_income: '{{ old('mother_annual_income') }}',
            mother_aadhaar_no: '{{ old('mother_aadhaar_no') }}',
            mother_age: '{{ old('mother_age') }}',
            mother_landline_no: '{{ old('mother_landline_no') }}',
            permanent_address: '{{ old('permanent_address') }}',
            permanent_country_id: '{{ old('permanent_country_id', 102) }}',
            permanent_state_id: '{{ old('permanent_state_id') }}',
            permanent_city_id: '{{ old('permanent_city_id') }}',
            permanent_pin: '{{ old('permanent_pin') }}',
            permanent_state_of_domicile: '{{ old('permanent_state_of_domicile') }}',
            permanent_railway_airport: '{{ old('permanent_railway_airport') }}',
            permanent_correspondence_address: '{{ old('permanent_correspondence_address') }}',
            correspondence_address: '{{ old('correspondence_address') }}',
            correspondence_country_id: '{{ old('correspondence_country_id', 102) }}',
            correspondence_state_id: '{{ old('correspondence_state_id') }}',
            correspondence_city_id: '{{ old('correspondence_city_id') }}',
            correspondence_pin: '{{ old('correspondence_pin') }}',
            correspondence_location: '{{ old('correspondence_location') }}',
            distance_from_school: '{{ old('distance_from_school') }}',
            correspondence_landmark: '{{ old('correspondence_landmark') }}',
            enquiry_father_photo: '',
            enquiry_mother_photo: '',
            enquiry_student_photo: '',
            enquiry_father_signature: '',
            enquiry_mother_signature: '',
            enquiry_student_signature: '',
        },

        goToStep(n) {
            if (n > this.currentStep) {
                for (let s = this.currentStep; s < n; s++) {
                    const saved = this.currentStep;
                    this.currentStep = s;
                    if (!this.validateCurrentStep()) {
                        return;
                    }
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

        validateCurrentStep() {
            const e = {};
            const stepFields = {
                1: ['academic_year_id','class_id'],
                2: ['first_name','last_name','gender','mobile_no'],
                3: ['father_first_name','father_last_name','father_mobile_no','mother_first_name','mother_last_name','mother_mobile_no'],
                4: ['permanent_address','permanent_pin'],
                5: [],
            };
            (stepFields[this.currentStep] || []).forEach(f => delete this.errors[f]);

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

        updateFee() {
            const id = this.formData.class_id;
            if (id && this.registrationFees[id] !== undefined) {
                this.formData.registration_fee = this.registrationFees[id];
            } else {
                this.formData.registration_fee = '';
            }
        },

        // Fuzzy-match an enquiry value against the registration dropdown options.
        // Enquiry uses fixed enums (e.g. "General") while registration uses DB-seeded
        // names (e.g. "GEN") — we try exact, case-insensitive, then prefix match.
        matchOption(value, options) {
            if (value === null || value === undefined || value === '') return { id: '', name: '' };
            const v = String(value);
            
            // Try matching by name in the options object {id: name}
            const entries = Object.entries(options);
            
            // 1. Exact match
            let found = entries.find(([id, name]) => name === v);
            if (found) return { id: found[0], name: found[1] };
            
            // 2. Case-insensitive
            const lv = v.toLowerCase();
            found = entries.find(([id, name]) => String(name).toLowerCase() === lv);
            if (found) return { id: found[0], name: found[1] };
            
            // 3. Prefix/Contains match
            found = entries.find(([id, name]) => {
                const lo = String(name).toLowerCase();
                return lv.startsWith(lo) || lo.startsWith(lv);
            });
            return found ? { id: found[0], name: found[1] } : { id: '', name: '' };
        },

        async fetchEnquiryData() {
            const id = this.formData.enquiry_id;
            if (!id) return;
            try {
                const res = await fetch(`/receptionist/student-registrations/enquiry/${id}`);
                const result = await res.json();
                if (!result.success) return;
                const q = result.data;

                if (q.academic_year_id) this.formData.academic_year_id = q.academic_year_id;
                if (q.class_id) { this.formData.class_id = q.class_id; this.updateFee(); }

                const parts = (q.student_name || '').split(' ');
                this.formData.first_name  = parts[0] || '';
                this.formData.middle_name = parts.length > 2 ? parts.slice(1, -1).join(' ') : '';
                this.formData.last_name   = parts.length > 1 ? parts[parts.length - 1] : '';
                if (q.gender) this.formData.gender = q.gender;
                if (q.dob) {
                    const d = typeof q.dob === 'object' ? q.dob.date : q.dob;
                    if (d) this.formData.dob = d.substring(0, 10);
                }
                this.formData.email     = q.email_id || '';
                this.formData.mobile_no = q.contact_no || '';
                
                if (q.religion) {
                    const matched = this.matchOption(q.religion, this.religionOptions);
                    this.formData.religion = matched.name;
                    this.formData.religion_id = matched.id;
                }
                if (q.category) {
                    const matched = this.matchOption(q.category, this.categoryOptions);
                    this.formData.category = matched.name;
                    this.formData.category_id = matched.id;
                }
                if (q.blood_group) {
                    const matched = this.matchOption(q.blood_group, this.bloodGroupOptions);
                    this.formData.blood_group = matched.name;
                    this.formData.blood_group_id = matched.id;
                }
                
                if (q.aadhaar_no) this.formData.aadhaar_no = q.aadhaar_no;
                if (q.no_of_brothers !== undefined && q.no_of_brothers !== null) this.formData.number_of_brothers = q.no_of_brothers;
                if (q.no_of_sisters !== undefined && q.no_of_sisters !== null)   this.formData.number_of_sisters   = q.no_of_sisters;
                if (q.transport_facility) this.formData.is_transport_required = q.transport_facility === 'Yes' ? 1 : 0;
                if (q.permanent_address) this.formData.permanent_address = q.permanent_address;
                if (q.country_id) this.formData.permanent_country_id = q.country_id;

                const fp = (q.father_name || '').split(' ');
                this.formData.father_first_name   = fp[0] || '';
                this.formData.father_middle_name  = fp.length > 2 ? fp.slice(1, -1).join(' ') : '';
                this.formData.father_last_name    = fp.length > 1 ? fp[fp.length - 1] : '';
                this.formData.father_mobile_no    = q.father_contact || '';
                this.formData.father_email        = q.father_email || '';
                this.formData.father_occupation   = q.father_occupation || '';
                this.formData.father_organization = q.father_organization || '';
                this.formData.father_office_address = q.father_office_address || '';
                this.formData.father_department   = q.father_department || '';
                this.formData.father_designation  = q.father_designation || '';
                this.formData.father_annual_income = q.father_annual_income || '';
                if (q.father_qualification) {
                    const matched = this.matchOption(q.father_qualification, this.qualificationOptions);
                    this.formData.father_qualification = matched.name;
                    this.formData.father_qualification_id = matched.id;
                }

                const mp = (q.mother_name || '').split(' ');
                this.formData.mother_first_name   = mp[0] || '';
                this.formData.mother_middle_name  = mp.length > 2 ? mp.slice(1, -1).join(' ') : '';
                this.formData.mother_last_name    = mp.length > 1 ? mp[mp.length - 1] : '';
                this.formData.mother_mobile_no    = q.mother_contact || '';
                this.formData.mother_email        = q.mother_email || '';
                this.formData.mother_occupation   = q.mother_occupation || '';
                this.formData.mother_organization = q.mother_organization || '';
                this.formData.mother_office_address = q.mother_office_address || '';
                this.formData.mother_department   = q.mother_department || '';
                this.formData.mother_designation  = q.mother_designation || '';
                this.formData.mother_annual_income = q.mother_annual_income || '';
                if (q.mother_qualification) {
                    const matched = this.matchOption(q.mother_qualification, this.qualificationOptions);
                    this.formData.mother_qualification = matched.name;
                    this.formData.mother_qualification_id = matched.id;
                }

                // Auto-expand collapsibles when extended parent data arrived from enquiry
                if (q.father_organization || q.father_qualification || q.father_designation || q.father_department || q.father_annual_income) this.fatherExpanded = true;
                if (q.mother_organization || q.mother_qualification || q.mother_designation || q.mother_department || q.mother_annual_income) this.motherExpanded = true;

                ['father_photo','mother_photo','student_photo','father_signature','mother_signature','student_signature'].forEach(f => {
                    if (q[f]) {
                        this.formData[`enquiry_${f}`] = q[f];
                        this.previews[f] = `/storage/${q[f]}`;
                    }
                });

                const filledFields = [
                    'academic_year_id','class_id','registration_fee',
                    'first_name','middle_name','last_name','gender','dob','email','mobile_no','religion','category','aadhaar_no',
                    'number_of_brothers','number_of_sisters','is_transport_required',
                    'father_first_name','father_middle_name','father_last_name','father_mobile_no','father_email','father_occupation',
                    'father_organization','father_office_address','father_department','father_designation','father_annual_income','father_qualification',
                    'mother_first_name','mother_middle_name','mother_last_name','mother_mobile_no','mother_email','mother_occupation',
                    'mother_organization','mother_office_address','mother_department','mother_designation','mother_annual_income','mother_qualification',
                    'permanent_address','permanent_country_id',
                    'father_photo','mother_photo','student_photo','father_signature','mother_signature','student_signature',
                ];
                filledFields.forEach(f => delete this.errors[f]);

                this.autofillBanner = true;
                setTimeout(() => this.autofillBanner = false, 5000);
                if (window.Toast) window.Toast.fire({ icon: 'success', title: 'Form auto-filled from enquiry' });
            } catch (err) {
                console.error('Enquiry fetch error:', err);
            }
        },

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
                const response = await fetch("{{ route('receptionist.student-registrations.store') }}", {
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
                    if (window.Toast) await window.Toast.fire({ icon: 'success', title: result.message || 'Registration completed' });
                    if (result.redirect) window.location.href = result.redirect;
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (err) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: err.message || 'Failed to process registration' });
            } finally {
                this.submitting = false;
            }
        },

        handleValidationErrors(errors) {
            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Please check the form for errors' });
            const step3Fields = Object.keys(errors).filter(f => f.startsWith('father_') || f.startsWith('mother_'));
            const map = {
                1: ['academic_year_id','class_id','registration_fee','enquiry_id'],
                2: ['first_name','last_name','gender','mobile_no','dob','email','aadhaar_no','religion','category','student_type','boarding_type'],
                3: step3Fields,
                4: ['permanent_address','permanent_country_id','permanent_state_id','permanent_city_id','permanent_pin',
                    'correspondence_address','correspondence_country_id','correspondence_state_id','correspondence_city_id'],
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
