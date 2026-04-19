@extends('layouts.receptionist')

@section('title', 'Edit Admission')

@section('content')
<div class="p-6" x-data="admissionManagement()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Admission</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">{{ $student->full_name }} &mdash; {{ $student->admission_no }}</p>
        </div>
        <a href="{{ route('receptionist.admission.index') }}"
           class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div x-cloak>

        @include('receptionist.admission.partials.stepper_header')

        <form id="admissionForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="hidden" name="student_photo_path"     x-model="formData.student_photo_path">
            <input type="hidden" name="father_photo_path"      x-model="formData.father_photo_path">
            <input type="hidden" name="mother_photo_path"      x-model="formData.mother_photo_path">
            <input type="hidden" name="student_signature_path" x-model="formData.student_signature_path">
            <input type="hidden" name="father_signature_path"  x-model="formData.father_signature_path">
            <input type="hidden" name="mother_signature_path"  x-model="formData.mother_signature_path">

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-6 md:p-8 min-h-[480px]">

                    <template x-if="currentStep === 1">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.admission.partials.step1_admission')
                        </div>
                    </template>

                    <template x-if="currentStep === 2">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.admission.partials.step2_student')
                        </div>
                    </template>

                    <template x-if="currentStep === 3">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.admission.partials.step3_parents')
                        </div>
                    </template>

                    <div x-show="currentStep === 4" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('receptionist.admission.partials.step4_address')
                    </div>

                    <template x-if="currentStep === 5">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            @include('receptionist.admission.partials.step5_media')
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
                        <a href="{{ route('receptionist.admission.index') }}"
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
                                <template x-if="!submitting"><i class="fas fa-check-circle text-xs"></i></template>
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
function admissionManagement() {
    return {
        currentStep: 1,
        submitting: false,
        errors: {},
        autofillBanner: false,
        fatherExpanded: false,
        motherExpanded: false,
        correspondenceExpanded: {{ $student->correspondence_address ? 'true' : 'false' }},
        sections: @json($sections->where('class_id', $student->class_id)->values()),
        stepLabels: ['Admission Info', 'Student Details', 'Parent Details', 'Address', 'Photos & Signatures'],

        previews: {
            student_photo:     '{{ $student->student_photo ? asset('storage/' . $student->student_photo) : '' }}',
            father_photo:      '{{ $student->father_photo  ? asset('storage/' . $student->father_photo)  : '' }}',
            mother_photo:      '{{ $student->mother_photo  ? asset('storage/' . $student->mother_photo)  : '' }}',
            student_signature: '{{ $student->student_signature ? asset('storage/' . $student->student_signature) : '' }}',
            father_signature:  '{{ $student->father_signature  ? asset('storage/' . $student->father_signature)  : '' }}',
            mother_signature:  '{{ $student->mother_signature  ? asset('storage/' . $student->mother_signature)  : '' }}',
        },

        formData: {
            registration_id: '',
            registration_no:   '{{ $student->registration_no }}',
            academic_year_id:  '{{ $student->academic_year_id }}',
            class_id:          '{{ $student->class_id }}',
            section_id:        '{{ $student->section_id }}',
            admission_date:    '{{ $student->admission_date?->format('Y-m-d') }}',
            admission_fee:     '{{ $student->admission_fee }}',
            roll_no:           '{{ $student->roll_no }}',
            receipt_no:        '{{ $student->receipt_no }}',
            referred_by:       '{{ $student->referred_by }}',

            first_name:   '{{ $student->first_name }}',
            middle_name:  '{{ $student->middle_name }}',
            last_name:    '{{ $student->last_name }}',
            gender:       '{{ is_object($student->gender) ? $student->gender->value : $student->gender }}',
            dob:          '{{ $student->dob?->format('Y-m-d') }}',
            mobile_no:    '{{ $student->mobile_no }}',
            email:        '{{ $student->email }}',
            blood_group_id: '{{ $student->blood_group_id }}',
            aadhaar_no:    '{{ $student->aadhaar_no }}',
            dob_certificate_no: '{{ $student->dob_certificate_no }}',
            place_of_birth: '{{ $student->place_of_birth }}',
            nationality:  '{{ $student->nationality ?: 'Indian' }}',
            religion_id:  '{{ $student->religion_id }}',
            category_id:  '{{ $student->category_id }}',
            student_type_id: '{{ $student->student_type_id }}',
            corresponding_relative_id: '{{ $student->corresponding_relative_id }}',
            mother_tongue: '{{ $student->mother_tongue }}',
            special_needs: '{{ $student->special_needs }}',
            boarding_type_id: '{{ $student->boarding_type_id }}',
            number_of_brothers: '{{ $student->number_of_brothers ?? 0 }}',
            number_of_sisters:  '{{ $student->number_of_sisters  ?? 0 }}',
            remarks: '{{ $student->remarks }}',

            father_name_prefix:  'Mr',
            father_first_name:   '{{ $student->father_first_name }}',
            father_middle_name:  '{{ $student->father_middle_name }}',
            father_last_name:    '{{ $student->father_last_name }}',
            father_mobile_no:    '{{ $student->father_mobile_no }}',
            father_email:        '{{ $student->father_email }}',
            father_occupation:   '{{ $student->father_occupation }}',
            father_qualification_id:'{{ $student->father_qualification_id }}',
            father_annual_income:'{{ $student->father_annual_income }}',
            father_aadhaar_no:    '{{ $student->father_aadhaar_no }}',
            father_pan:          '{{ $student->father_pan }}',

            mother_name_prefix:  'Mrs',
            mother_first_name:   '{{ $student->mother_first_name }}',
            mother_middle_name:  '{{ $student->mother_middle_name }}',
            mother_last_name:    '{{ $student->mother_last_name }}',
            mother_mobile_no:    '{{ $student->mother_mobile_no }}',
            mother_email:        '{{ $student->mother_email }}',
            mother_occupation:   '{{ $student->mother_occupation }}',
            mother_qualification_id:'{{ $student->mother_qualification_id }}',
            mother_annual_income:'{{ $student->mother_annual_income }}',
            mother_aadhaar_no:    '{{ $student->mother_aadhaar_no }}',
            mother_pan:          '{{ $student->mother_pan }}',

            permanent_address:    '{{ $student->permanent_address }}',
            permanent_country_id: '{{ $student->permanent_country_id ?? 102 }}',
            permanent_state_id:   '{{ $student->permanent_state_id }}',
            permanent_city_id:    '{{ $student->permanent_city_id }}',
            permanent_pin:        '{{ $student->permanent_pin }}',
            state_of_domicile:    '{{ $student->state_of_domicile }}',
            railway_airport:      '{{ $student->railway_airport }}',
            correspondence_address:    '{{ $student->correspondence_address }}',
            correspondence_country_id: '{{ $student->correspondence_country_id ?? 102 }}',
            correspondence_state_id:   '{{ $student->correspondence_state_id }}',
            correspondence_city_id:    '{{ $student->correspondence_city_id }}',
            correspondence_pin:        '{{ $student->correspondence_pin }}',
            correspondence_location:   '{{ $student->correspondence_location }}',
            distance_from_school:      '{{ $student->distance_from_school }}',

            student_photo_path:     '{{ $student->student_photo }}',
            father_photo_path:      '{{ $student->father_photo }}',
            mother_photo_path:      '{{ $student->mother_photo }}',
            student_signature_path: '{{ $student->student_signature }}',
            father_signature_path:  '{{ $student->father_signature }}',
            mother_signature_path:  '{{ $student->mother_signature }}',
        },

        init() {
            if (this.formData.class_id) this.loadClassData(this.formData.class_id, false);
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
            if (this.validateCurrentStep()) { this.currentStep++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },
        prevStep() {
            if (this.currentStep > 1) { this.currentStep--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },

        validateCurrentStep() {
            const e = {};
            const stepFields = {
                1: ['academic_year_id','class_id','section_id','roll_no','receipt_no','admission_date'],
                2: ['first_name','last_name','gender','mobile_no','dob'],
                3: ['father_first_name','father_last_name','father_mobile_no','mother_first_name','mother_last_name','mother_mobile_no'],
                4: ['permanent_address','permanent_pin'],
                5: [],
            };
            (stepFields[this.currentStep] || []).forEach(f => delete this.errors[f]);

            if (this.currentStep === 1) {
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

        async loadClassData(classId, resetSection = true) {
            if (!classId) { this.sections = []; this.formData.admission_fee = ''; return; }
            try {
                const res = await fetch(`{{ url('receptionist/admission/class-data') }}/${classId}`);
                const data = await res.json();
                this.sections = data.sections || [];
                if (data.admission_fee && resetSection) this.formData.admission_fee = data.admission_fee;
            } catch (err) {
                console.error('Class data fetch error:', err);
            }
        },

        // Edit page: registration picker is read-only, no fetchRegistrationData needed

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
            fd.append('_method', 'PUT');

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
                const response = await fetch("{{ route('receptionist.admission.update', $student->id) }}", {
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
                    if (window.Toast) await window.Toast.fire({ icon: 'success', title: result.message || 'Student updated successfully' });
                    if (result.redirect) window.location.href = result.redirect;
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (err) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: err.message || 'Failed to save changes' });
            } finally {
                this.submitting = false;
            }
        },

        handleValidationErrors(errors) {
            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Please check the form for errors' });
            const step3Fields = Object.keys(errors).filter(f => f.startsWith('father_') || f.startsWith('mother_'));
            const map = {
                1: ['academic_year_id','class_id','section_id','roll_no','receipt_no','admission_date','admission_fee'],
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

        clearError(field) { delete this.errors[field]; },
    }
}
</script>
@endpush
