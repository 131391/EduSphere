@extends('layouts.school')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">New Student Enquiry</h1>
                <p class="text-gray-600 dark:text-gray-400">Fill in the details to record a new student enquiry</p>
            </div>
            <a href="{{ route('school.student-enquiries.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <div x-data="enquiryManagement()">
            <form id="enquiryForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
                @csrf
                
                @include('school.student-enquiries.partials.form')

                {{-- Action Buttons --}}
                <div class="mt-8 flex items-center justify-end gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <a href="{{ route('school.student-enquiries.index') }}" 
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
                        <span x-text="submitting ? 'Processing Enquiry...' : 'Record Enquiry'">Record Enquiry</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function enquiryManagement() {
            return {
                submitting: false,
                errors: {},
                fatherExpanded: false,
                motherExpanded: false,
                contactExpanded: false,
                
                formData: {
                    academic_year_id: '{{ old('academic_year_id') }}',
                    class_id: '{{ old('class_id') }}',
                    subject_name: '{{ old('subject_name') }}',
                    student_name: '{{ old('student_name') }}',
                    gender: '{{ old('gender') }}',
                    follow_up_date: '{{ old('follow_up_date', date('Y-m-d')) }}',
                    
                    // Father Details
                    father_name: '{{ old('father_name') }}',
                    father_contact: '{{ old('father_contact') }}',
                    father_email: '{{ old('father_email') }}',
                    father_qualification: '{{ old('father_qualification') }}',
                    father_occupation: '{{ old('father_occupation') }}',
                    father_annual_income: '{{ old('father_annual_income') }}',
                    father_organization: '{{ old('father_organization') }}',
                    father_office_address: '{{ old('father_office_address') }}',
                    father_department: '{{ old('father_department') }}',
                    father_designation: '{{ old('father_designation') }}',
                    
                    // Mother Details
                    mother_name: '{{ old('mother_name') }}',
                    mother_contact: '{{ old('mother_contact') }}',
                    mother_email: '{{ old('mother_email') }}',
                    mother_qualification: '{{ old('mother_qualification') }}',
                    mother_occupation: '{{ old('mother_occupation') }}',
                    mother_annual_income: '{{ old('mother_annual_income') }}',
                    mother_organization: '{{ old('mother_organization') }}',
                    mother_office_address: '{{ old('mother_office_address') }}',
                    mother_department: '{{ old('mother_department') }}',
                    mother_designation: '{{ old('mother_designation') }}',
                    
                    // Contact Details
                    contact_no: '{{ old('contact_no') }}',
                    whatsapp_no: '{{ old('whatsapp_no') }}',
                    facebook_id: '{{ old('facebook_id') }}',
                    email_id: '{{ old('email_id') }}',
                    sms_no: '{{ old('sms_no') }}',
                    twitter_id: '{{ old('twitter_id') }}',
                    emergency_contact_no: '{{ old('emergency_contact_no') }}',
                    
                    // Personal details
                    dob: '{{ old('dob') }}',
                    aadhar_no: '{{ old('aadhar_no') }}',
                    grand_father_name: '{{ old('grand_father_name') }}',
                    annual_income: '{{ old('annual_income') }}',
                    no_of_brothers: '{{ old('no_of_brothers', 0) }}',
                    no_of_sisters: '{{ old('no_of_sisters', 0) }}',
                    category: '{{ old('category') }}',
                    minority: '{{ old('minority') }}',
                    religion: '{{ old('religion') }}',
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
                    only_child: '{{ old('only_child') ? true : false }}'
                },

                async submitForm() {
                    this.submitting = true;
                    this.errors = {};

                    const form = document.getElementById('enquiryForm');
                    const fd = new FormData(form);

                    try {
                        const response = await fetch("{{ route('school.student-enquiries.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                            if (window.Toast) {
                                await window.Toast.fire({
                                    icon: 'success',
                                    title: result.message || 'Enquiry recorded successfully'
                                });
                            }
                            if (result.redirect) {
                                window.location.href = result.redirect;
                            }
                        } else {
                            throw new Error(result.message || 'Something went wrong');
                        }
                    } catch (error) {
                        console.error("Enquiry Creation Error:", error);
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: "error",
                                title: error.message || "Failed to process enquiry"
                            });
                        }
                    } finally {
                        this.submitting = false;
                    }
                },

                handleValidationErrors(errors) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: "error", title: "Please check the form for errors" });
                    }

                    // Auto-expand sections with errors
                    Object.keys(errors).forEach(field => {
                        if (field.startsWith("father_")) this.fatherExpanded = true;
                        if (field.startsWith("mother_")) this.motherExpanded = true;
                        if (["contact_no", "whatsapp_no", "email_id", "sms_no", "facebook_id", "twitter_id", "emergency_contact_no"].includes(field)) {
                            this.contactExpanded = true;
                        }
                    });

                    this.$nextTick(() => {
                        const firstError = document.querySelector(".border-red-500");
                        if (firstError) firstError.scrollIntoView({ behavior: "smooth", block: "center" });
                    });
                },

                clearError(field) {
                    if (this.errors[field]) {
                        delete this.errors[field];
                    }
                },

                previewPhoto(event, previewId, iconId, removeBtnId) {
                    const input = event.target;
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const preview = document.getElementById(previewId);
                            const icon = document.getElementById(iconId);
                            const removeBtn = document.getElementById(removeBtnId);
                            if (preview) {
                                preview.src = e.target.result;
                                preview.classList.remove('hidden');
                            }
                            if (icon) icon.classList.add('hidden');
                            if (removeBtn) removeBtn.classList.remove('hidden');
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                },

                removePhoto(inputName, previewId, iconId, removeBtnId) {
                    const input = document.querySelector(`input[name="${inputName}"]`);
                    if (input) input.value = '';

                    const preview = document.getElementById(previewId);
                    const icon = document.getElementById(iconId);
                    const removeBtn = document.getElementById(removeBtnId);
                    if (preview) {
                        preview.src = '#';
                        preview.classList.add('hidden');
                    }
                    if (icon) icon.classList.remove('hidden');
                    if (removeBtn) removeBtn.classList.add('hidden');
                }
            }
        }
    </script>
@endpush
