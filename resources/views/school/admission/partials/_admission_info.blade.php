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
                <select name="registration_no" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Enter Registration No</option>
                    @foreach($registrations as $registration)
                        <option value="{{ $registration->registration_no }}">{{ $registration->registration_no }} - {{ $registration->first_name }} {{ $registration->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Academic Year <span class="text-red-500">*</span>
                </label>
                <select name="academic_year_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" id="class_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Section <span class="text-red-500">*</span>
                </label>
                <select name="section_id" id="section_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Section</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Roll No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="roll_no" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white" placeholder="Enter Roll No">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Receipt No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="receipt_no" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white" placeholder="Enter Receipt No">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Registration Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="admission_date" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Admission Fee <span class="text-red-500">*</span>
                </label>
                <input type="number" step="0.01" name="admission_fee" id="admission_fee" readonly class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-gray-100 dark:bg-gray-600 dark:text-white" placeholder="Admission Fee">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Referred by
                </label>
                <select name="referred_by" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Referred by</option>
                    <option value="Staff">Staff</option>
                    <option value="Parent">Parent</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('class_id').addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.getElementById('section_id');
    const admissionFeeInput = document.getElementById('admission_fee');
    
    // Clear sections
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    admissionFeeInput.value = '';
    
    if (classId) {
        // Fetch sections and admission fee for selected class
        fetch(`{{ url('school/admission/class-data') }}/${classId}`)
            .then(response => response.json())
            .then(data => {
                // Populate sections
                data.sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.name;
                    sectionSelect.appendChild(option);
                });
                
                // Set admission fee
                admissionFeeInput.value = data.admission_fee;
            })
            .catch(error => console.error('Error:', error));
    }
});
</script>
@endpush
