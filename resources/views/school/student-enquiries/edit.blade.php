@extends('layouts.school')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Student Enquiry</h1>
                <p class="text-gray-600 dark:text-gray-400">Update the details of the enquiry for: <span class="text-teal-600 font-bold font-serif italic uppercase underline underline-offset-4 decoration-2 decoration-teal-600/30 whitespace-nowrap">{{ $studentEnquiry->student_name }}</span></p>
            </div>
            <a href="{{ route('school.student-enquiries.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <div x-data="editEnquiryManagement()">
            <form id="enquiryForm" @submit.prevent="submitForm()" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
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
                        <i class="fas fa-save" x-show="!submitting"></i>
                        <i class="fas fa-circle-notch animate-spin" x-show="submitting" x-cloak></i>
                        
                        <span x-show="!submitting">Save Changes</span>
                        <span x-show="submitting" x-cloak>Updating Enquiry...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editEnquiryManagement() {
            return {
                submitting: false,
                errors: {},
                fatherExpanded: true,
                motherExpanded: true,
                contactExpanded: true,

                init() {
                    this.$nextTick(() => {
                        if (typeof $ !== 'undefined') {
                            $(this.$el).find('select').on('change', (e) => {
                                const fieldName = e.target.getAttribute('name');
                                if (fieldName && this.errors[fieldName]) {
                                    delete this.errors[fieldName];
                                }
                            });
                        }
                    });
                },

                async submitForm() {
                    this.submitting = true;
                    this.errors = {};

                    const form = document.getElementById('enquiryForm');
                    const formData = new FormData(form);

                    try {
                        const response = await fetch("{{ route('school.student-enquiries.update', $studentEnquiry->id) }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest"
                            },
                            body: formData
                        });

                        if (response.ok) {
                            if (window.Toast) {
                                window.Toast.fire({
                                    icon: "success",
                                    title: "Enquiry updated successfully"
                                });
                            }
                            setTimeout(() => {
                                window.location.href = "{{ route('school.student-enquiries.index') }}";
                            }, 1500);
                        } else {
                            const result = await response.json();
                            if (result.errors) {
                                this.displayErrors(result.errors);
                            } else {
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: "error",
                                        title: "Error!",
                                        text: result.message || "Failed to update enquiry"
                                    });
                                }
                            }
                        }
                    } catch (error) {
                        console.error("Enquiry Update Error:", error);
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: "error",
                                title: "Failed to update enquiry"
                            });
                        }
                    } finally {
                        this.submitting = false;
                    }
                },

                displayErrors(errors) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: "error", title: "Please check the form for errors" });
                    }

                    // Auto-expand sections with errors
                    Object.keys(errors).forEach(field => {
                        if (field.startsWith("father_")) this.fatherExpanded = true;
                        if (field.startsWith("mother_")) this.motherExpanded = true;
                        if (["contact_no", "whatsapp_no", "facebook_id", "email_id", "sms_no", "twitter_id", "emergency_contact_no"].includes(field)) {
                            this.contactExpanded = true;
                        }
                    });

                    this.$nextTick(() => {
                        const firstError = document.querySelector(".border-red-500, .bg-red-50");
                        if (firstError) firstError.scrollIntoView({ behavior: "smooth", block: "center" });
                    });
                }
            }
        }

        // Global preview handlers (matching create)
        function previewImage(event, previewId, iconId, removeBtnId) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    const icon = document.getElementById(iconId);
                    const removeBtn = document.getElementById(removeBtnId);
                    if (preview) { preview.src = e.target.result; preview.classList.remove('hidden'); }
                    if (icon) icon.classList.add('hidden');
                    if (removeBtn) removeBtn.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage(event, inputName, previewId, iconId, removeBtnId) {
            event.preventDefault();
            const input = document.querySelector(`input[name="${inputName}"]`);
            if (input) input.value = '';
            
            const preview = document.getElementById(previewId);
            const icon = document.getElementById(iconId);
            const removeBtn = document.getElementById(removeBtnId);
            if (preview) { preview.src = '#'; preview.classList.add('hidden'); }
            if (icon) icon.classList.remove('hidden');
            if (removeBtn) removeBtn.classList.add('hidden');
        }
    </script>
@endpush
