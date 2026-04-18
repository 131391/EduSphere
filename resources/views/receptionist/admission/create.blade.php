@extends('layouts.receptionist')

@section('title', 'Admission Form')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Admission Form</h1>
        <a href="{{ route('receptionist.admission.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>





    <form x-data="admissionForm()" 
          @submit.prevent="submitForm"
          action="{{ route('receptionist.admission.store') }}" 
          method="POST" 
          id="admissionForm"
          enctype="multipart/form-data" 
          class="space-y-8">
        @csrf
        
        <!-- Admission Info -->
        @include('receptionist.admission.partials._admission_info')

        <!-- Personal Info -->
        @include('receptionist.admission.partials._personal_info')

        <!-- Father Details -->
        @include('receptionist.admission.partials._father_details')

        <!-- Mother Details -->
        @include('receptionist.admission.partials._mother_details')

        <!-- Address Details -->
        @include('receptionist.admission.partials._address_details')

        <!-- Correspondence Address -->
        @include('receptionist.admission.partials._correspondence_address')

        <!-- Photo Details -->
        @include('receptionist.admission.partials._photo_details')

        <!-- Signature Details -->
        @include('receptionist.admission.partials._signature_details')

        <div class="flex justify-end gap-4">
            <button type="reset" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Reset</button>
            <button type="submit" id="submit-btn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Submit Admission</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('admissionForm', () => ({
        submitting: false,
        registrationId: '',
        sections: [],
        errors: {},
        
        formData: {
            registration_id: '',
            registration_no: '',
            academic_year_id: '{{ old('academic_year_id', isset($student) ? $student->academic_year_id : '') }}',
            class_id: '{{ old('class_id', isset($student) ? $student->class_id : '') }}',
            section_id: '{{ old('section_id', isset($student) ? $student->section_id : '') }}',
            admission_date: '{{ old('admission_date', isset($student) && $student->admission_date ? $student->admission_date->format('Y-m-d') : '') }}',
            roll_no: '{{ old('roll_no', isset($student) ? $student->roll_no : '') }}',
            receipt_no: '{{ old('receipt_no', isset($student) ? $student->receipt_no : '') }}',
            admission_fee: '{{ old('admission_fee', isset($student) ? $student->admission_fee : '') }}',
            
            // Personal Info
            first_name: '{{ old('first_name', isset($student) ? $student->first_name : '') }}',
            middle_name: '{{ old('middle_name', isset($student) ? $student->middle_name : '') }}',
            last_name: '{{ old('last_name', isset($student) ? $student->last_name : '') }}',
            gender: '{{ old('gender', isset($student) ? (is_object($student->gender) ? $student->gender->value : $student->gender) : '') }}',
            date_of_birth: '{{ old('date_of_birth', isset($student) && $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}',
            email: '{{ old('email', isset($student) ? $student->email : '') }}',
            phone: '{{ old('phone', isset($student) ? $student->phone : '') }}',
            student_type: '{{ old('student_type', isset($student) ? $student->student_type : '') }}',
            blood_group: '{{ old('blood_group', isset($student) ? $student->blood_group : '') }}',
            aadhar_no: '{{ old('aadhar_no', isset($student) ? $student->aadhar_no : '') }}',
            nationality: '{{ old('nationality', isset($student) ? $student->nationality : 'India') }}',
            religion: '{{ old('religion', isset($student) ? $student->religion : '') }}',
            category: '{{ old('category', isset($student) ? $student->category : '') }}',
            
            // Father Details
            father_first_name: '{{ old('father_first_name', isset($student) ? $student->father_first_name : '') }}',
            father_middle_name: '{{ old('father_middle_name', isset($student) ? $student->father_middle_name : '') }}',
            father_last_name: '{{ old('father_last_name', isset($student) ? $student->father_last_name : '') }}',
            father_mobile: '{{ old('father_mobile', isset($student) ? $student->father_mobile : '') }}',
            father_email: '{{ old('father_email', isset($student) ? $student->father_email : '') }}',
            
            // Mother Details
            mother_first_name: '{{ old('mother_first_name', isset($student) ? $student->mother_first_name : '') }}',
            mother_middle_name: '{{ old('mother_middle_name', isset($student) ? $student->mother_middle_name : '') }}',
            mother_last_name: '{{ old('mother_last_name', isset($student) ? $student->mother_last_name : '') }}',
            mother_mobile: '{{ old('mother_mobile', isset($student) ? $student->mother_mobile : '') }}',
            
            // Address
            permanent_address: '{{ old('permanent_address', isset($student) ? $student->permanent_address : '') }}',
            permanent_country_id: '{{ old('permanent_country_id', isset($student) ? $student->permanent_country_id : '') }}',
            permanent_state_id: '{{ old('permanent_state_id', isset($student) ? $student->permanent_state_id : '') }}',
            permanent_city_id: '{{ old('permanent_city_id', isset($student) ? $student->permanent_city_id : '') }}',
            permanent_pin: '{{ old('permanent_pin', isset($student) ? $student->permanent_pin : '') }}',
        },

        init() {
            if (this.formData.class_id) {
                this.loadClassData(this.formData.class_id);
            }

            this.$watch('formData.class_id', (value) => {
                if (value) this.loadClassData(value);
                else {
                    this.sections = [];
                    this.formData.admission_fee = '';
                }
            });
        },

        async fetchRegistrationData() {
            if (!this.registrationId) {
                return;
            }

            try {
                const response = await fetch(`/receptionist/admission/registration/${this.registrationId}`);
                if (!response.ok) throw new Error('Registration not found');
                const data = await response.json();
                
                // Map registration data to form data
                this.formData.registration_no = data.registration_no || '';
                this.formData.first_name = data.first_name || '';
                this.formData.middle_name = data.middle_name || '';
                this.formData.last_name = data.last_name || '';
                this.formData.gender = data.gender || '';
                this.formData.date_of_birth = data.dob || '';
                this.formData.email = data.email || '';
                this.formData.phone = data.mobile_no || '';
                this.formData.academic_year_id = data.academic_year_id || '';
                this.formData.class_id = data.class_id || '';
                this.formData.admission_date = data.registration_date || '';
                
                // Family
                this.formData.father_first_name = data.father_first_name || '';
                this.formData.father_middle_name = data.father_middle_name || '';
                this.formData.father_last_name = data.father_last_name || '';
                this.formData.father_mobile = data.father_mobile_no || '';
                this.formData.father_email = data.father_email || '';
                
                this.formData.mother_first_name = data.mother_first_name || '';
                this.formData.mother_middle_name = data.mother_middle_name || '';
                this.formData.mother_last_name = data.mother_last_name || '';
                this.formData.mother_mobile = data.mother_mobile_no || '';

                // Address
                this.formData.permanent_address = data.permanent_address || '';
                this.formData.permanent_country_id = data.permanent_country_id || '';
                this.formData.permanent_state_id = data.permanent_state_id || '';
                this.formData.permanent_city_id = data.permanent_city_id || '';
                this.formData.permanent_pin = data.permanent_pin || '';

                // Handle cascading location trigger
                this.$nextTick(() => {
                    const countrySelect = document.querySelector('select[name="permanent_country_id"]');
                    if (countrySelect && window.locationCascade) {
                         window.locationCascade.loadStates(
                            document.querySelector('select[name="permanent_state_id"]'),
                            this.formData.permanent_country_id,
                            this.formData.permanent_state_id
                         );
                         setTimeout(() => {
                             window.locationCascade.loadCities(
                                document.querySelector('select[name="permanent_city_id"]'),
                                this.formData.permanent_state_id,
                                this.formData.permanent_city_id
                             );
                         }, 500);
                    }
                });

                window.Toast?.fire({ icon: 'success', title: 'Data auto-filled from registration' });
            } catch (error) {
                console.error(error);
                window.Toast?.fire({ icon: 'error', title: 'Error fetching registration data' });
            }
        },

        async loadClassData(classId) {
            try {
                const response = await fetch(`{{ url('school/admission/class-data') }}/${classId}`);
                const data = await response.json();
                this.sections = data.sections || [];
                this.formData.admission_fee = data.admission_fee || '';
            } catch (error) {
                console.error('Error loading class data:', error);
            }
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};

            const form = document.getElementById('admissionForm');
            const fd = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (response.status === 422) {
                    this.errors = result.errors || {};
                    this.$nextTick(() => {
                        const firstError = document.querySelector('.border-red-500');
                        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                } else if (response.ok) {
                    window.Toast?.fire({ icon: 'success', title: result.message });
                    if (result.redirect) setTimeout(() => window.location.href = result.redirect, 1000);
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                window.Toast?.fire({ icon: 'error', title: error.message });
            } finally {
                this.submitting = false;
            }
        },

        clearError(field) {
            delete this.errors[field];
        }
    }));
});

</script>
@endpush
