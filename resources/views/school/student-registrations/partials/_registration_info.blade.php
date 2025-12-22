{{-- Registration Form Information --}}
<div class="mb-6" x-data="{ 
    selectedClassId: '{{ old('class_id', $studentRegistration->class_id ?? '') }}',
    registrationFees: {
        @foreach($classes as $class)
            '{{ $class->id }}': '{{ $class->registrationFee->amount ?? 0 }}',
        @endforeach
    },
    updateFee() {
        if (this.selectedClassId && this.registrationFees[this.selectedClassId]) {
            $refs.registrationFeeInput.value = this.registrationFees[this.selectedClassId];
        }
    }
}">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Registration Form Information
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Enquiry No
                </label>
                <select name="enquiry_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Enquiry No</option>
                    @foreach($enquiries as $enquiry)
                        <option value="{{ $enquiry->id }}" {{ (old('enquiry_id', $studentRegistration->enquiry_id ?? '') == $enquiry->id) ? 'selected' : '' }}>
                            {{ $enquiry->enquiry_no }} - {{ $enquiry->student_name }}
                        </option>
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
                        <option value="{{ $year->id }}" {{ (old('academic_year_id', $studentRegistration->academic_year_id ?? '') == $year->id) ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    For Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" required x-model="selectedClassId" @change="updateFee()"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Registration Fee
                </label>
                <input type="number" step="0.01" name="registration_fee" x-ref="registrationFeeInput" readonly
                       value="{{ old('registration_fee', $studentRegistration->registration_fee ?? '') }}" placeholder="Enter Registration Fee"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white bg-gray-50 dark:bg-gray-900 cursor-not-allowed">
            </div>
        </div>
    </div>
</div>
