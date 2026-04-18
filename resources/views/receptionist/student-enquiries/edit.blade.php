@extends('layouts.receptionist')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Student Enquiry</h1>
                <p class="text-gray-600 dark:text-gray-400">Update the details of the enquiry for: <span class="text-teal-600 font-bold font-serif italic uppercase underline underline-offset-4 decoration-2 decoration-teal-600/30 whitespace-nowrap">{{ $studentEnquiry->student_name }}</span></p>
            </div>
            <a href="{{ route('receptionist.student-enquiries.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <div x-data="enquiryManagement()">
            <form id="enquiryForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @include('receptionist.student-enquiries.partials.form')

                {{-- Action Buttons --}}
                <div class="mt-8 flex items-center justify-end gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <a href="{{ route('receptionist.student-enquiries.index') }}" 
                       class="px-6 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-10 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-teal-200 dark:shadow-none transition-all flex items-center gap-2"
                            :class="submitting ? 'opacity-75 cursor-wait' : ''"
                            :disabled="submitting">
                        <template x-if="!submitting">
                            <i class="fas fa-save"></i>
                        </template>
                        <template x-if="submitting">
                            <i class="fas fa-circle-notch animate-spin"></i>
                        </template>
                        <span x-text="submitting ? 'Updating Enquiry...' : 'Save Changes'">Save Changes</span>
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
                    academic_year_id: '{{ $studentEnquiry->academic_year_id }}',
                    class_id: '{{ $studentEnquiry->class_id }}',
                    subject_name: '{{ $studentEnquiry->subject_name }}',
                    student_name: '{{ $studentEnquiry->student_name }}',
                    gender: '{{ $studentEnquiry->gender }}',
                    follow_up_date: '{{ $studentEnquiry->follow_up_date ? $studentEnquiry->follow_up_date->format('Y-m-d') : '' }}',
                    
                    // Father Details
                    father_name: '{{ $studentEnquiry->father_name }}',
                    father_contact: '{{ $studentEnquiry->father_contact }}',
                    father_email: '{{ $studentEnquiry->father_email }}',
                    father_qualification: '{{ $studentEnquiry->father_qualification }}',
                    father_occupation: '{{ $studentEnquiry->father_occupation }}',
                    father_annual_income: '{{ $studentEnquiry->father_annual_income }}',
                    father_organization: '{{ $studentEnquiry->father_organization }}',
                    father_office_address: '{{ $studentEnquiry->father_office_address }}',
                    father_department: '{{ $studentEnquiry->father_department }}',
                    father_designation: '{{ $studentEnquiry->father_designation }}',
                    
                    // Mother Details
                    mother_name: '{{ $studentEnquiry->mother_name }}',
                    mother_contact: '{{ $studentEnquiry->mother_contact }}',
                    mother_email: '{{ $studentEnquiry->mother_email }}',
                    mother_qualification: '{{ $studentEnquiry->mother_qualification }}',
                    mother_occupation: '{{ $studentEnquiry->mother_occupation }}',
                    mother_annual_income: '{{ $studentEnquiry->mother_annual_income }}',
                    mother_organization: '{{ $studentEnquiry->mother_organization }}',
                    mother_office_address: '{{ $studentEnquiry->mother_office_address }}',
                    mother_department: '{{ $studentEnquiry->mother_department }}',
                    mother_designation: '{{ $studentEnquiry->mother_designation }}',
                    
                    // Contact Details
                    contact_no: '{{ $studentEnquiry->contact_no }}',
                    whatsapp_no: '{{ $studentEnquiry->whatsapp_no }}',
                    facebook_id: '{{ $studentEnquiry->facebook_id }}',
                    email_id: '{{ $studentEnquiry->email_id }}',
                    sms_no: '{{ $studentEnquiry->sms_no }}',
                    twitter_id: '{{ $studentEnquiry->twitter_id }}',
                    emergency_contact_no: '{{ $studentEnquiry->emergency_contact_no }}',
                    
                    // Personal details
                    dob: '{{ $studentEnquiry->dob ? $studentEnquiry->dob->format('Y-m-d') : '' }}',
                    aadhar_no: '{{ $studentEnquiry->aadhar_no }}',
                    grand_father_name: '{{ $studentEnquiry->grand_father_name }}',
                    annual_income: '{{ $studentEnquiry->annual_income }}',
                    no_of_brothers: '{{ $studentEnquiry->no_of_brothers }}',
                    no_of_sisters: '{{ $studentEnquiry->no_of_sisters }}',
                    category: '{{ $studentEnquiry->category }}',
                    minority: '{{ $studentEnquiry->minority }}',
                    religion: '{{ $studentEnquiry->religion }}',
                    transport_facility: '{{ $studentEnquiry->transport_facility }}',
                    hostel_facility: '{{ $studentEnquiry->hostel_facility }}',
                    previous_class: '{{ $studentEnquiry->previous_class }}',
                    identity_marks: '{{ $studentEnquiry->identity_marks }}',
                    permanent_address: '{{ $studentEnquiry->permanent_address }}',
                    country_id: '{{ $studentEnquiry->country_id }}',
                    previous_school_name: '{{ $studentEnquiry->previous_school_name }}',
                    student_roll_no: '{{ $studentEnquiry->student_roll_no }}',
                    passing_year: '{{ $studentEnquiry->passing_year }}',
                    exam_name: '{{ $studentEnquiry->exam_name }}',
                    board_university: '{{ $studentEnquiry->board_university }}',
                    only_child: {{ $studentEnquiry->only_child ? 'true' : 'false' }}
                },

                async submitForm() {
                    this.submitting = true;
                    this.errors = {};

                    const form = document.getElementById('enquiryForm');
                    const fd = new FormData(form);

                    try {
                        const response = await fetch("{{ route('receptionist.student-enquiries.update', $studentEnquiry->id) }}", {
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
                                    title: result.message || 'Enquiry updated successfully'
                                });
                            }
                            if (result.redirect || "{{ route('receptionist.student-enquiries.index') }}") {
                                window.location.href = result.redirect || "{{ route('receptionist.student-enquiries.index') }}";
                            }
                        } else {
                            throw new Error(result.message || 'Something went wrong');
                        }
                    } catch (error) {
                        console.error("Enquiry Update Error:", error);
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: "error",
                                title: error.message || "Failed to update enquiry"
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
