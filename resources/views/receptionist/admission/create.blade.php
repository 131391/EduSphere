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

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif



    <form action="{{ route('receptionist.admission.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- Admission Info -->
        @include('receptionist.admission.partials._admission_info')

        <!-- Personal Info -->
        @include('school.admission.partials._personal_info')

        <!-- Father Details -->
        @include('school.admission.partials._father_details')

        <!-- Mother Details -->
        @include('school.admission.partials._mother_details')

        <!-- Address Details -->
        @include('school.admission.partials._address_details')

        <!-- Correspondence Address -->
        @include('school.admission.partials._correspondence_address')

        <!-- Photo Details -->
        @include('school.admission.partials._photo_details')

        <!-- Signature Details -->
        @include('school.admission.partials._signature_details')


        <div class="flex justify-end gap-4">
            <button type="reset" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Reset</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Submit Admission</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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
                $('select[name="permanent_country_id"]').val(data.permanent_country_id || 1).trigger('change');
                if (data.permanent_state) {
                    $('input[name="permanent_state"]').val(data.permanent_state);
                    clearFieldError('permanent_state');
                }
                if (data.permanent_city) {
                    $('input[name="permanent_city"]').val(data.permanent_city);
                    clearFieldError('permanent_city');
                }
                if (data.permanent_pin) {
                    $('input[name="permanent_pin"]').val(data.permanent_pin);
                    clearFieldError('permanent_pin');
                }
                
                // Correspondence Address
                $('input[name="correspondence_address"]').val(data.correspondence_address || '');
                $('textarea[name="correspondence_address"]').val(data.correspondence_address || '');
                $('select[name="correspondence_country_id"]').val(data.correspondence_country_id || 1).trigger('change');
                $('input[name="correspondence_state"]').val(data.correspondence_state || '');
                $('input[name="correspondence_city"]').val(data.correspondence_city || '');
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
            },
            error: function(xhr) {
                alert('Error fetching registration data');
                console.error(xhr);
            }
        });
    });

    // Trigger change event if registration is selected (e.g. after validation error)
    if ($('#registration_select').val()) {
        $('#registration_select').trigger('change');
    }
    
    // Global error clearing - clear errors when fields are interacted with (but not on page load)
    // Use a flag to track if this is user interaction vs page load
    let isUserInteraction = false;
    let pageLoadComplete = false;
    
    // Mark page load as complete after a delay
    setTimeout(function() {
        pageLoadComplete = true;
        // Only enable user interaction clearing after page is fully loaded
        setTimeout(function() {
            isUserInteraction = true;
        }, 100);
    }, 1000);
    
    $(document).on('input change', 'input, select, textarea', function(e) {
        // Only clear errors if this is actual user interaction, not page load or programmatic change
        if (!isUserInteraction || !pageLoadComplete) return;
        
        // Don't clear if this was triggered programmatically (e.g., by Select2 initialization)
        if (e.originalEvent === undefined) return;
        
        const $field = $(this);
        const fieldName = $field.attr('name');
        
        if (fieldName && $field.val()) {
            // Remove red border
            $field.removeClass('border-red-500');
            
            // Remove error message (only <p> tags with text-red-500, not asterisks in labels)
            $field.closest('div').find('p.text-red-500').remove();
            
            // For Select2 fields, also remove error styling from container
            if ($field.hasClass('select2-hidden-accessible')) {
                $field.next('.select2-container').find('.select2-selection').removeClass('border-red-500');
            }
        }
    });
});
</script>
@endpush
