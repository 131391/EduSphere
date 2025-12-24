@extends('layouts.receptionist')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px;
        color: #374151;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .dark .select2-container--default .select2-selection--single {
        background-color: #374151;
        border-color: #4b5563;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Student Registration</h1>
            <p class="text-gray-600 dark:text-gray-400">Fill in the details to register a new student</p>
        </div>
        <a href="{{ route('receptionist.student-registrations.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    <form action="{{ route('receptionist.student-registrations.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('receptionist.student-registrations.partials.form')
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
    // Initialize Select2 on enquiry dropdown
    $('#enquiry_id').select2({
        placeholder: 'Search Enquiry by No or Student Name',
        allowClear: true,
        width: '100%'
    });

    // Update registration fee when class is manually changed
    $('select[name="class_id"]').on('change', function() {
        const classId = $(this).val();
        
        if (classId) {
            fetch(`/receptionist/student-registrations/registration-fee/${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('input[name="registration_fee"]').val(data.fee);
                    }
                })
                .catch(error => {
                    console.error('Error fetching registration fee:', error);
                });
        } else {
            $('input[name="registration_fee"]').val('');
        }
    });

    // Auto-fill form when enquiry is selected
    $('#enquiry_id').on('change', function() {
        const enquiryId = $(this).val();
        
        if (enquiryId) {
            fetch(`/receptionist/student-registrations/enquiry/${enquiryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const enquiry = data.data;
                        
                        // Academic Year (if available)
                        if (enquiry.academic_year_id) {
                            $('select[name="academic_year_id"]').val(enquiry.academic_year_id).trigger('change');
                        }
                        
                        // Personal Information
                        const nameParts = (enquiry.student_name || '').split(' ');
                        $('input[name="first_name"]').val(nameParts[0] || '');
                        $('input[name="middle_name"]').val(nameParts.slice(1, -1).join(' ') || '');
                        $('input[name="last_name"]').val(nameParts[nameParts.length - 1] || '');
                        
                        if (enquiry.gender) {
                            $('select[name="gender"]').val(enquiry.gender).trigger('change');
                        }
                        
                        // Format DOB to YYYY-MM-DD if it exists
                        if (enquiry.dob) {
                            let dobValue = enquiry.dob;
                            // If it's an object with date property (Laravel date format)
                            if (typeof dobValue === 'object' && dobValue.date) {
                                dobValue = dobValue.date.split(' ')[0];
                            }
                            // Ensure it's in YYYY-MM-DD format
                            if (dobValue && dobValue.length >= 10) {
                                dobValue = dobValue.substring(0, 10);
                            }
                            console.log('Setting DOB:', dobValue);
                            $('input[name="dob"]').val(dobValue);
                        }
                        
                        $('input[name="email"]').val(enquiry.email_id || '');
                        $('input[name="mobile_no"]').val(enquiry.contact_no || '');
                        
                        // Religion and Category
                        if (enquiry.religion) {
                            $('select[name="religion"]').val(enquiry.religion).trigger('change');
                        }
                        if (enquiry.category) {
                            $('select[name="category"]').val(enquiry.category).trigger('change');
                        }
                        
                        // Number of Brothers and Sisters
                        if (enquiry.no_of_brothers !== undefined && enquiry.no_of_brothers !== null) {
                            $('input[name="number_of_brothers"]').val(enquiry.no_of_brothers);
                        }
                        if (enquiry.no_of_sisters !== undefined && enquiry.no_of_sisters !== null) {
                            $('input[name="number_of_sisters"]').val(enquiry.no_of_sisters);
                        }
                        
                        // Father's Details
                        const fatherNameParts = (enquiry.father_name || '').split(' ');
                        $('input[name="father_first_name"]').val(fatherNameParts[0] || '');
                        $('input[name="father_middle_name"]').val(fatherNameParts.slice(1, -1).join(' ') || '');
                        $('input[name="father_last_name"]').val(fatherNameParts[fatherNameParts.length - 1] || '');
                        $('input[name="father_mobile_no"]').val(enquiry.father_contact || '');
                        $('input[name="father_occupation"]').val(enquiry.father_occupation || '');
                        $('input[name="father_email"]').val(enquiry.father_email || '');
                        
                        // Father's Additional Details
                        if (enquiry.father_qualification) {
                            $('select[name="father_qualification"]').val(enquiry.father_qualification).trigger('change');
                        }
                        if (enquiry.father_organization) {
                            $('input[name="father_organization"]').val(enquiry.father_organization);
                        }
                        if (enquiry.father_office_address) {
                            $('input[name="father_office_address"]').val(enquiry.father_office_address);
                        }
                        if (enquiry.father_department) {
                            $('input[name="father_department"]').val(enquiry.father_department);
                        }
                        if (enquiry.father_designation) {
                            $('input[name="father_designation"]').val(enquiry.father_designation);
                        }
                        if (enquiry.father_annual_income !== undefined && enquiry.father_annual_income !== null) {
                            $('input[name="father_annual_income"]').val(enquiry.father_annual_income);
                        }
                        
                        // Mother's Details
                        const motherNameParts = (enquiry.mother_name || '').split(' ');
                        $('input[name="mother_first_name"]').val(motherNameParts[0] || '');
                        $('input[name="mother_middle_name"]').val(motherNameParts.slice(1, -1).join(' ') || '');
                        $('input[name="mother_last_name"]').val(motherNameParts[motherNameParts.length - 1] || '');
                        $('input[name="mother_mobile_no"]').val(enquiry.mother_contact || '');
                        $('input[name="mother_occupation"]').val(enquiry.mother_occupation || '');
                        $('input[name="mother_email"]').val(enquiry.mother_email || '');
                        
                        // Mother's Additional Details
                        if (enquiry.mother_qualification) {
                            $('select[name="mother_qualification"]').val(enquiry.mother_qualification).trigger('change');
                        }
                        if (enquiry.mother_organization) {
                            $('input[name="mother_organization"]').val(enquiry.mother_organization);
                        }
                        if (enquiry.mother_office_address) {
                            $('input[name="mother_office_address"]').val(enquiry.mother_office_address);
                        }
                        if (enquiry.mother_department) {
                            $('input[name="mother_department"]').val(enquiry.mother_department);
                        }
                        if (enquiry.mother_designation) {
                            $('input[name="mother_designation"]').val(enquiry.mother_designation);
                        }
                        if (enquiry.mother_annual_income !== undefined && enquiry.mother_annual_income !== null) {
                            $('input[name="mother_annual_income"]').val(enquiry.mother_annual_income);
                        }
                        
                        // Address
                        if (enquiry.permanent_address) {
                            $('input[name="permanent_address"]').val(enquiry.permanent_address);
                        }
                        if (enquiry.country_id) {
                            $('select[name="permanent_country_id"]').val(enquiry.country_id).trigger('change');
                        }
                        
                        // Note: Enquiry model doesn't have separate city, state, pincode fields
                        // They would need to be parsed from permanent_address if needed
                        
                        // Class (if available)
                        if (enquiry.class_id) {
                            $('select[name="class_id"]').val(enquiry.class_id).trigger('change');
                            
                            // Fetch and fill registration fee for the selected class
                            fetch(`/receptionist/student-registrations/registration-fee/${enquiry.class_id}`)
                                .then(response => response.json())
                                .then(feeData => {
                                    if (feeData.success) {
                                        $('input[name="registration_fee"]').val(feeData.fee);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching registration fee:', error);
                                });
                        }
                        
                        // Photos - Load from enquiry if available
                        if (enquiry.father_photo) {
                            loadImagePreview(enquiry.father_photo, 'father-photo-preview', 'father-photo-icon', 'father-photo-remove');
                        }
                        if (enquiry.mother_photo) {
                            loadImagePreview(enquiry.mother_photo, 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove');
                        }
                        if (enquiry.student_photo) {
                            loadImagePreview(enquiry.student_photo, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove');
                        }
                        
                        // Signatures - Load from enquiry if available
                        if (enquiry.father_signature) {
                            loadImagePreview(enquiry.father_signature, 'father-signature-preview', 'father-signature-icon', 'father-signature-remove');
                        }
                        if (enquiry.mother_signature) {
                            loadImagePreview(enquiry.mother_signature, 'mother-signature-preview', 'mother-signature-icon', 'mother-signature-remove');
                        }
                        if (enquiry.student_signature) {
                            loadImagePreview(enquiry.student_signature, 'student-signature-preview', 'student-signature-icon', 'student-signature-remove');
                        }
                        
                        console.log('Form auto-filled successfully from enquiry #' + enquiry.enquiry_no);
                    }
                })
                .catch(error => {
                    console.error('Error fetching enquiry data:', error);
                    alert('Failed to load enquiry data. Please try again.');
                });
        }
    });
});
</script>
@endpush
