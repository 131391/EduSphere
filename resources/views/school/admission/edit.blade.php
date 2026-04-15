@extends('layouts.school')

@section('title', 'Edit Admission')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Edit Admission</h1>
        <a href="{{ route('school.admission.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>



    <form action="{{ route('school.admission.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')
        
        <!-- Admission Info -->
        @include('school.admission.partials._admission_info')

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
            <button type="submit" id="submit-btn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update Admission</button>
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
    $('#registration_select').on('change', function(e) {
        // Skip if this change event was NOT triggered by a real user interaction on the Edit page
        // (to prevent overwriting saved student data on page load)
        if (e.originalEvent === undefined && !$(this).data('manual-trigger')) return;

        const registrationId = $(this).val();
        
        if (!registrationId) return;
        
        // Fetch registration data
        $.ajax({
            url: `/school/admission/registration/${registrationId}`,
            method: 'GET',
            success: function(data) {
                // Helper function to clear errors for a field
                function clearFieldError(fieldName) {
                    const field = $(`[name="${fieldName}"]`);
                    if (field.length && field.val()) {
                        field.removeClass('border-red-500');
                        field.closest('div').find('p.text-red-500').remove();
                        if (field.hasClass('select2-hidden-accessible')) {
                            field.next('.select2-container').find('.select2-selection').removeClass('border-red-500');
                        }
                    }
                }
                
                // Personal Information
                if (data.first_name) { $('input[name="first_name"]').val(data.first_name); clearFieldError('first_name'); }
                $('input[name="middle_name"]').val(data.middle_name || '');
                if (data.last_name) { $('input[name="last_name"]').val(data.last_name); clearFieldError('last_name'); }
                if (data.gender) { $('select[name="gender"]').val(data.gender).trigger('change'); clearFieldError('gender'); }
                $('input[name="date_of_birth"]').val(data.dob || '');
                $('input[name="email"]').val(data.email || '');
                if (data.mobile_no) { $('input[name="phone"]').val(data.mobile_no); clearFieldError('phone'); }
                
                // Location Sync
                if (data.permanent_country_id) { $('select[name="permanent_country_id"]').val(data.permanent_country_id).trigger('change'); }
                if (data.permanent_state_id) { $('select[name="permanent_state_id"]').attr('data-selected', data.permanent_state_id); }
                if (data.permanent_city_id) { $('select[name="permanent_city_id"]').attr('data-selected', data.permanent_city_id); }

                // Basic logic for Father/Mother
                if (data.father_first_name) { $('input[name="father_first_name"]').val(data.father_first_name); }
                if (data.father_mobile_no) { $('input[name="father_mobile"]').val(data.father_mobile_no); }
                if (data.mother_first_name) { $('input[name="mother_first_name"]').val(data.mother_first_name); }
                if (data.mother_mobile_no) { $('input[name="mother_mobile"]').val(data.mother_mobile_no); }

                // Update Photos if available
                if (data.student_photo) { loadImagePreview(data.student_photo, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove'); }
            }
        });
    });
    
    // Load existing photos and signatures
    @if($student->photo)
        loadImagePreview('{{ $student->photo }}', 'student-photo-preview', 'student-photo-icon', 'student-photo-remove');
    @endif
    @if($student->father_photo)
        loadImagePreview('{{ $student->father_photo }}', 'father-photo-preview', 'father-photo-icon', 'father-photo-remove');
    @endif
    @if($student->mother_photo)
        loadImagePreview('{{ $student->mother_photo }}', 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove');
    @endif

    @if($student->signature)
        loadImagePreview('{{ $student->signature }}', 'student-signature-preview', 'student-signature-icon', 'student-signature-remove');
    @endif
    @if($student->father_signature)
        loadImagePreview('{{ $student->father_signature }}', 'father-signature-preview', 'father-signature-icon', 'father-signature-remove');
    @endif
    @if($student->mother_signature)
        loadImagePreview('{{ $student->mother_signature }}', 'mother-signature-preview', 'mother-signature-icon', 'mother-signature-remove');
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


    // Form submission interceptor
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            form.querySelectorAll('select.select2-hidden-accessible').forEach(function(select) { select.removeAttribute('required'); });
            form.querySelectorAll('select[disabled]').forEach(function(select) { select.removeAttribute('disabled'); });
        }, true);
    });
});
</script>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var firstError = document.querySelector('.text-red-500.text-xs.mt-1, .border-red-500');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });
    </script>
@endif
@endpush
