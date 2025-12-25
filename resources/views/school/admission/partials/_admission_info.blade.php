{{-- Admission Form Information --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Admission Form Information
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Registration No <span class="text-red-500">*</span>
                </label>
                @if(isset($student))
                    @php
                        $regNo = '';
                        if($student->registration_no) {
                            $reg = $registrations->firstWhere('registration_no', $student->registration_no);
                            $regNo = $student->registration_no;
                            if ($reg) {
                                $regNo .= ' - ' . $reg->first_name . ' ' . $reg->last_name;
                            }
                        }
                    @endphp
                    <input type="text" value="{{ $regNo }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed" readonly>
                    <input type="hidden" name="registration_no" value="{{ $student->registration_no }}">
                @else
                    <select name="registration_id" id="registration_select" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Registration</option>
                        @foreach($registrations as $registration)
                            <option value="{{ $registration->id }}" data-reg-no="{{ $registration->registration_no }}" {{ old('registration_id') == $registration->id ? 'selected' : '' }}>{{ $registration->registration_no }} - {{ $registration->first_name }} {{ $registration->last_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="registration_no" id="registration_no_hidden">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Academic Year <span class="text-red-500">*</span>
                </label>
                <select name="academic_year_id" class="w-full px-4 py-2 border {{ $errors->has('academic_year_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', isset($student) ? $student->academic_year_id : '') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" id="class_id" class="w-full px-4 py-2 border {{ $errors->has('class_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', isset($student) ? $student->class_id : '') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
                @error('class_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Section <span class="text-red-500">*</span>
                </label>
                <select name="section_id" id="section_id" class="w-full px-4 py-2 border {{ $errors->has('section_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Section</option>
                </select>
                @error('section_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Roll No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="roll_no" value="{{ old('roll_no', isset($student) ? $student->roll_no : '') }}" class="w-full px-4 py-2 border {{ $errors->has('roll_no') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white" placeholder="Enter Roll No">
                @error('roll_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Receipt No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="receipt_no" value="{{ old('receipt_no', isset($student) ? $student->receipt_no : '') }}" class="w-full px-4 py-2 border {{ $errors->has('receipt_no') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white" placeholder="Enter Receipt No">
                @error('receipt_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Registration Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="admission_date" value="{{ old('admission_date', isset($student) && $student->admission_date ? $student->admission_date->format('Y-m-d') : '') }}" class="w-full px-4 py-2 border {{ $errors->has('admission_date') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('admission_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Admission Fee <span class="text-red-500">*</span>
                </label>
                <input type="number" step="0.01" name="admission_fee" id="admission_fee" value="{{ old('admission_fee', isset($student) ? $student->admission_fee : '') }}" readonly class="w-full px-4 py-2 border {{ $errors->has('admission_fee') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-gray-100 dark:bg-gray-600 dark:text-white" placeholder="Admission Fee">
                @error('admission_fee')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Referred by
                </label>
                <select name="referred_by" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Referred by</option>
                    <option value="Staff" {{ old('referred_by', isset($student) ? $student->referred_by : '') == 'Staff' ? 'selected' : '' }}>Staff</option>
                    <option value="Parent" {{ old('referred_by', isset($student) ? $student->referred_by : '') == 'Parent' ? 'selected' : '' }}>Parent</option>
                    <option value="Other" {{ old('referred_by', isset($student) ? $student->referred_by : '') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Function to load sections and admission fee for a class
    function loadClassData(classId, selectedSectionId = null) {
        const sectionSelect = $('#section_id');
        const admissionFeeInput = $('#admission_fee');
        
        // Clear sections
        sectionSelect.html('<option value="">Select Section</option>');
        admissionFeeInput.val('');
        
        if (classId) {
            // Fetch sections and admission fee for selected class
            $.ajax({
                url: `{{ url('school/admission/class-data') }}/${classId}`,
                method: 'GET',
                success: function(data) {
                    // Populate sections
                    data.sections.forEach(section => {
                        const isSelected = selectedSectionId && section.id == selectedSectionId ? 'selected' : '';
                        sectionSelect.append(`<option value="${section.id}" ${isSelected}>${section.name}</option>`);
                    });
                    
                    // Set admission fee
                    admissionFeeInput.val(data.admission_fee);
                    
                    // Clear errors for section and admission fee when they're populated
                    if (data.sections.length > 0) {
                        sectionSelect.removeClass('border-red-500');
                        sectionSelect.closest('div').find('p.text-red-500').remove();
                    }
                    if (data.admission_fee) {
                        admissionFeeInput.removeClass('border-red-500');
                        admissionFeeInput.closest('div').find('p.text-red-500').remove();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading class data:', xhr);
                }
            });
        }
    }
    
    // Trigger when class changes
    $('#class_id').on('change', function() {
        loadClassData($(this).val());
    });
    
    // Load on page load if class is already selected
    const initialClassId = $('#class_id').val();
    const oldSectionId = "{{ old('section_id', isset($student) ? $student->section_id : '') }}";
    if (initialClassId) {
        loadClassData(initialClassId, oldSectionId);
    }
});
</script>
@endpush
