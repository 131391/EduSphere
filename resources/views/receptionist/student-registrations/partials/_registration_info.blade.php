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
                @if(isset($studentRegistration) && $studentRegistration->id)
                    <input type="text" readonly
                           value="{{ $studentRegistration->enquiry ? $studentRegistration->enquiry->enquiry_no . ' - ' . $studentRegistration->enquiry->student_name : 'N/A' }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white bg-gray-50 dark:bg-gray-900 cursor-not-allowed">
                    <input type="hidden" name="enquiry_id" value="{{ $studentRegistration->enquiry_id }}">
                @else
                    <select name="enquiry_id" id="enquiry_id" @change="delete errors.enquiry_id"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                            :class="errors.enquiry_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <option value="">Select Enquiry No</option>
                        @foreach($enquiries as $enquiry)
                            <option value="{{ $enquiry->id }}" {{ (old('enquiry_id', $studentRegistration->enquiry_id ?? '') == $enquiry->id) ? 'selected' : '' }}>
                                {{ $enquiry->enquiry_no }} - {{ $enquiry->student_name }}
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.enquiry_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.enquiry_id[0]"></p>
                    </template>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Academic Year <span class="text-red-500">*</span>
                </label>
                <select name="academic_year_id" @change="delete errors.academic_year_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.academic_year_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ (old('academic_year_id', $studentRegistration->academic_year_id ?? '') == $year->id) ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
                <template x-if="errors.academic_year_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.academic_year_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    For Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" x-model="selectedClassId" @change="updateFee(); delete errors.class_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.class_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (old('class_id', $studentRegistration->class_id ?? '') == $class->id) ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                <template x-if="errors.class_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.class_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Registration Fee
                </label>
                <input type="number" step="0.01" name="registration_fee" x-ref="registrationFeeInput" readonly
                       value="{{ old('registration_fee', $studentRegistration->registration_fee ?? '') }}" placeholder="Enter Registration Fee"
                       @input="delete errors.registration_fee"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm bg-gray-50 dark:bg-gray-900 cursor-not-allowed"
                       :class="errors.registration_fee ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.registration_fee">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.registration_fee[0]"></p>
                </template>
                <p class="text-[9px] text-gray-400 mt-1 italic italic">Auto-calculated based on selected class</p>
            </div>
            </div>
        </div>
    </div>
</div>
