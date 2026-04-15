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
        
        async submitForm() {
            this.submitting = true;
            this.clearErrors();

            const form = document.getElementById('admissionForm');
            
            // Trigger the JQuery fix for disabled selects before creating FormData
            $(form).find('select[disabled]').removeAttr('disabled');
            
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (response.status === 422) {
                    this.displayErrors(result.errors);
                } else if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message || 'Admission completed successfully'
                        });
                    }
                    if (result.redirect) {
                        setTimeout(() => window.location.href = result.redirect, 1000);
                    }
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                console.error('Submission error:', error);
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message || 'Could not complete admission'
                    });
                }
            } finally {
                this.submitting = false;
            }
        },

        displayErrors(errors) {
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('border-red-500');
                    if ($(input).hasClass('select2-hidden-accessible')) {
                        $(input).next('.select2-container').find('.select2-selection').addClass('border-red-500');
                    }

                    // Find or create error message element
                    let errorMsg = input.closest('div').querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('p');
                        errorMsg.className = 'error-message text-red-500 text-xs mt-1';
                        input.closest('div').appendChild(errorMsg);
                    }
                    errorMsg.innerText = errors[field][0];
                }
            });
            
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        clearErrors() {
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            document.querySelectorAll('.error-message').forEach(el => el.remove());
        }
    }));
});

// Helper function to load image preview from storage path
function loadImagePreview(imagePath, previewId, iconId, removeBtnId) {
    if (!imagePath) return;
    
    const preview = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    const removeBtn = document.getElementById(removeBtnId);
    
    if (preview) {
        // Construct the full URL to the image
        const imageUrl = `/storage/${imagePath}`;
        preview.src = imageUrl;
        preview.classList.remove('hidden');
    }
    if (icon) {
        icon.classList.add('hidden');
    }
    if (removeBtn) {
        removeBtn.classList.remove('hidden');
    }
}

$(document).ready(function() {
    // Initialize Select2 for registration dropdown (only if not already initialized)
    const $registrationSelect = $('#registration_select');
    if ($registrationSelect.length && !$registrationSelect.hasClass('select2-hidden-accessible')) {
        $registrationSelect.select2({
            placeholder: 'Select a registration',
            allowClear: false,
            width: '100%'
        });
    }
    
    // Auto-fill form when registration is selected
    $('#registration_select').on('change', function() {
        const registrationId = $(this).val();
        const registrationNo = $(this).find(':selected').data('reg-no');
        
        if (!registrationId) {
            $('#registration_no_hidden').val('');
            return;
        }
        
        // Set hidden registration_no field
        $('#registration_no_hidden').val(registrationNo);
        
        // Fetch registration data
        $.ajax({
            url: `/receptionist/admission/registration/${registrationId}`,
            method: 'GET',
            success: function(data) {
                // Helper function to clear errors for a field ONLY if it has a value
                function clearFieldError(fieldName) {
                    const field = $(`[name="${fieldName}"]`);
                    // Only clear errors if the field has a value
                    if (field.length && field.val()) {
                        // Remove red border
                        field.removeClass('border-red-500');
                        // Remove error message (only <p> tags with text-red-500, not asterisks in labels)
                        field.closest('div').find('p.text-red-500').remove();
                        // For Select2 fields, also remove error styling from container
                        if (field.hasClass('select2-hidden-accessible')) {
                            field.next('.select2-container').find('.select2-selection').removeClass('border-red-500');
                        }
                    }
                }
                
                // Personal Information
                if (data.first_name) {
                    $('input[name="first_name"]').val(data.first_name);
                    clearFieldError('first_name');
                }
                $('input[name="middle_name"]').val(data.middle_name || '');
                if (data.last_name) {
                    $('input[name="last_name"]').val(data.last_name);
                    clearFieldError('last_name');
                }
                if (data.gender) {
                    $('select[name="gender"]').val(data.gender).trigger('change');
                    clearFieldError('gender');
                }
                $('input[name="date_of_birth"]').val(data.dob || '');
                $('input[name="email"]').val(data.email || '');
                if (data.mobile_no) {
                    $('input[name="phone"]').val(data.mobile_no);
                    clearFieldError('phone');
                }
                
                // Registration Date
                if (data.registration_date) {
                    $('input[name="admission_date"]').val(data.registration_date);
                    clearFieldError('admission_date');
                }
                
                // Academic Information
                if (data.academic_year_id) {
                    $('select[name="academic_year_id"]').val(data.academic_year_id).trigger('change');
                    clearFieldError('academic_year_id');
                }
                if (data.class_id) {
                    // Set class and wait for sections/admission fee to load
                    // Only trigger change if the class is different to avoid resetting section
                    if ($('select[name="class_id"]').val() != data.class_id) {
                        $('select[name="class_id"]').val(data.class_id).trigger('change');
                    }
                    clearFieldError('class_id');
                }
                // Note: section_id will be populated when class data loads via AJAX
                // Don't clear its error here as it might not be set yet
                
                // Additional Personal Details
                $('select[name="student_type"]').val(data.student_type || '').trigger('change');
                $('select[name="blood_group"]').val(data.blood_group || '').trigger('change');
                $('input[name="aadhar_no"]').val(data.aadhar_no || '');
                $('input[name="place_of_birth"]').val(data.place_of_birth || '');
                $('input[name="nationality"]').val(data.nationality || '');
                clearFieldError('nationality');
                $('select[name="religion"]').val(data.religion || '').trigger('change');
                $('select[name="category"]').val(data.category || '').trigger('change');
                $('input[name="mother_tongue"]').val(data.mother_tongue || '');
                
                // Family Information
                $('input[name="number_of_brothers"]').val(data.number_of_brothers || '');
                $('input[name="number_of_sisters"]').val(data.number_of_sisters || '');
                
                // Father's Details
                if (data.father_first_name) {
                    $('input[name="father_first_name"]').val(data.father_first_name);
                    clearFieldError('father_first_name');
                }
                $('input[name="father_middle_name"]').val(data.father_middle_name || '');
                if (data.father_last_name) {
                    $('input[name="father_last_name"]').val(data.father_last_name);
                    clearFieldError('father_last_name');
                }
                $('input[name="father_email"]').val(data.father_email || '');
                if (data.father_mobile_no) {
                    $('input[name="father_mobile"]').val(data.father_mobile_no);
                    clearFieldError('father_mobile');
                }
                $('input[name="father_occupation"]').val(data.father_occupation || '');
                $('input[name="father_organization"]').val(data.father_organization || '');
                $('input[name="father_qualification"]').val(data.father_qualification || '');
                $('input[name="father_annual_income"]').val(data.father_annual_income || '');
                
                // Mother's Details
                if (data.mother_first_name) {
                    $('input[name="mother_first_name"]').val(data.mother_first_name);
                    clearFieldError('mother_first_name');
                }
                $('input[name="mother_middle_name"]').val(data.mother_middle_name || '');
                if (data.mother_last_name) {
                    $('input[name="mother_last_name"]').val(data.mother_last_name);
                    clearFieldError('mother_last_name');
                }
                $('input[name="mother_email"]').val(data.mother_email || '');
                if (data.mother_mobile_no) {
                    $('input[name="mother_mobile"]').val(data.mother_mobile_no);
                    clearFieldError('mother_mobile');
                }
                $('input[name="mother_occupation"]').val(data.mother_occupation || '');
                $('input[name="mother_organization"]').val(data.mother_organization || '');
                $('input[name="mother_qualification"]').val(data.mother_qualification || '');
                $('input[name="mother_annual_income"]').val(data.mother_annual_income || '');
                
                // Permanent Address
                if (data.permanent_address) {
                    $('input[name="permanent_address"]').val(data.permanent_address);
                    $('textarea[name="permanent_address"]').val(data.permanent_address);
                    clearFieldError('permanent_address');
                }
                
                if (data.permanent_state_id) {
                    $('select[name="permanent_state_id"]').attr('data-selected', data.permanent_state_id);
                }
                if (data.permanent_city_id) {
                    $('select[name="permanent_city_id"]').attr('data-selected', data.permanent_city_id);
                }
                
                if (data.permanent_country_id) {
                    $('select[name="permanent_country_id"]').val(data.permanent_country_id).trigger('change');
                    clearFieldError('permanent_country_id');
                } else {
                    $('select[name="permanent_country_id"]').val(102).trigger('change'); // default India
                }
                
                if (data.permanent_pin) {
                    $('input[name="permanent_pin"]').val(data.permanent_pin);
                    clearFieldError('permanent_pin');
                }
                
                // Correspondence Address
                $('input[name="correspondence_address"]').val(data.correspondence_address || '');
                $('textarea[name="correspondence_address"]').val(data.correspondence_address || '');
                
                if (data.correspondence_state_id) {
                    $('select[name="correspondence_state_id"]').attr('data-selected', data.correspondence_state_id);
                }
                if (data.correspondence_city_id) {
                    $('select[name="correspondence_city_id"]').attr('data-selected', data.correspondence_city_id);
                }
                
                if (data.correspondence_country_id) {
                    $('select[name="correspondence_country_id"]').val(data.correspondence_country_id).trigger('change');
                } else {
                    $('select[name="correspondence_country_id"]').val(102).trigger('change'); // default India
                }
                
                $('input[name="correspondence_pin"]').val(data.correspondence_pin || '');
                
                // Clear errors for admission-specific fields ONLY if they have values
                // Don't clear errors for empty fields - they should show validation errors if required
                if (data.roll_no) {
                    $('input[name="roll_no"]').val(data.roll_no);
                    clearFieldError('roll_no');
                }
                if (data.receipt_no) {
                    $('input[name="receipt_no"]').val(data.receipt_no);
                    clearFieldError('receipt_no');
                }
                // section_id will be set when class data loads, don't clear it here
                // admission_fee will be set when class data loads, don't clear it here
                
                // Photos - Load from registration if available
                if (data.father_photo) {
                    loadImagePreview(data.father_photo, 'father-photo-preview', 'father-photo-icon', 'father-photo-remove');
                    $('#father_photo_path').val(data.father_photo);
                }
                if (data.mother_photo) {
                    loadImagePreview(data.mother_photo, 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove');
                    $('#mother_photo_path').val(data.mother_photo);
                }
                if (data.student_photo) {
                    loadImagePreview(data.student_photo, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove');
                    $('#student_photo_path').val(data.student_photo);
                }
                
                // Signatures - Load from registration if available
                if (data.father_signature) {
                    loadImagePreview(data.father_signature, 'father-signature-preview', 'father-signature-icon', 'father-signature-remove');
                    $('#father_signature_path').val(data.father_signature);
                }
                if (data.mother_signature) {
                    loadImagePreview(data.mother_signature, 'mother-signature-preview', 'mother-signature-icon', 'mother-signature-remove');
                    $('#mother_signature_path').val(data.mother_signature);
                }
                if (data.student_signature) {
                    loadImagePreview(data.student_signature, 'student-signature-preview', 'student-signature-icon', 'student-signature-remove');
                    $('#student_signature_path').val(data.student_signature);
                }
                
                // Clear all error messages and red borders after a short delay to ensure Select2 is updated
                // This only clears errors for fields that were populated from registration data
                setTimeout(function() {
                    // List of fields that were populated from registration
                    const populatedFields = [
                        'first_name', 'last_name', 'gender', 'phone', 'admission_date',
                        'academic_year_id', 'class_id', 'nationality',
                        'father_first_name', 'father_last_name', 'father_mobile_no',
                        'mother_first_name', 'mother_last_name', 'mother_mobile_no',
                        'permanent_address', 'permanent_state', 'permanent_city', 'permanent_pin'
                    ];
                    
                    // Clear errors only for fields that were populated
                    populatedFields.forEach(function(fieldName) {
                        const $field = $(`[name="${fieldName}"]`);
                        if ($field.length && $field.val()) {
                            $field.removeClass('border-red-500');
                            $field.closest('div').find('p.text-red-500').remove();
                            // Also clear Select2 error styling
                            if ($field.hasClass('select2-hidden-accessible')) {
                                $field.next('.select2-container').find('.select2-selection').removeClass('border-red-500');
                            }
                        }
                    });
                }, 300);
            },
            error: function(xhr) {
                alert('Error fetching registration data');
                console.error(xhr);
            }
        });
    });

    // Trigger change event if registration is selected and NO validation errors exist
    // If validation errors exist, we want to keep the old() values and let Laravel show the errors!
    @if(!$errors->any())
        if ($('#registration_select').val()) {
            $('#registration_select').trigger('change');
        }
    @endif
    
    // Global error clearing - clear errors when fields are interacted with
    $(document).on('input change', 'input, select, textarea', function(e) {
        const $field = $(this);
        const $wrapper = $field.closest('div');
        
        // Always remove red border from the field itself on interaction
        $field.removeClass('border-red-500');
        
        // If it's a Select2 field, remove border from its container too
        if ($field.hasClass('select2-hidden-accessible')) {
            $field.next('.select2-container').find('.select2-selection').removeClass('border-red-500');
        }
        
        // Remove the error message text
        $wrapper.find('.error-message, p.text-red-500').remove();
    });


    /**
     * Fix form submission issues caused by Select2 and disabled selects:
     * 1. Select2-wrapped selects with required attribute cannot be focused by
     *    native browser validation → silently blocks submission.
     * 2. Disabled selects (state/city before selection) don't submit values →
     *    server never receives them, validation errors appear but old() is empty.
     */
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            // Fix Select2 hidden selects blocking browser validation
            form.querySelectorAll('select.select2-hidden-accessible').forEach(function(select) {
                select.removeAttribute('required');
            });
            // Fix disabled selects not sending values
            form.querySelectorAll('select[disabled]').forEach(function(select) {
                select.removeAttribute('disabled');
            });
        }, true);
    });
});
</script>
@endpush
