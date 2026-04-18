@php
    use App\Enums\AdmissionStatus;
@endphp
@extends('layouts.school')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Student Registration</h1>
                <p class="text-gray-600 dark:text-gray-400">Update the details for registration:
                    {{ $studentRegistration->registration_no }}</p>
            </div>
            <a href="{{ route('school.student-registrations.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <div x-data="registrationManagement()">
            <form id="registrationForm" @submit.prevent="submitForm">
                @csrf
                @method('PUT')

                {{-- Admission Status (Only in Edit) --}}
                <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Admission Status <span class="text-red-500">*</span>
                    </label>
                    <select name="admission_status" x-model="formData.admission_status" @change="clearError('admission_status')"
                        class="no-select2 w-full md:w-1/3 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.admission_status ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        @foreach(AdmissionStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.admission_status">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.admission_status[0]"></p>
                    </template>
                </div>

                @include('school.student-registrations.partials.form')

                <!-- Submit Section -->
                <div class="mt-8 flex items-center justify-end gap-4 p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <a href="{{ route('school.student-registrations.index') }}" 
                       class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            :disabled="submitting"
                            class="px-10 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold shadow-lg shadow-teal-500/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!submitting">
                            <i class="fas fa-save"></i>
                        </template>
                        <template x-if="submitting">
                            <i class="fas fa-spinner fa-spin"></i>
                        </template>
                        <span x-text="submitting ? 'Updating Registration...' : 'Update Registration'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function registrationManagement() {
            return {
                submitting: false,
                errors: {},
                registrationFees: {
                    @foreach($classes as $class)
                        '{{ $class->id }}': '{{ $class->registrationFee->amount ?? 0 }}',
                    @endforeach
                },
                
                // Previews state
                previews: {
                    father_photo: '{{ $studentRegistration->father_photo ? "/storage/".$studentRegistration->father_photo : "" }}',
                    mother_photo: '{{ $studentRegistration->mother_photo ? "/storage/".$studentRegistration->mother_photo : "" }}',
                    student_photo: '{{ $studentRegistration->student_photo ? "/storage/".$studentRegistration->student_photo : "" }}',
                    father_signature: '{{ $studentRegistration->father_signature ? "/storage/".$studentRegistration->father_signature : "" }}',
                    mother_signature: '{{ $studentRegistration->mother_signature ? "/storage/".$studentRegistration->mother_signature : "" }}',
                    student_signature: '{{ $studentRegistration->student_signature ? "/storage/".$studentRegistration->student_signature : "" }}'
                },

                formData: {
                    admission_status: '{{ old('admission_status', $studentRegistration->admission_status->value ?? $studentRegistration->admission_status) }}',
                    enquiry_id: '{{ old('enquiry_id', $studentRegistration->enquiry_id) }}',
                    academic_year_id: '{{ old('academic_year_id', $studentRegistration->academic_year_id) }}',
                    class_id: '{{ old('class_id', $studentRegistration->class_id) }}',
                    registration_fee: '{{ old('registration_fee', $studentRegistration->registration_fee) }}',
                    
                    // Personal Information
                    first_name: '{{ old('first_name', $studentRegistration->first_name) }}',
                    middle_name: '{{ old('middle_name', $studentRegistration->middle_name) }}',
                    last_name: '{{ old('last_name', $studentRegistration->last_name) }}',
                    gender: '{{ old('gender', $studentRegistration->gender) }}',
                    dob: '{{ old('dob', $studentRegistration->dob ? \Carbon\Carbon::parse($studentRegistration->dob)->format('Y-m-d') : '') }}',
                    email: '{{ old('email', $studentRegistration->email) }}',
                    mobile_no: '{{ old('mobile_no', $studentRegistration->mobile_no) }}',
                    student_type: '{{ old('student_type', $studentRegistration->student_type) }}',
                    aadhar_no: '{{ old('aadhar_no', $studentRegistration->aadhar_no) }}',
                    place_of_birth: '{{ old('place_of_birth', $studentRegistration->place_of_birth) }}',
                    nationality: '{{ old('nationality', $studentRegistration->nationality ?? 'Indian') }}',
                    religion: '{{ old('religion', $studentRegistration->religion) }}',
                    category: '{{ old('category', $studentRegistration->category) }}',
                    special_needs: '{{ old('special_needs', $studentRegistration->special_needs) }}',
                    mother_tongue: '{{ old('mother_tongue', $studentRegistration->mother_tongue) }}',
                    remarks: '{{ old('remarks', $studentRegistration->remarks) }}',
                    number_of_brothers: '{{ old('number_of_brothers', $studentRegistration->number_of_brothers ?? 0) }}',
                    number_of_sisters: '{{ old('number_of_sisters', $studentRegistration->number_of_sisters ?? 0) }}',
                    is_single_parent: '{{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) }}',
                    corresponding_relative: '{{ old('corresponding_relative', $studentRegistration->corresponding_relative) }}',
                    is_transport_required: '{{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) }}',
                    bus_stop: '{{ old('bus_stop', $studentRegistration->bus_stop) }}',
                    other_stop: '{{ old('other_stop', $studentRegistration->other_stop) }}',
                    boarding_type: '{{ old('boarding_type', $studentRegistration->boarding_type) }}',

                    // Father Details
                    father_first_name: '{{ old('father_first_name', $studentRegistration->father_first_name) }}',
                    father_middle_name: '{{ old('father_middle_name', $studentRegistration->father_middle_name) }}',
                    father_last_name: '{{ old('father_last_name', $studentRegistration->father_last_name) }}',
                    father_mobile_no: '{{ old('father_mobile_no', $studentRegistration->father_mobile_no) }}',
                    father_occupation: '{{ old('father_occupation', $studentRegistration->father_occupation) }}',
                    father_email: '{{ old('father_email', $studentRegistration->father_email) }}',
                    father_qualification: '{{ old('father_qualification', $studentRegistration->father_qualification) }}',
                    father_organization: '{{ old('father_organization', $studentRegistration->father_organization) }}',
                    father_office_address: '{{ old('father_office_address', $studentRegistration->father_office_address) }}',
                    father_department: '{{ old('father_department', $studentRegistration->father_department) }}',
                    father_designation: '{{ old('father_designation', $studentRegistration->father_designation) }}',
                    father_annual_income: '{{ old('father_annual_income', $studentRegistration->father_annual_income) }}',

                    // Mother Details
                    mother_first_name: '{{ old('mother_first_name', $studentRegistration->mother_first_name) }}',
                    mother_middle_name: '{{ old('mother_middle_name', $studentRegistration->mother_middle_name) }}',
                    mother_last_name: '{{ old('mother_last_name', $studentRegistration->mother_last_name) }}',
                    mother_mobile_no: '{{ old('mother_mobile_no', $studentRegistration->mother_mobile_no) }}',
                    mother_occupation: '{{ old('mother_occupation', $studentRegistration->mother_occupation) }}',
                    mother_email: '{{ old('mother_email', $studentRegistration->mother_email) }}',
                    mother_qualification: '{{ old('mother_qualification', $studentRegistration->mother_qualification) }}',
                    mother_organization: '{{ old('mother_organization', $studentRegistration->mother_organization) }}',
                    mother_office_address: '{{ old('mother_office_address', $studentRegistration->mother_office_address) }}',
                    mother_department: '{{ old('mother_department', $studentRegistration->mother_department) }}',
                    mother_designation: '{{ old('mother_designation', $studentRegistration->mother_designation) }}',
                    mother_annual_income: '{{ old('mother_annual_income', $studentRegistration->mother_annual_income) }}',

                    // Address
                    permanent_address: '{{ old('permanent_address', $studentRegistration->permanent_address) }}',
                    permanent_country_id: '{{ old('permanent_country_id', $studentRegistration->permanent_country_id ?? 102) }}',
                    permanent_state_id: '{{ old('permanent_state_id', $studentRegistration->permanent_state_id) }}',
                    permanent_city_id: '{{ old('permanent_city_id', $studentRegistration->permanent_city_id) }}',
                    permanent_pin: '{{ old('permanent_pin', $studentRegistration->permanent_pin) }}',
                    permanent_state_of_domicile: '{{ old('permanent_state_of_domicile', $studentRegistration->permanent_state_of_domicile) }}',
                    permanent_railway_airport: '{{ old('permanent_railway_airport', $studentRegistration->permanent_railway_airport) }}',
                    
                    correspondence_address: '{{ old('correspondence_address', $studentRegistration->correspondence_address) }}',
                    correspondence_country_id: '{{ old('correspondence_country_id', $studentRegistration->correspondence_country_id ?? 102) }}',
                    correspondence_state_id: '{{ old('correspondence_state_id', $studentRegistration->correspondence_state_id) }}',
                    correspondence_city_id: '{{ old('correspondence_city_id', $studentRegistration->correspondence_city_id) }}',
                    correspondence_pin: '{{ old('correspondence_pin', $studentRegistration->correspondence_pin) }}',
                    correspondence_location: '{{ old('correspondence_location', $studentRegistration->correspondence_location) }}',
                    distance_from_school: '{{ old('distance_from_school', $studentRegistration->distance_from_school) }}',
                    correspondence_landmark: '{{ old('correspondence_landmark', $studentRegistration->correspondence_landmark) }}',

                    // Hidden/Session fields for photos
                    enquiry_father_photo: '',
                    enquiry_mother_photo: '',
                    enquiry_student_photo: '',
                    enquiry_father_signature: '',
                    enquiry_mother_signature: '',
                    enquiry_student_signature: ''
                },

                updateFee() {
                    const classId = this.formData.class_id;
                    if (classId && this.registrationFees[classId]) {
                        this.formData.registration_fee = this.registrationFees[classId];
                    }
                },

                clearError(field) {
                    if (this.errors[field]) {
                        delete this.errors[field];
                    }
                },

                handlePhotoUpload(event, field) {
                    const file = event.target.files[0];
                    if (file) {
                        this.previews[field] = URL.createObjectURL(file);
                        this.clearError(field);
                    }
                },

                removePhoto(field) {
                    this.previews[field] = '';
                    const input = document.querySelector(`input[name="${field}"]`);
                    if (input) input.value = '';
                    this.formData[`enquiry_${field}`] = '';
                },

                async submitForm() {
                    this.submitting = true;
                    this.errors = {};

                    const form = document.getElementById('registrationForm');
                    const fd = new FormData(form);

                    try {
                        const response = await fetch("{{ route('school.student-registrations.update', $studentRegistration->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: fd
                        });

                        const result = await response.json();

                        if (response.status === 422) {
                            this.errors = result.errors;
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: 'Please check the form for errors' });
                            }
                            this.$nextTick(() => {
                                const firstError = document.querySelector('.border-red-500, .bg-red-50');
                                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            });
                        } else if (response.ok) {
                            if (window.Toast) {
                                await window.Toast.fire({
                                    icon: 'success',
                                    title: result.message || 'Registration updated successfully'
                                });
                            }
                            window.location.href = "{{ route('school.student-registrations.index') }}";
                        } else {
                            throw new Error(result.message || 'Something went wrong');
                        }
                    } catch (error) {
                        console.error('Update Error:', error);
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'error',
                                title: error.message || 'Failed to update registration'
                            });
                        }
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection