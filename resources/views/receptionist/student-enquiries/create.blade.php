@extends('layouts.receptionist')

@section('title', 'New Student Enquiry')

@section('content')
<div class="p-6" x-data="enquiryManagement()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">New Student Enquiry</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Fill in the details to record a new enquiry</p>
        </div>
        <a href="{{ route('receptionist.student-enquiries.index') }}"
           class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2 text-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div x-cloak>

    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                Step <span x-text="currentStep"></span> of 4
            </span>
            <span class="text-xs font-semibold text-teal-600" x-text="stepLabels[currentStep - 1]"></span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
            <div class="bg-gradient-to-r from-teal-500 to-emerald-500 h-1.5 rounded-full transition-all duration-500"
                 :style="`width: ${(currentStep / 4) * 100}%`"></div>
        </div>
    </div>

    <div class="mb-8">
    <div class="flex items-center gap-0">
        @php
        $steps = [
            ['icon' => 'fa-clipboard', 'label' => 'Basic Info'],
            ['icon' => 'fa-users', 'label' => 'Parents'],
            ['icon' => 'fa-address-card', 'label' => 'Contact & Personal'],
            ['icon' => 'fa-camera', 'label' => 'Photos'],
        ];
        @endphp
        @foreach($steps as $i => $step)
        @php $n = $i + 1; @endphp
        <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
            <button type="button" @click="goToStep({{ $n }})"
                    class="flex flex-col items-center gap-1 group focus:outline-none">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200 border-2"
                     :class="currentStep === {{ $n }}
                        ? 'bg-teal-600 border-teal-600 text-white shadow-lg shadow-teal-200 dark:shadow-none'
                        : currentStep > {{ $n }}
                            ? 'bg-emerald-500 border-emerald-500 text-white'
                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 group-hover:border-teal-400'">
                    <i class="fas fa-check text-xs" x-show="currentStep > {{ $n }}"></i>
                    <i class="fas {{ $step['icon'] }} text-xs" x-show="currentStep <= {{ $n }}"></i>
                </div>
                <span class="text-[10px] font-semibold hidden sm:block transition-colors"
                      :class="currentStep === {{ $n }} ? 'text-teal-600' : currentStep > {{ $n }} ? 'text-emerald-500' : 'text-gray-400'">
                    {{ $step['label'] }}
                </span>
            </button>
            @if($i < count($steps) - 1)
            <div class="flex-1 h-0.5 mx-2 rounded transition-all duration-500"
                 :class="currentStep > {{ $n }} ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-gray-700'"></div>
            @endif
        </div>
        @endforeach
    </div>
    </div>

    <form id="enquiryForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="p-6 md:p-8 min-h-[420px]">

                <template x-if="currentStep === 1">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('school.student-enquiries.partials.step1')
                    </div>
                </template>

                <template x-if="currentStep === 2">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('school.student-enquiries.partials.step2')
                    </div>
                </template>

                <template x-if="currentStep === 3">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('school.student-enquiries.partials.step3')
                    </div>
                </template>

                <template x-if="currentStep === 4">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        @include('school.student-enquiries.partials.step4')
                    </div>
                </template>

            </div>

            <div class="px-6 md:px-8 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl flex items-center justify-between gap-4">
                <template x-if="currentStep > 1">
                    <button type="button" @click="prevStep()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-arrow-left text-xs"></i> Previous
                    </button>
                </template>
                <template x-if="currentStep === 1">
                    <span aria-hidden="true"></span>
                </template>

                <div class="flex items-center gap-3">
                    <a href="{{ route('receptionist.student-enquiries.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        Cancel
                    </a>

                    <template x-if="currentStep < 4">
                        <button type="button" @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                            Next <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </template>

                    <template x-if="currentStep === 4">
                        <button type="submit"
                                :disabled="submitting"
                                :class="submitting ? 'opacity-75 cursor-wait' : ''"
                                class="inline-flex items-center gap-2 px-8 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-bold rounded-lg shadow-md transition-all">
                            <template x-if="!submitting"><i class="fas fa-check-circle text-xs"></i></template>
                            <template x-if="submitting"><i class="fas fa-circle-notch animate-spin text-xs"></i></template>
                            <span x-text="submitting ? 'Processing...' : 'Record Enquiry'"></span>
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
function enquiryManagement() {
    return {
        currentStep: 1,
        submitting: false,
        errors: {},
        fatherExpanded: false,
        motherExpanded: false,
        contactExpanded: false,
        stepLabels: ['Basic Info', 'Parent Details', 'Contact & Personal', 'Photos'],

        formData: {
            academic_year_id: '{{ old('academic_year_id') }}',
            class_id: '{{ old('class_id') }}',
            subject_name: '{{ old('subject_name') }}',
            student_name: '{{ old('student_name') }}',
            gender: '{{ old('gender') }}',
            follow_up_date: '{{ old('follow_up_date', date('Y-m-d')) }}',
            father_name: '{{ old('father_name') }}',
            father_contact: '{{ old('father_contact') }}',
            father_email: '{{ old('father_email') }}',
            father_qualification_id: '{{ old('father_qualification_id') }}',
            father_occupation: '{{ old('father_occupation') }}',
            father_annual_income: '{{ old('father_annual_income') }}',
            father_organization: '{{ old('father_organization') }}',
            father_office_address: '{{ old('father_office_address') }}',
            father_department: '{{ old('father_department') }}',
            father_designation: '{{ old('father_designation') }}',
            mother_name: '{{ old('mother_name') }}',
            mother_contact: '{{ old('mother_contact') }}',
            mother_email: '{{ old('mother_email') }}',
            mother_qualification_id: '{{ old('mother_qualification_id') }}',
            mother_occupation: '{{ old('mother_occupation') }}',
            mother_annual_income: '{{ old('mother_annual_income') }}',
            mother_organization: '{{ old('mother_organization') }}',
            mother_office_address: '{{ old('mother_office_address') }}',
            mother_department: '{{ old('mother_department') }}',
            mother_designation: '{{ old('mother_designation') }}',
            contact_no: '{{ old('contact_no') }}',
            whatsapp_no: '{{ old('whatsapp_no') }}',
            facebook_id: '{{ old('facebook_id') }}',
            email_id: '{{ old('email_id') }}',
            sms_no: '{{ old('sms_no') }}',
            twitter_id: '{{ old('twitter_id') }}',
            emergency_contact_no: '{{ old('emergency_contact_no') }}',
            dob: '{{ old('dob') }}',
            aadhaar_no: '{{ old('aadhaar_no') }}',
            grand_father_name: '{{ old('grand_father_name') }}',
            annual_income: '{{ old('annual_income') }}',
            no_of_brothers: '{{ old('no_of_brothers', 0) }}',
            no_of_sisters: '{{ old('no_of_sisters', 0) }}',
            blood_group: '{{ old('blood_group') }}',
            blood_group_id: '{{ old('blood_group_id') }}',
            category: '{{ old('category') }}',
            category_id: '{{ old('category_id') }}',
            minority: '{{ old('minority') }}',
            religion: '{{ old('religion') }}',
            religion_id: '{{ old('religion_id') }}',
            transport_facility: '{{ old('transport_facility') }}',
            hostel_facility: '{{ old('hostel_facility') }}',
            previous_class: '{{ old('previous_class') }}',
            identity_marks: '{{ old('identity_marks') }}',
            permanent_address: '{{ old('permanent_address') }}',
            country_id: '{{ old('country_id', 102) }}',
            previous_school_name: '{{ old('previous_school_name') }}',
            student_roll_no: '{{ old('student_roll_no') }}',
            passing_year: '{{ old('passing_year') }}',
            exam_name: '{{ old('exam_name') }}',
            board_university: '{{ old('board_university') }}',
            only_child: {{ old('only_child') ? 'true' : 'false' }},
        },

        goToStep(n) {
            if (n < this.currentStep || this.validateCurrentStep()) {
                this.currentStep = n;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        nextStep() {
            if (this.validateCurrentStep()) { this.currentStep++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },
        prevStep() {
            if (this.currentStep > 1) { this.currentStep--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },

        validateCurrentStep() {
            const stepErrors = {};
            if (this.currentStep === 1) {
                if (!this.formData.academic_year_id) stepErrors.academic_year_id = ['Academic year is required.'];
                if (!this.formData.class_id) stepErrors.class_id = ['Class is required.'];
                if (!this.formData.student_name?.trim()) stepErrors.student_name = ['Student name is required.'];
            }
            if (this.currentStep === 2) {
                if (!this.formData.father_name?.trim()) stepErrors.father_name = ['Father name is required.'];
                if (!this.formData.father_contact?.trim()) stepErrors.father_contact = ['Father contact is required.'];
                if (!this.formData.mother_name?.trim()) stepErrors.mother_name = ['Mother name is required.'];
                if (!this.formData.mother_contact?.trim()) stepErrors.mother_contact = ['Mother contact is required.'];
            }
            if (this.currentStep === 3) {
                if (!this.formData.contact_no?.trim()) stepErrors.contact_no = ['Contact number is required.'];
                if (!this.formData.whatsapp_no?.trim()) stepErrors.whatsapp_no = ['WhatsApp number is required.'];
                if (!this.formData.country_id) stepErrors.country_id = ['Country is required.'];
            }
            if (Object.keys(stepErrors).length > 0) {
                this.errors = { ...this.errors, ...stepErrors };
                if (this.currentStep === 2) {
                    if (stepErrors.father_name || stepErrors.father_contact) this.fatherExpanded = true;
                    if (stepErrors.mother_name || stepErrors.mother_contact) this.motherExpanded = true;
                }
                if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Please fill in the required fields.' });
                return false;
            }
            return true;
        },

        async submitForm() {
            if (!this.validateCurrentStep()) return;
            this.submitting = true;
            this.errors = {};

            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);
            Object.entries(this.formData).forEach(([key, value]) => {
                if (value === null || value === undefined || value === '') return;
                fd.append(key, value === true ? '1' : value === false ? '0' : value);
            });
            ['student_photo', 'father_photo', 'mother_photo'].forEach(name => {
                const input = document.querySelector(`input[name="${name}"]`);
                if (input && input.files && input.files[0]) fd.append(name, input.files[0]);
            });

            try {
                const response = await fetch("{{ route('receptionist.student-enquiries.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });
                const result = await response.json();
                if (response.status === 422) {
                    this.errors = result.errors;
                    this.handleValidationErrors(result.errors);
                } else if (response.ok) {
                    if (window.Toast) await window.Toast.fire({ icon: 'success', title: result.message || 'Enquiry recorded successfully' });
                    if (result.redirect) window.location.href = result.redirect;
                } else {
                    throw new Error(window.resolveApiMessage(result, 'Something went wrong'));
                }
            } catch (error) {
                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(error.response?.data || { message: error.message }, error.message || 'Failed to process enquiry') });
            } finally {
                this.submitting = false;
            }
        },

        handleValidationErrors(errors) {
            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage({ errors }, 'Please check the form for errors') });
            const step1Fields = ['academic_year_id','class_id','student_name','gender','subject_name','follow_up_date'];
            const step2Fields = Object.keys(errors).filter(f => f.startsWith('father_') || f.startsWith('mother_'));
            const step3Fields = ['contact_no','whatsapp_no','email_id','country_id','dob','aadhaar_no','category','religion'];
            const step4Fields = ['student_photo','father_photo','mother_photo'];
            if (Object.keys(errors).some(f => step1Fields.includes(f))) { this.currentStep = 1; return; }
            if (step2Fields.length > 0) {
                this.currentStep = 2;
                if (step2Fields.some(f => f.startsWith('father_'))) this.fatherExpanded = true;
                if (step2Fields.some(f => f.startsWith('mother_'))) this.motherExpanded = true;
                return;
            }
            if (Object.keys(errors).some(f => step3Fields.includes(f))) { this.currentStep = 3; return; }
            if (Object.keys(errors).some(f => step4Fields.includes(f))) { this.currentStep = 4; return; }
        },

        clearError(field) { delete this.errors[field]; },

        previewPhoto(event, previewId, iconId, removeBtnId) {
            const file = event.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.getElementById(previewId);
                const icon = document.getElementById(iconId);
                const btn = document.getElementById(removeBtnId);
                if (preview) { preview.src = e.target.result; preview.classList.remove('hidden'); }
                if (icon) icon.classList.add('hidden');
                if (btn) btn.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        },

        removePhoto(inputName, previewId, iconId, removeBtnId) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            if (input) input.value = '';
            const preview = document.getElementById(previewId);
            const icon = document.getElementById(iconId);
            const btn = document.getElementById(removeBtnId);
            if (preview) { preview.src = '#'; preview.classList.add('hidden'); }
            if (icon) icon.classList.remove('hidden');
            if (btn) btn.classList.add('hidden');
        }
    }
}
</script>
@endpush
