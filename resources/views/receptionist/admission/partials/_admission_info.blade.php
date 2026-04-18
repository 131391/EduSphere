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
                    <select name="registration_id" 
                            x-model="registrationId"
                            @change="fetchRegistrationData()"
                            class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                            :class="errors.registration_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <option value="">Select Registration</option>
                        @foreach($registrations as $registration)
                            <option value="{{ $registration->id }}" data-reg-no="{{ $registration->registration_no }}">{{ $registration->registration_no }} - {{ $registration->first_name }} {{ $registration->last_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="registration_no" x-model="formData.registration_no">
                    <template x-if="errors.registration_id">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.registration_id[0]"></p>
                    </template>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Academic Year <span class="text-red-500">*</span>
                </label>
                <select name="academic_year_id" 
                        x-model="formData.academic_year_id"
                        @change="clearError('academic_year_id')"
                        class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        :class="errors.academic_year_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.academic_year_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.academic_year_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" 
                        x-model="formData.class_id"
                        @change="clearError('class_id')"
                        class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        :class="errors.class_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.class_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.class_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Section <span class="text-red-500">*</span>
                </label>
                <select name="section_id" 
                        x-model="formData.section_id"
                        @change="clearError('section_id')"
                        class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        :class="errors.section_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Select Section</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name" :selected="section.id == formData.section_id"></option>
                    </template>
                </select>
                <template x-if="errors.section_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.section_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Roll No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="roll_no" 
                       x-model="formData.roll_no"
                       @input="clearError('roll_no')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                       :class="errors.roll_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       placeholder="Enter Roll No">
                <template x-if="errors.roll_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.roll_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Receipt No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="receipt_no" 
                       x-model="formData.receipt_no"
                       @input="clearError('receipt_no')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                       :class="errors.receipt_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       placeholder="Enter Receipt No">
                <template x-if="errors.receipt_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.receipt_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Registration Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="admission_date" 
                       x-model="formData.admission_date"
                       @input="clearError('admission_date')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                       :class="errors.admission_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.admission_date">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.admission_date[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Admission Fee <span class="text-red-500">*</span>
                </label>
                <input type="number" step="0.01" name="admission_fee" 
                       x-model="formData.admission_fee"
                       readonly 
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-gray-100 dark:bg-gray-600 dark:text-white"
                       :class="errors.admission_fee ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       placeholder="Admission Fee">
                <template x-if="errors.admission_fee">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.admission_fee[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Referred by
                </label>
                <select name="referred_by" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Referred by</option>
                    <option value="Staff" {{ old('referred_by', isset($student) ? $student->referred_by : '') == 'Staff' ? 'selected' : '' }}>Staff</option>
                    <option value="Parent" {{ old('referred_by', isset($student) ? $student->referred_by : '') == 'Parent' ? 'selected' : '' }}>Parent</option>
                    <option value="Other" {{ old('referred_by', isset($student) ? $student->referred_by : '') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>
    </div>
</div>

