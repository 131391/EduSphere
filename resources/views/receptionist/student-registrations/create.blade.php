@extends('layouts.receptionist')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Student Registration</h1>
                <p class="text-gray-600 dark:text-gray-400">Fill in the details to register a new student</p>
            </div>
            <a href="{{ route('receptionist.student-registrations.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <div x-data="registrationManagement()">
            <form id="registrationForm" @submit.prevent="submitForm">
                @csrf
                

                @include('receptionist.student-registrations.partials.form')

                {{-- Action Buttons --}}
                <div class="mt-8 flex items-center justify-end gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <a href="{{ route('receptionist.student-registrations.index') }}" 
                       class="px-6 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-10 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-teal-200 dark:shadow-none transition-all flex items-center gap-2"
                            :class="submitting ? 'opacity-75 cursor-wait' : ''"
                            :disabled="submitting">
                        <template x-if="!submitting">
                            <i class="fas fa-check-circle"></i>
                        </template>
                        <template x-if="submitting">
                            <i class="fas fa-circle-notch animate-spin"></i>
                        </template>
                        <span x-text="submitting ? 'Processing Registration...' : 'Complete Registration'"></span>
                    </button>
                </div>
            </form>
        </div>

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Find the first field with a validation error and scroll to it
                    var firstError = document.querySelector('.text-red-500.text-xs.mt-1');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            </script>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function registrationManagement() {
            return {
                submitting: false,
                errors: {},
                selectedClassId: '{{ old('class_id') }}',
                registrationFees: {
                    @foreach($classes as $class)
                        '{{ $class->id }}': '{{ $class->registrationFee->amount ?? 0 }}',
                    @endforeach
                },
                
                formData: {
                    enquiry_id: '{{ old('enquiry_id') }}',
                    academic_year_id: '{{ old('academic_year_id') }}',
                    class_id: '{{ old('class_id') }}',
                    registration_fee: '{{ old('registration_fee') }}',
                    
                    // Personal Information
                    first_name: '{{ old('first_name') }}',
                    middle_name: '{{ old('middle_name') }}',
                    last_name: '{{ old('last_name') }}',
                    gender: '{{ old('gender') }}',
                    dob: '{{ old('dob') }}',
                    email: '{{ old('email') }}',
                    mobile_no: '{{ old('mobile_no') }}',
                    student_type: '{{ old('student_type') }}',
                    aadhar_no: '{{ old('aadhar_no') }}',
                    place_of_birth: '{{ old('place_of_birth') }}',
                    nationality: '{{ old('nationality', 'Indian') }}',
                    religion: '{{ old('religion') }}',
                    category: '{{ old('category') }}',
                    special_needs: '{{ old('special_needs') }}',
                    mother_tongue: '{{ old('mother_tongue') }}',
                    remarks: '{{ old('remarks') }}',
                    number_of_brothers: '{{ old('number_of_brothers', 0) }}',
                    number_of_sisters: '{{ old('number_of_sisters', 0) }}',
                    is_single_parent: '{{ old('is_single_parent', 0) }}',
                    corresponding_relative: '{{ old('corresponding_relative') }}',
                    is_transport_required: '{{ old('is_transport_required', 0) }}',
                    bus_stop: '{{ old('bus_stop') }}',
                    other_stop: '{{ old('other_stop') }}',
                    boarding_type: '{{ old('boarding_type') }}',

                    // Father Details
                    father_first_name: '{{ old('father_first_name') }}',
                    father_middle_name: '{{ old('father_middle_name') }}',
                    father_last_name: '{{ old('father_last_name') }}',
                    father_mobile_no: '{{ old('father_mobile_no') }}',
                    father_occupation: '{{ old('father_occupation') }}',
                    father_email: '{{ old('father_email') }}',
                    father_qualification: '{{ old('father_qualification') }}',
                    father_organization: '{{ old('father_organization') }}',
                    father_office_address: '{{ old('father_office_address') }}',
                    father_department: '{{ old('father_department') }}',
                    father_designation: '{{ old('father_designation') }}',
                    father_annual_income: '{{ old('father_annual_income') }}',

                    // Mother Details
                    mother_first_name: '{{ old('mother_first_name') }}',
                    mother_middle_name: '{{ old('mother_middle_name') }}',
                    mother_last_name: '{{ old('mother_last_name') }}',
                    mother_mobile_no: '{{ old('mother_mobile_no') }}',
                    mother_occupation: '{{ old('mother_occupation') }}',
                    mother_email: '{{ old('mother_email') }}',
                    mother_qualification: '{{ old('mother_qualification') }}',
                    mother_organization: '{{ old('mother_organization') }}',
                    mother_office_address: '{{ old('mother_office_address') }}',
                    mother_department: '{{ old('mother_department') }}',
                    mother_designation: '{{ old('mother_designation') }}',
                    mother_annual_income: '{{ old('mother_annual_income') }}',

                    // Address
                    permanent_latitude: '{{ old('permanent_latitude') }}',
                    permanent_longitude: '{{ old('permanent_longitude') }}',
                    permanent_address: '{{ old('permanent_address') }}',
                    permanent_country_id: '{{ old('permanent_country_id', 102) }}',
                    permanent_state_id: '{{ old('permanent_state_id') }}',
                    permanent_city_id: '{{ old('permanent_city_id') }}',
                    permanent_pin: '{{ old('permanent_pin') }}',
                    permanent_state_of_domicile: '{{ old('permanent_state_of_domicile') }}',
                    permanent_railway_airport: '{{ old('permanent_railway_airport') }}',
                    permanent_correspondence_address: '{{ old('permanent_correspondence_address') }}',
                    
                    correspondence_latitude: '{{ old('correspondence_latitude') }}',
                    correspondence_longitude: '{{ old('correspondence_longitude') }}',
                    correspondence_address: '{{ old('correspondence_address') }}',
                    correspondence_country_id: '{{ old('correspondence_country_id', 102) }}',
                    correspondence_state_id: '{{ old('correspondence_state_id') }}',
                    correspondence_city_id: '{{ old('correspondence_city_id') }}',
                    correspondence_pin: '{{ old('correspondence_pin') }}',

                    // Hidden/Session fields for photos carry-over from enquiry
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
                    } else {
                        this.formData.registration_fee = '';
                    }
                },

                async fetchEnquiryData() {
                    const enquiryId = this.formData.enquiry_id;
                    if (!enquiryId) {
                        this.clearPhotos();
                        return;
                    }

                    try {
                        const response = await fetch(`/school/student-registrations/enquiry/${enquiryId}`);
                        const result = await response.json();

                        if (result.success) {
                            const enquiry = result.data;
                            
                            // Map enquiry fields to formData
                            if (enquiry.academic_year_id) this.formData.academic_year_id = enquiry.academic_year_id;
                            if (enquiry.class_id) {
                                this.formData.class_id = enquiry.class_id;
                                this.updateFee();
                            }

                            // Personal Name
                            const nameParts = (enquiry.student_name || '').split(' ');
                            this.formData.first_name = nameParts[0] || '';
                            this.formData.middle_name = nameParts.slice(1, -1).join(' ') || '';
                            this.formData.last_name = nameParts[nameParts.length - 1] || '';
                            
                            if (enquiry.gender) this.formData.gender = enquiry.gender;
                            
                            if (enquiry.dob) {
                                let dobValue = enquiry.dob;
                                if (typeof dobValue === 'object' && dobValue.date) {
                                    dobValue = dobValue.date.split(' ')[0];
                                }
                                if (dobValue && dobValue.length >= 10) {
                                    this.formData.dob = dobValue.substring(0, 10);
                                }
                            }

                            this.formData.email = enquiry.email_id || '';
                            this.formData.mobile_no = enquiry.contact_no || '';
                            if (enquiry.religion) this.formData.religion = enquiry.religion;
                            if (enquiry.category) this.formData.category = enquiry.category;

                            // Father
                            const fNameParts = (enquiry.father_name || '').split(' ');
                            this.formData.father_first_name = fNameParts[0] || '';
                            this.formData.father_middle_name = fNameParts.slice(1, -1).join(' ') || '';
                            this.formData.father_last_name = fNameParts[fNameParts.length - 1] || '';
                            this.formData.father_mobile_no = enquiry.father_contact || '';
                            this.formData.father_occupation = enquiry.father_occupation || '';
                            this.formData.father_email = enquiry.father_email || '';

                            // Mother
                            const mNameParts = (enquiry.mother_name || '').split(' ');
                            this.formData.mother_first_name = mNameParts[0] || '';
                            this.formData.mother_middle_name = mNameParts.slice(1, -1).join(' ') || '';
                            this.formData.mother_last_name = mNameParts[mNameParts.length - 1] || '';
                            this.formData.mother_mobile_no = enquiry.mother_contact || '';
                            this.formData.mother_occupation = enquiry.mother_occupation || '';
                            this.formData.mother_email = enquiry.mother_email || '';

                            // Address
                            this.formData.permanent_address = enquiry.permanent_address || '';
                            if (enquiry.country_id) this.formData.permanent_country_id = enquiry.country_id;

                            // Photos from Enquiry
                            this.loadEnquiryPhotos(enquiry);
                            
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'success', title: 'Form auto-filled from enquiry' });
                            }
                        }
                    } catch (error) {
                        console.error('Error fetching enquiry:', error);
                    }
                },

                loadEnquiryPhotos(enquiry) {
                    const mappings = [
                        { field: 'father_photo', previewId: 'father-photo-preview', iconId: 'father-photo-icon', removeId: 'father-photo-remove' },
                        { field: 'mother_photo', previewId: 'mother-photo-preview', iconId: 'mother-photo-icon', removeId: 'mother-photo-remove' },
                        { field: 'student_photo', previewId: 'student-photo-preview', iconId: 'student-photo-icon', removeId: 'student-photo-remove' },
                        { field: 'father_signature', previewId: 'father-signature-preview', iconId: 'father-signature-icon', removeId: 'father-signature-remove' },
                        { field: 'mother_signature', previewId: 'mother-signature-preview', iconId: 'mother-signature-icon', removeId: 'mother-signature-remove' },
                        { field: 'student_signature', previewId: 'student-signature-preview', iconId: 'student-signature-icon', removeId: 'student-signature-remove' }
                    ];

                    mappings.forEach(m => {
                        if (enquiry[m.field]) {
                            this.formData[`enquiry_${m.field}`] = enquiry[m.field];
                            this.setPreview(m.previewId, m.iconId, m.removeId, `/storage/${enquiry[m.field]}`);
                        }
                    });
                },

                clearPhotos() {
                    const mappings = ['father-photo', 'mother-photo', 'student-photo', 'father-signature', 'mother-signature', 'student-signature'];
                    mappings.forEach(m => {
                        this.setPreview(`${m}-preview`, `${m}-icon`, `${m}-remove`, '#', true);
                        this.formData[`enquiry_${m.replace('-', '_')}`] = '';
                    });
                },

                setPreview(previewId, iconId, removeId, src, hide = false) {
                    const preview = document.getElementById(previewId);
                    const icon = document.getElementById(iconId);
                    const removeBtn = document.getElementById(removeId);
                    if (preview) {
                        preview.src = src;
                        hide ? preview.classList.add('hidden') : preview.classList.remove('hidden');
                    }
                    if (icon) hide ? icon.classList.remove('hidden') : icon.classList.add('hidden');
                    if (removeBtn) hide ? removeBtn.classList.add('hidden') : removeBtn.classList.remove('hidden');
                },

                previewPhoto(event, previewId, iconId, removeId) {
                    const input = event.target;
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.setPreview(previewId, iconId, removeId, e.target.result);
                        reader.readAsDataURL(input.files[0]);
                    }
                },

                removePhoto(inputName, previewId, iconId, removeId) {
                    const input = document.querySelector(`input[name="${inputName}"]`);
                    if (input) input.value = '';
                    this.setPreview(previewId, iconId, removeId, '#', true);
                    this.formData[`enquiry_${inputName}`] = '';
                },

                async submitForm() {
                    this.submitting = true;
                    this.errors = {};

                    const form = document.getElementById('registrationForm');
                    const fd = new FormData(form);

                    try {
                        const response = await fetch("{{ route('receptionist.student-registrations.store') }}", {
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
                            this.handleValidationErrors(result.errors);
                        } else if (response.ok) {
                            if (window.Toast) {
                                await window.Toast.fire({
                                    icon: 'success',
                                    title: result.message || 'Registration completed successfully'
                                });
                            }
                            if (result.redirect) {
                                window.location.href = result.redirect;
                            }
                        } else {
                            throw new Error(result.message || 'Something went wrong');
                        }
                    } catch (error) {
                        console.error('Registration Error:', error);
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'error',
                                title: error.message || 'Failed to process registration'
                            });
                        }
                    } finally {
                        this.submitting = false;
                    }
                },

                handleValidationErrors(errors) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'error', title: 'Please check the form for errors' });
                    }
                    this.$nextTick(() => {
                        const firstError = document.querySelector('.border-red-500, .bg-red-50');
                        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                }
            }
        }

@endpush
    </script>
@endpush