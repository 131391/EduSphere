{{-- Enquiry Form Section --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Enquiry Form
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-b-lg border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Academic Year <span class="text-red-500">*</span>
                </label>
                <select name="academic_year_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('academic_year_id') border-red-500 @enderror">
                    <option value="">Choose Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
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
                <select name="class_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('class_id') border-red-500 @enderror">
                    <option value="">Choose Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject Name</label>
                <input type="text" name="subject_name" value="{{ old('subject_name') }}" placeholder="Subject Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('subject_name') border-red-500 @enderror">
                @error('subject_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student's Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="student_name" value="{{ old('student_name') }}" placeholder="Student's Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('student_name') border-red-500 @enderror">
                @error('student_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                <select name="gender" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('gender') border-red-500 @enderror">
                    <option value="">Choose Gender</option>
                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Follow Up Date</label>
                <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('follow_up_date') border-red-500 @enderror">
                @error('follow_up_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Father's Details Section --}}
<div class="mb-6" x-data="{ fatherExpanded: false }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="fatherExpanded = !fatherExpanded">
        <span>Father's Details</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': fatherExpanded }"></i>
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-b-lg border border-gray-200 dark:border-gray-600">
        <!-- Always Visible: Name and Contact -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father's Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="Enter Father's Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_name') border-red-500 @enderror">
                @error('father_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Contact No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_contact" value="{{ old('father_contact') }}" placeholder="Enter Father contact no"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_contact') border-red-500 @enderror">
                @error('father_contact')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Collapsible Additional Fields -->
        <div x-show="fatherExpanded" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Email Id</label>
                <input type="email" name="father_email" value="{{ old('father_email') }}" placeholder="Enter Father Email id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_email') border-red-500 @enderror">
                @error('father_email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Qualification</label>
                <input type="text" name="father_qualification" value="{{ old('father_qualification') }}" placeholder="Enter Father qualification"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_qualification') border-red-500 @enderror">
                @error('father_qualification')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Occupation</label>
                <input type="text" name="father_occupation" value="{{ old('father_occupation') }}" placeholder="Enter Father Occupation"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_occupation') border-red-500 @enderror">
                @error('father_occupation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Annual Income</label>
                <input type="number" step="0.01" name="father_annual_income" value="{{ old('father_annual_income') }}" placeholder="Enter Father Annual Income"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_annual_income') border-red-500 @enderror">
                @error('father_annual_income')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization</label>
                <input type="text" name="father_organization" value="{{ old('father_organization') }}" placeholder="Organization"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_organization') border-red-500 @enderror">
                @error('father_organization')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Office Address</label>
                <input type="text" name="father_office_address" value="{{ old('father_office_address') }}" placeholder="Enter Father Office Address"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_office_address') border-red-500 @enderror">
                @error('father_office_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                <input type="text" name="father_department" value="{{ old('father_department') }}" placeholder="Enter Department"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_department') border-red-500 @enderror">
                @error('father_department')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                <input type="text" name="father_designation" value="{{ old('father_designation') }}" placeholder="Enter Designation"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('father_designation') border-red-500 @enderror">
                @error('father_designation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Mother's Details Section --}}
<div class="mb-6" x-data="{ motherExpanded: false }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="motherExpanded = !motherExpanded">
        <span>Mother's Details</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': motherExpanded }"></i>
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-b-lg border border-gray-200 dark:border-gray-600">
        <!-- Always Visible: Name and Contact -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother's Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="Enter mother's Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_name') border-red-500 @enderror">
                @error('mother_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Contact No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_contact" value="{{ old('mother_contact') }}" placeholder="Enter Mother contact no"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_contact') border-red-500 @enderror">
                @error('mother_contact')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Collapsible Additional Fields -->
        <div x-show="motherExpanded" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Email Id</label>
                <input type="email" name="mother_email" value="{{ old('mother_email') }}" placeholder="Enter Mother Email id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_email') border-red-500 @enderror">
                @error('mother_email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Qualification</label>
                <input type="text" name="mother_qualification" value="{{ old('mother_qualification') }}" placeholder="Enter Mother qualification"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_qualification') border-red-500 @enderror">
                @error('mother_qualification')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Occupation</label>
                <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}" placeholder="Enter Mother Occupation"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_occupation') border-red-500 @enderror">
                @error('mother_occupation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Annual Income</label>
                <input type="number" step="0.01" name="mother_annual_income" value="{{ old('mother_annual_income') }}" placeholder="Enter Mother Annual Income"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_annual_income') border-red-500 @enderror">
                @error('mother_annual_income')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization</label>
                <input type="text" name="mother_organization" value="{{ old('mother_organization') }}" placeholder="Organization"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_organization') border-red-500 @enderror">
                @error('mother_organization')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Office Address</label>
                <input type="text" name="mother_office_address" value="{{ old('mother_office_address') }}" placeholder="Enter Mother Office Address"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_office_address') border-red-500 @enderror">
                @error('mother_office_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                <input type="text" name="mother_department" value="{{ old('mother_department') }}" placeholder="Enter Department"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_department') border-red-500 @enderror">
                @error('mother_department')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                <input type="text" name="mother_designation" value="{{ old('mother_designation') }}" placeholder="Enter Designation"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('mother_designation') border-red-500 @enderror">
                @error('mother_designation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Contact Details Section --}}
<div class="mb-6" x-data="{ contactExpanded: false }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="contactExpanded = !contactExpanded">
        <span>Contact Details</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': contactExpanded }"></i>
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-b-lg border border-gray-200 dark:border-gray-600">
        <!-- Always Visible: Contact No and WhatsApp No -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Contact No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="contact_no" value="{{ old('contact_no') }}" placeholder="Enter Contact no"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('contact_no') border-red-500 @enderror">
                @error('contact_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Whatsapp No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="whatsapp_no" value="{{ old('whatsapp_no') }}" placeholder="Enter whatsapp  no"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('whatsapp_no') border-red-500 @enderror">
                @error('whatsapp_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Collapsible Additional Fields -->
        <div x-show="contactExpanded" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Facebook Id</label>
                <input type="text" name="facebook_id" value="{{ old('facebook_id') }}" placeholder="Enter Facebook Id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('facebook_id') border-red-500 @enderror">
                @error('facebook_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Id</label>
                <input type="email" name="email_id" value="{{ old('email_id') }}" placeholder="Enter Email id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('email_id') border-red-500 @enderror">
                @error('email_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMS No</label>
                <input type="text" name="sms_no" value="{{ old('sms_no') }}" placeholder="Enter SMS no"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('sms_no') border-red-500 @enderror">
                @error('sms_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Twitter Id</label>
                <input type="text" name="twitter_id" value="{{ old('twitter_id') }}" placeholder="Enter Twitter Id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('twitter_id') border-red-500 @enderror">
                @error('twitter_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Emergency Contact No</label>
                <input type="text" name="emergency_contact_no" value="{{ old('emergency_contact_no') }}" placeholder="Emergency Contact no"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('emergency_contact_no') border-red-500 @enderror">
                @error('emergency_contact_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Continue in next part due to character limit --}}

{{-- Personal Details Section --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Personal Details
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-b-lg border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">DOB</label>
                <input type="date" name="dob" value="{{ old('dob') }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('dob') border-red-500 @enderror">
                @error('dob')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aadhar No</label>
                <input type="text" name="aadhar_no" value="{{ old('aadhar_no') }}" placeholder="Aadhar no of the Students"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('aadhar_no') border-red-500 @enderror">
                @error('aadhar_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Grand Father Name</label>
                <input type="text" name="grand_father_name" value="{{ old('grand_father_name') }}" placeholder="Enter Grand father name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('grand_father_name') border-red-500 @enderror">
                @error('grand_father_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Annual Income</label>
                <input type="number" step="0.01" name="annual_income" value="{{ old('annual_income') }}" placeholder="Enter Annual Income"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('annual_income') border-red-500 @enderror">
                @error('annual_income')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">No of Brother's</label>
                <input type="number" step="0.01" name="no_of_brothers" value="{{ old('no_of_brothers', 0) }}" placeholder="Choose No of Brother's"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('no_of_brothers') border-red-500 @enderror">
                @error('no_of_brothers')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">No of Sister's</label>
                <input type="number" step="0.01" name="no_of_sisters" value="{{ old('no_of_sisters', 0) }}" placeholder="Choose No of Sister's"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('no_of_sisters') border-red-500 @enderror">
                @error('no_of_sisters')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('category') border-red-500 @enderror">
                    <option value="">Choose Category</option>
                    <option value="General" {{ old('category') == 'General' ? 'selected' : '' }}>General</option>
                    <option value="OBC" {{ old('category') == 'OBC' ? 'selected' : '' }}>OBC</option>
                    <option value="SC" {{ old('category') == 'SC' ? 'selected' : '' }}>SC</option>
                    <option value="ST" {{ old('category') == 'ST' ? 'selected' : '' }}>ST</option>
                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('category')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minority</label>
                <select name="minority" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('minority') border-red-500 @enderror">
                    <option value="">Choose Minority</option>
                    <option value="Yes" {{ old('minority') == 'Yes' ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ old('minority') == 'No' ? 'selected' : '' }}>No</option>
                </select>
                @error('minority')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Religion</label>
                <select name="religion" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('religion') border-red-500 @enderror">
                    <option value="">Choose Religion</option>
                    <option value="Hindu" {{ old('religion') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                    <option value="Muslim" {{ old('religion') == 'Muslim' ? 'selected' : '' }}>Muslim</option>
                    <option value="Christian" {{ old('religion') == 'Christian' ? 'selected' : '' }}>Christian</option>
                    <option value="Sikh" {{ old('religion') == 'Sikh' ? 'selected' : '' }}>Sikh</option>
                    <option value="Other" {{ old('religion') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('religion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transport Facility</label>
                <select name="transport_facility" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('transport_facility') border-red-500 @enderror">
                    <option value="">Choose Transport Facility</option>
                    <option value="Yes" {{ old('transport_facility') == 'Yes' ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ old('transport_facility') == 'No' ? 'selected' : '' }}>No</option>
                </select>
                @error('transport_facility')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hostel Facility</label>
                <select name="hostel_facility" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('hostel_facility') border-red-500 @enderror">
                    <option value="">Choose Hostel Facility</option>
                    <option value="Yes" {{ old('hostel_facility') == 'Yes' ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ old('hostel_facility') == 'No' ? 'selected' : '' }}>No</option>
                </select>
                @error('hostel_facility')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Previous Class</label>
                <input type="text" name="previous_class" value="{{ old('previous_class') }}" placeholder="Choose Previous Class"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('previous_class') border-red-500 @enderror">
                @error('previous_class')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Identity Marks</label>
                <textarea name="identity_marks" rows="3" placeholder="Enter Identity Marks"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('identity_marks') border-red-500 @enderror">{{ old('identity_marks') }}</textarea>
                @error('identity_marks')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permanent Address</label>
                <textarea name="permanent_address" rows="3" placeholder="Enter Permanent Address"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('permanent_address') border-red-500 @enderror">{{ old('permanent_address') }}</textarea>
                @error('permanent_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Previous School Name</label>
                <input type="text" name="previous_school_name" value="{{ old('previous_school_name') }}" placeholder="Previous School Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('previous_school_name') border-red-500 @enderror">
                @error('previous_school_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student's Roll No</label>
                <input type="text" name="student_roll_no" value="{{ old('student_roll_no') }}" placeholder="Student's Roll No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('student_roll_no') border-red-500 @enderror">
                @error('student_roll_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Passing Year</label>
                <input type="text" name="passing_year" value="{{ old('passing_year') }}" placeholder="Passing Year"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('passing_year') border-red-500 @enderror">
                @error('passing_year')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name Of The Exam</label>
                <input type="text" name="exam_name" value="{{ old('exam_name') }}" placeholder="Name Of The Exam"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('exam_name') border-red-500 @enderror">
                @error('exam_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Board / University Name</label>
                <input type="text" name="board_university" value="{{ old('board_university') }}" placeholder="Board / University Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white @error('board_university') border-red-500 @enderror">
                @error('board_university')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="only_child" value="1" {{ old('only_child') ? 'checked' : '' }}
                           class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Only Child of the Parents</span>
                </label>
            </div>
        </div>
    </div>
</div>

{{-- Upload Photo Section --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Upload Photo
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-b-lg border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father's Photo</label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        <img id="father-photo-preview" src="#" alt="Father's Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user text-gray-400 text-4xl" id="father-photo-icon"></i>
                        <button type="button" 
                                id="father-photo-remove" 
                                onclick="removeImage(event, 'father_photo', 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="father_photo" accept="image/*" 
                           onchange="previewImage(event, 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    @error('father_photo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother's Photo</label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        <img id="mother-photo-preview" src="#" alt="Mother's Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user text-gray-400 text-4xl" id="mother-photo-icon"></i>
                        <button type="button" 
                                id="mother-photo-remove" 
                                onclick="removeImage(event, 'mother_photo', 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="mother_photo" accept="image/*" 
                           onchange="previewImage(event, 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    @error('mother_photo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student Photo</label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        <img id="student-photo-preview" src="#" alt="Student Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user text-gray-400 text-4xl" id="student-photo-icon"></i>
                        <button type="button" 
                                id="student-photo-remove" 
                                onclick="removeImage(event, 'student_photo', 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="student_photo" accept="image/*" 
                           onchange="previewImage(event, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    @error('student_photo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId, iconId, removeBtnId) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            const icon = document.getElementById(iconId);
            const removeBtn = document.getElementById(removeBtnId);
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) {
                icon.classList.add('hidden');
            }
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    }
}

function removeImage(event, inputName, previewId, iconId, removeBtnId) {
    event.preventDefault();
    event.stopPropagation();
    
    const input = document.querySelector(`input[name="${inputName}"]`);
    const preview = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    const removeBtn = document.getElementById(removeBtnId);
    
    // Reset file input
    if (input) {
        input.value = '';
    }
    
    // Hide preview and show icon
    if (preview) {
        preview.src = '#';
        preview.classList.add('hidden');
    }
    if (icon) {
        icon.classList.remove('hidden');
    }
    if (removeBtn) {
        removeBtn.classList.add('hidden');
    }
}
</script>
