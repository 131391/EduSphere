@extends('layouts.school')

@section('title', 'Add Student Admission')

@section('content')
<div class="p-6" x-data="admissionManagement()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Student Admission</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Complete all steps to admit a new student</p>
        </div>
        <a href="{{ route('school.admission.index') }}"
           class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

        @include('school.admission.partials.stepper_header')

        <form id="admissionForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
            @csrf

            {{-- Hidden path fields for photo/signature carry-over from registration --}}
            <input type="hidden" name="student_photo_path"   x-model="formData.student_photo_path">
            <input type="hidden" name="father_photo_path"    x-model="formData.father_photo_path">
            <input type="hidden" name="mother_photo_path"    x-model="formData.mother_photo_path">
            <input type="hidden" name="student_signature_path" x-model="formData.student_signature_path">
            <input type="hidden" name="father_signature_path"  x-model="formData.father_signature_path">
            <input type="hidden" name="mother_signature_path"  x-model="formData.mother_signature_path">

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-6 md:p-8 min-h-[480px]">

                    {{-- Step 1: Admission Info --}}
                    <div x-show="currentStep === 1">
                        @include('school.admission.partials.step1_admission')
                    </div>

                    {{-- Step 2: Student Details --}}
                    <template x-if="currentStep === 2">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('school.admission.partials.step2_student')
                        </div>
                    </template>

                    {{-- Step 3: Parent Details --}}
                    <template x-if="currentStep === 3">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('school.admission.partials.step3_parents')
                        </div>
                    </template>

                    {{-- Step 4: Address — x-show keeps DOM alive for location-cascade.js --}}
                    <div x-show="currentStep === 4" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('school.admission.partials.step4_address')
                    </div>

                    {{-- Step 5: Photos & Signatures --}}
                    <template x-if="currentStep === 5">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('school.admission.partials.step5_media')
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
                        <a href="{{ route('school.admission.index') }}"
                           class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            Cancel
                        </a>

                        <button type="button" @click="nextStep()" x-show="currentStep < 5"
                                :disabled="currentStep === 1 && !formData.registration_id"
                                :class="(currentStep === 1 && !formData.registration_id) ? 'opacity-50 cursor-not-allowed' : ''"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                            Next <i class="fas fa-arrow-right text-xs"></i>
                        </button>

                        <button type="submit" x-show="currentStep === 5" x-cloak
                                :disabled="submitting"
                                :class="submitting ? 'opacity-75 cursor-wait' : ''"
                                class="inline-flex items-center gap-2 px-8 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-bold rounded-lg shadow-md transition-all">
                            <template x-if="!submitting"><i class="fas fa-check-circle text-xs"></i></template>
                            <template x-if="submitting"><i class="fas fa-circle-notch animate-spin text-xs"></i></template>
                            <span x-text="submitting ? 'Processing...' : 'Complete Admission'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
</div>
@endsection

@push('scripts')
<script>
function admissionManagement() {
    return {
        currentStep: 1,
        submitting: false,
        errors: {},
        autofillBanner: false,
        fatherExpanded: false,
        motherExpanded: false,
        correspondenceExpanded: false,
        sections: [],
        stepLabels: ['Admission Info', 'Student Details', 'Parent Details', 'Address', 'Photos & Signatures'],

        previews: {
            student_photo: '', father_photo: '', mother_photo: '',
            student_signature: '', father_signature: '', mother_signature: ''
        },

        formData: {
            registration_id: '',
            registration_no: '',
            academic_year_id: '{{ old('academic_year_id') }}',
            class_id: '{{ old('class_id') }}',
            section_id: '{{ old('section_id') }}',
            admission_date: '{{ old('admission_date', date('Y-m-d')) }}',
            admission_fee: '{{ old('admission_fee') }}',
            roll_no: '{{ old('roll_no') }}',
            receipt_no: '{{ old('receipt_no') }}',
            referred_by: '{{ old('referred_by') }}',

            first_name: '{{ old('first_name') }}',
            middle_name: '{{ old('middle_name') }}',
            last_name: '{{ old('last_name') }}',
            gender: '{{ old('gender') }}',
            dob: '{{ old('dob') }}',
            mobile_no: '{{ old('mobile_no') }}',
            email: '{{ old('email') }}',
            blood_group: '{{ old('blood_group') }}',
            blood_group_id: '{{ old('blood_group_id') }}',
            aadhaar_no: '{{ old('aadhaar_no') }}',
            dob_certificate_no: '{{ old('dob_certificate_no') }}',
            place_of_birth: '{{ old('place_of_birth') }}',
            nationality: '{{ old('nationality', 'Indian') }}',
            religion: '{{ old('religion') }}',
            religion_id: '{{ old('religion_id') }}',
            category: '{{ old('category') }}',
            category_id: '{{ old('category_id') }}',
            student_type: '{{ old('student_type') }}',
            student_type_id: '{{ old('student_type_id') }}',
            corresponding_relative: '{{ old('corresponding_relative') }}',
            corresponding_relative_id: '{{ old('corresponding_relative_id') }}',
            mother_tongue: '{{ old('mother_tongue') }}',
            special_needs: '{{ old('special_needs') }}',
            boarding_type: '{{ old('boarding_type') }}',
            boarding_type_id: '{{ old('boarding_type_id') }}',
            number_of_brothers: '{{ old('number_of_brothers', 0) }}',
            number_of_sisters: '{{ old('number_of_sisters', 0) }}',
            remarks: '{{ old('remarks') }}',

            father_name_prefix: 'Mr',
            father_first_name: '{{ old('father_first_name') }}',
            father_middle_name: '{{ old('father_middle_name') }}',
            father_last_name: '{{ old('father_last_name') }}',
            father_mobile_no: '{{ old('father_mobile_no') }}',
            father_email: '{{ old('father_email') }}',
            father_occupation: '{{ old('father_occupation') }}',
            father_qualification: '{{ old('father_qualification') }}',
            father_annual_income: '{{ old('father_annual_income') }}',
            father_aadhaar_no: '{{ old('father_aadhaar_no') }}',
            father_pan: '{{ old('father_pan') }}',

            mother_name_prefix: 'Mrs',
            mother_first_name: '{{ old('mother_first_name') }}',
            mother_middle_name: '{{ old('mother_middle_name') }}',
            mother_last_name: '{{ old('mother_last_name') }}',
            mother_mobile_no: '{{ old('mother_mobile_no') }}',
            mother_email: '{{ old('mother_email') }}',
            mother_occupation: '{{ old('mother_occupation') }}',
            mother_qualification: '{{ old('mother_qualification') }}',
            mother_annual_income: '{{ old('mother_annual_income') }}',
            mother_aadhaar_no: '{{ old('mother_aadhaar_no') }}',
            mother_pan: '{{ old('mother_pan') }}',

            permanent_address: '{{ old('permanent_address') }}',
            permanent_country_id: '{{ old('permanent_country_id', 102) }}',
            permanent_state_id: '{{ old('permanent_state_id') }}',
            permanent_city_id: '{{ old('permanent_city_id') }}',
            permanent_pin: '{{ old('permanent_pin') }}',
            state_of_domicile: '{{ old('state_of_domicile') }}',
            railway_airport: '{{ old('railway_airport') }}',
            correspondence_address: '{{ old('correspondence_address') }}',
            correspondence_country_id: '{{ old('correspondence_country_id', 102) }}',
            correspondence_state_id: '{{ old('correspondence_state_id') }}',
            correspondence_city_id: '{{ old('correspondence_city_id') }}',
            correspondence_pin: '{{ old('correspondence_pin') }}',
            correspondence_location: '{{ old('correspondence_location') }}',
            distance_from_school: '{{ old('distance_from_school') }}',

            student_photo_path: '',
            father_photo_path: '',
            mother_photo_path: '',
            student_signature_path: '',
            father_signature_path: '',
            mother_signature_path: '',
        },

        init() {
            if (this.formData.class_id) {
                this.loadClassData(this.formData.class_id);
            }
        },

        goToStep(n) {
            if (n > this.currentStep) {
                for (let s = this.currentStep; s < n; s++) {
                    const saved = this.currentStep;
                    this.currentStep = s;
                    if (!this.validateCurrentStep()) return;
                    this.currentStep = saved;
                }
            }
            this.currentStep = n;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        nextStep() {
            // Block progression entirely if no registrations available
            if (this.currentStep === 1 && !this.formData.registration_id && {{ $registrations->isEmpty() ? 'true' : 'false' }}) {
                if (window.Toast) window.Toast.fire({ icon: 'warning', title: 'Please create a registration first.' });
                return;
            }
            if (this.validateCurrentStep()) { this.currentStep++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },
        prevStep() {
            if (this.currentStep > 1) { this.currentStep--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },

        validateCurrentStep() {
            const e = {};
            const stepFields = {
                1: ['registration_id','academic_year_id','class_id','section_id','roll_no','receipt_no','admission_date'],
                2: ['first_name','last_name','gender','mobile_no','dob'],
                3: ['father_first_name','father_last_name','father_mobile_no','mother_first_name','mother_last_name','mother_mobile_no'],
                4: ['permanent_address','permanent_pin'],
                5: [],
            };
            (stepFields[this.currentStep] || []).forEach(f => delete this.errors[f]);

            if (this.currentStep === 1) {
                if (!this.formData.registration_id) e.registration_id = ['A registration must be selected to proceed.'];
                if (!this.formData.academic_year_id) e.academic_year_id = ['Academic year is required.'];
                if (!this.formData.class_id)         e.class_id         = ['Class is required.'];
                if (!this.formData.section_id)       e.section_id       = ['Section is required.'];
                if (!this.formData.roll_no?.trim())  e.roll_no          = ['Roll number is required.'];
                if (!this.formData.receipt_no?.trim()) e.receipt_no     = ['Receipt number is required.'];
                if (!this.formData.admission_date)   e.admission_date   = ['Admission date is required.'];
            }
            if (this.currentStep === 2) {
                if (!this.formData.first_name?.trim()) e.first_name = ['First name is required.'];
                if (!this.formData.last_name?.trim())  e.last_name  = ['Last name is required.'];
                if (!this.formData.gender)             e.gender     = ['Gender is required.'];
                if (!this.formData.mobile_no?.trim())  e.mobile_no  = ['Mobile number is required.'];
                if (!this.formData.dob)                e.dob        = ['Date of birth is required.'];
            }
            if (this.currentStep === 3) {
                if (!this.formData.father_first_name?.trim()) e.father_first_name = ['Father first name is required.'];
                if (!this.formData.father_last_name?.trim())  e.father_last_name  = ['Father last name is required.'];
                if (!this.formData.father_mobile_no?.trim())  e.father_mobile_no  = ['Father mobile is required.'];
                if (!this.formData.mother_first_name?.trim()) e.mother_first_name = ['Mother first name is required.'];
                if (!this.formData.mother_last_name?.trim())  e.mother_last_name  = ['Mother last name is required.'];
                if (!this.formData.mother_mobile_no?.trim())  e.mother_mobile_no  = ['Mother mobile is required.'];
            }
            if (this.currentStep === 4) {
                if (!this.formData.permanent_address?.trim()) e.permanent_address = ['Address is required.'];
                if (!this.formData.permanent_pin?.trim())     e.permanent_pin     = ['Pin code is required.'];
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

        async loadClassData(classId) {
            if (!classId) { this.sections = []; this.formData.admission_fee = ''; return; }
            try {
                const res = await fetch(`{{ url('school/admission/class-data') }}/${classId}`);
                const data = await res.json();
                this.sections = data.sections || [];
                if (data.admission_fee) this.formData.admission_fee = data.admission_fee;
            } catch (err) {
                console.error('Class data fetch error:', err);
            }
        },

        async fetchRegistrationData() {
            const id = this.formData.registration_id;
            if (!id) return;
            try {
                const res = await fetch(`{{ url('school/admission/registration') }}/${id}`);
                if (!res.ok) throw new Error('Registration not found');
                const d = await res.json();

                this.formData.registration_no   = d.registration_no || '';
                this.formData.academic_year_id  = d.academic_year_id || '';
                if (d.class_id) { this.formData.class_id = d.class_id; await this.loadClassData(d.class_id); }

                this.formData.first_name   = d.first_name  || '';
                this.formData.middle_name  = d.middle_name || '';
                this.formData.last_name    = d.last_name   || '';
                if (d.gender) this.formData.gender = d.gender;
                if (d.dob)    this.formData.dob    = d.dob.substring(0, 10);
                this.formData.mobile_no    = d.mobile_no   || '';
                this.formData.email        = d.email       || '';
                this.formData.aadhaar_no    = d.aadhaar_no   || '';
                this.formData.blood_group   = d.blood_group  || '';
                this.formData.blood_group_id = d.blood_group_id || '';
                this.formData.religion     = d.religion    || '';
                this.formData.religion_id  = d.religion_id || '';
                this.formData.category     = d.category    || '';
                this.formData.category_id  = d.category_id || '';
                this.formData.student_type = d.student_type || '';
                this.formData.student_type_id = d.student_type_id || '';
                this.formData.nationality  = d.nationality || 'Indian';
                this.formData.place_of_birth = d.place_of_birth || '';
                this.formData.mother_tongue  = d.mother_tongue  || '';
                this.formData.corresponding_relative = d.corresponding_relative || '';
                this.formData.corresponding_relative_id = d.corresponding_relative_id || '';
                this.formData.boarding_type = d.boarding_type || '';
                this.formData.boarding_type_id = d.boarding_type_id || '';
                this.formData.number_of_brothers = d.number_of_brothers ?? 0;
                this.formData.number_of_sisters  = d.number_of_sisters  ?? 0;

                this.formData.permanent_address    = d.permanent_address    || '';
                this.formData.permanent_country_id = d.permanent_country_id || 102;
                this.formData.permanent_state_id   = d.permanent_state_id   || '';
                this.formData.permanent_city_id    = d.permanent_city_id    || '';
                this.formData.permanent_pin        = d.permanent_pin        || '';

                // Split father name from registration
                this.formData.father_first_name  = d.father_first_name  || '';
                this.formData.father_middle_name = d.father_middle_name || '';
                this.formData.father_last_name   = d.father_last_name   || '';
                this.formData.father_mobile_no   = d.father_mobile_no   || '';
                this.formData.father_email       = d.father_email       || '';
                this.formData.father_occupation  = d.father_occupation  || '';
                this.formData.father_qualification = d.father_qualification || '';
                this.formData.father_qualification_id = d.father_qualification_id || '';
                this.formData.father_annual_income = d.father_annual_income || '';
                this.formData.father_aadhaar_no   = d.father_aadhaar_no   || '';

                this.formData.mother_first_name  = d.mother_first_name  || '';
                this.formData.mother_middle_name = d.mother_middle_name || '';
                this.formData.mother_last_name   = d.mother_last_name   || '';
                this.formData.mother_mobile_no   = d.mother_mobile_no   || '';
                this.formData.email             = d.mother_email       || '';
                this.formData.mother_occupation  = d.mother_occupation  || '';
                this.formData.mother_qualification = d.mother_qualification || '';
                this.formData.mother_qualification_id = d.mother_qualification_id || '';
                this.formData.mother_annual_income = d.mother_annual_income || '';
                this.formData.mother_aadhaar_no   = d.mother_aadhaar_no   || '';

                // Carry over photos/signatures as path references
                ['student_photo','father_photo','mother_photo','student_signature','father_signature','mother_signature'].forEach(f => {
                    if (d[f]) {
                        this.formData[`${f}_path`] = d[f];
                        this.previews[f] = `/storage/${d[f]}`;
                    }
                });

                // Auto-expand parent sections if data arrived
                if (d.father_occupation || d.father_qualification) this.fatherExpanded = true;
                if (d.mother_occupation || d.mother_qualification) this.motherExpanded = true;

                this.autofillBanner = true;
                setTimeout(() => this.autofillBanner = false, 5000);
                if (window.Toast) window.Toast.fire({ icon: 'success', title: 'Form auto-filled from registration' });
            } catch (err) {
                console.error('Registration fetch error:', err);
                if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Could not load registration data' });
            }
        },

        handlePhotoUpload(event, field) {
            const file = event.target.files[0];
            if (file) {
                this.previews[field] = URL.createObjectURL(file);
                this.formData[`${field}_path`] = '';
                this.clearError(field);
            }
        },

        removePhoto(field) {
            this.previews[field] = '';
            this.formData[`${field}_path`] = '';
            const input = document.querySelector(`input[name="${field}"]`);
            if (input) input.value = '';
        },

        async submitForm() {
            if (!this.validateCurrentStep()) return;
            this.submitting = true;
            this.errors = {};

            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);

            const locationFields = new Set([
                'permanent_country_id','permanent_state_id','permanent_city_id',
                'correspondence_country_id','correspondence_state_id','correspondence_city_id'
            ]);

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
                const response = await fetch("{{ route('school.admission.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: fd
                });
                const result = await response.json();
                if (response.status === 422) {
                    this.errors = result.errors || {};
                    this.handleValidationErrors(result.errors || {});
                } else if (response.ok) {
                    if (window.Toast) await window.Toast.fire({ icon: 'success', title: result.message || 'Student admitted successfully' });
                    if (result.redirect) window.location.href = result.redirect;
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (err) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: err.message || 'Failed to process admission' });
            } finally {
                this.submitting = false;
            }
        },

        handleValidationErrors(errors) {
            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Please check the form for errors' });
            const step3Fields = Object.keys(errors).filter(f => f.startsWith('father_') || f.startsWith('mother_'));
            const map = {
                1: ['academic_year_id','class_id','section_id','roll_no','receipt_no','admission_date','admission_fee','registration_id'],
                2: ['first_name','last_name','gender','mobile_no','dob','email','aadhaar_no','blood_group','religion','category','student_type'],
                3: step3Fields,
                4: ['permanent_address','permanent_country_id','permanent_state_id','permanent_city_id','permanent_pin',
                    'correspondence_address','correspondence_country_id','correspondence_state_id','correspondence_city_id'],
                5: ['student_photo','father_photo','mother_photo','student_signature','father_signature','mother_signature'],
            };
            for (let step = 1; step <= 5; step++) {
                if (Object.keys(errors).some(f => map[step].includes(f))) {
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
