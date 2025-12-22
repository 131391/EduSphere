@extends('layouts.school')

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
        <a href="{{ route('school.student-registrations.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('school.student-registrations.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('school.student-registrations.partials.form')
    </form>
</div>
@endsection

@push('scripts')
<script>
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
            fetch(`/school/student-registrations/registration-fee/${classId}`)
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
            // Show loading indicator (optional)
            const originalText = $(this).next('.select2').find('.select2-selection__rendered').text();
            
            fetch(`/school/student-registrations/enquiry/${enquiryId}`)
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
                        
                        // Father's Details
                        const fatherNameParts = (enquiry.father_name || '').split(' ');
                        $('input[name="father_first_name"]').val(fatherNameParts[0] || '');
                        $('input[name="father_middle_name"]').val(fatherNameParts.slice(1, -1).join(' ') || '');
                        $('input[name="father_last_name"]').val(fatherNameParts[fatherNameParts.length - 1] || '');
                        $('input[name="father_mobile_no"]').val(enquiry.father_contact || '');
                        $('input[name="father_occupation"]').val(enquiry.father_occupation || '');
                        $('input[name="father_email"]').val(enquiry.father_email || '');
                        
                        // Mother's Details
                        const motherNameParts = (enquiry.mother_name || '').split(' ');
                        $('input[name="mother_first_name"]').val(motherNameParts[0] || '');
                        $('input[name="mother_middle_name"]').val(motherNameParts.slice(1, -1).join(' ') || '');
                        $('input[name="mother_last_name"]').val(motherNameParts[motherNameParts.length - 1] || '');
                        $('input[name="mother_mobile_no"]').val(enquiry.mother_contact || '');
                        $('input[name="mother_occupation"]').val(enquiry.mother_occupation || '');
                        $('input[name="mother_email"]').val(enquiry.mother_email || '');
                        
                        // Address
                        $('textarea[name="permanent_address"]').val(enquiry.address || '');
                        $('input[name="permanent_city"]').val(enquiry.city || '');
                        $('input[name="permanent_state"]').val(enquiry.state || '');
                        $('input[name="permanent_pin"]').val(enquiry.pincode || '');
                        
                        // Copy to current address if checkbox exists
                        $('textarea[name="current_address"]').val(enquiry.address || '');
                        $('input[name="current_city"]').val(enquiry.city || '');
                        $('input[name="current_state"]').val(enquiry.state || '');
                        $('input[name="current_pin"]').val(enquiry.pincode || '');
                        
                        // Class (if available)
                        if (enquiry.class_id) {
                            $('select[name="class_id"]').val(enquiry.class_id).trigger('change');
                            
                            // Fetch and fill registration fee for the selected class
                            fetch(`/school/student-registrations/registration-fee/${enquiry.class_id}`)
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
