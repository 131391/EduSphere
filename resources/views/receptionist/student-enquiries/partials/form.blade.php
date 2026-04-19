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
                <select name="academic_year_id" 
                        x-model="formData.academic_year_id"
                        @change="clearError('academic_year_id')"
                        :class="{'border-red-500 ring-red-500/10': errors.academic_year_id}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Academic Year</option>
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
                        :class="{'border-red-500 ring-red-500/10': errors.class_id}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.class_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.class_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject Name</label>
                <input type="text" name="subject_name" 
                       x-model="formData.subject_name"
                       @input="clearError('subject_name')"
                       placeholder="Subject Name"
                       :class="{'border-red-500 ring-red-500/10': errors.subject_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.subject_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.subject_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student's Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="student_name" 
                       x-model="formData.student_name"
                       @input="clearError('student_name')"
                       placeholder="Student's Name"
                       :class="{'border-red-500 ring-red-500/10': errors.student_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.student_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.student_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                <select name="gender" 
                        x-model="formData.gender"
                        @change="clearError('gender')"
                        :class="{'border-red-500 ring-red-500/10': errors.gender}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Gender</option>
                    @foreach(\App\Constants\Gender::getOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <template x-if="errors.gender">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.gender[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Follow Up Date</label>
                <input type="date" name="follow_up_date" 
                       x-model="formData.follow_up_date"
                       @change="clearError('follow_up_date')"
                       :class="{'border-red-500 ring-red-500/10': errors.follow_up_date}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.follow_up_date">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.follow_up_date[0]"></p>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Father's Details Section --}}
<div class="mb-6">
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
                <input type="text" name="father_name" 
                       x-model="formData.father_name"
                       @input="clearError('father_name')"
                       placeholder="Enter Father's Name"
                       :class="{'border-red-500 ring-red-500/10': errors.father_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Contact No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_contact" 
                       x-model="formData.father_contact"
                       @input="clearError('father_contact')"
                       placeholder="Enter Father contact no"
                       :class="{'border-red-500 ring-red-500/10': errors.father_contact}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_contact">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_contact[0]"></p>
                </template>
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
                <input type="email" name="father_email" 
                       x-model="formData.father_email"
                       @input="clearError('father_email')"
                       placeholder="Enter Father Email id"
                       :class="{'border-red-500 ring-red-500/10': errors.father_email}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_email">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_email[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Qualification</label>
                <input type="text" name="father_qualification" 
                       x-model="formData.father_qualification"
                       @input="clearError('father_qualification')"
                       placeholder="Enter Father qualification"
                       :class="{'border-red-500 ring-red-500/10': errors.father_qualification}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_qualification">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_qualification[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Occupation</label>
                <input type="text" name="father_occupation" 
                       x-model="formData.father_occupation"
                       @input="clearError('father_occupation')"
                       placeholder="Enter Father Occupation"
                       :class="{'border-red-500 ring-red-500/10': errors.father_occupation}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_occupation">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_occupation[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Annual Income</label>
                <input type="number" step="0.01" name="father_annual_income" 
                       x-model="formData.father_annual_income"
                       @input="clearError('father_annual_income')"
                       placeholder="Enter Father Annual Income"
                       :class="{'border-red-500 ring-red-500/10': errors.father_annual_income}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_annual_income">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_annual_income[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization</label>
                <input type="text" name="father_organization" 
                       x-model="formData.father_organization"
                       @input="clearError('father_organization')"
                       placeholder="Organization"
                       :class="{'border-red-500 ring-red-500/10': errors.father_organization}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_organization">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_organization[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Father Office Address</label>
                <input type="text" name="father_office_address" 
                       x-model="formData.father_office_address"
                       @input="clearError('father_office_address')"
                       placeholder="Enter Father Office Address"
                       :class="{'border-red-500 ring-red-500/10': errors.father_office_address}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_office_address">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_office_address[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                <input type="text" name="father_department" 
                       x-model="formData.father_department"
                       @input="clearError('father_department')"
                       placeholder="Enter Department"
                       :class="{'border-red-500 ring-red-500/10': errors.father_department}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_department">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_department[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                <input type="text" name="father_designation" 
                       x-model="formData.father_designation"
                       @input="clearError('father_designation')"
                       placeholder="Enter Designation"
                       :class="{'border-red-500 ring-red-500/10': errors.father_designation}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.father_designation">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.father_designation[0]"></p>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Mother's Details Section --}}
<div class="mb-6">
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
                <input type="text" name="mother_name" 
                       x-model="formData.mother_name"
                       @input="clearError('mother_name')"
                       placeholder="Enter mother's Name"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Contact No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_contact" 
                       x-model="formData.mother_contact"
                       @input="clearError('mother_contact')"
                       placeholder="Enter Mother contact no"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_contact}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_contact">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_contact[0]"></p>
                </template>
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
                <input type="email" name="mother_email" 
                       x-model="formData.mother_email"
                       @input="clearError('mother_email')"
                       placeholder="Enter Mother Email id"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_email}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_email">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_email[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Qualification</label>
                <input type="text" name="mother_qualification" 
                       x-model="formData.mother_qualification"
                       @input="clearError('mother_qualification')"
                       placeholder="Enter Mother qualification"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_qualification}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_qualification">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_qualification[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Occupation</label>
                <input type="text" name="mother_occupation" 
                       x-model="formData.mother_occupation"
                       @input="clearError('mother_occupation')"
                       placeholder="Enter Mother Occupation"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_occupation}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_occupation">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_occupation[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Annual Income</label>
                <input type="number" step="0.01" name="mother_annual_income" 
                       x-model="formData.mother_annual_income"
                       @input="clearError('mother_annual_income')"
                       placeholder="Enter Mother Annual Income"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_annual_income}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_annual_income">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_annual_income[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization</label>
                <input type="text" name="mother_organization" 
                       x-model="formData.mother_organization"
                       @input="clearError('mother_organization')"
                       placeholder="Organization"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_organization}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_organization">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_organization[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother Office Address</label>
                <input type="text" name="mother_office_address" 
                       x-model="formData.mother_office_address"
                       @input="clearError('mother_office_address')"
                       placeholder="Enter Mother Office Address"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_office_address}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_office_address">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_office_address[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                <input type="text" name="mother_department" 
                       x-model="formData.mother_department"
                       @input="clearError('mother_department')"
                       placeholder="Enter Department"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_department}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_department">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_department[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                <input type="text" name="mother_designation" 
                       x-model="formData.mother_designation"
                       @input="clearError('mother_designation')"
                       placeholder="Enter Designation"
                       :class="{'border-red-500 ring-red-500/10': errors.mother_designation}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.mother_designation">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.mother_designation[0]"></p>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Contact Details Section --}}
<div class="mb-6">
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
                <input type="text" name="contact_no" 
                       x-model="formData.contact_no"
                       @input="clearError('contact_no')"
                       placeholder="Enter Contact no"
                       :class="{'border-red-500 ring-red-500/10': errors.contact_no}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.contact_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.contact_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Whatsapp No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="whatsapp_no" 
                       x-model="formData.whatsapp_no"
                       @input="clearError('whatsapp_no')"
                       placeholder="Enter whatsapp  no"
                       :class="{'border-red-500 ring-red-500/10': errors.whatsapp_no}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.whatsapp_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.whatsapp_no[0]"></p>
                </template>
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
                <input type="text" name="facebook_id" 
                       x-model="formData.facebook_id"
                       @input="clearError('facebook_id')"
                       placeholder="Enter Facebook Id"
                       :class="{'border-red-500 ring-red-500/10': errors.facebook_id}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.facebook_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.facebook_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Id</label>
                <input type="email" name="email_id" 
                       x-model="formData.email_id"
                       @input="clearError('email_id')"
                       placeholder="Enter Email id"
                       :class="{'border-red-500 ring-red-500/10': errors.email_id}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.email_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.email_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMS No</label>
                <input type="text" name="sms_no" 
                       x-model="formData.sms_no"
                       @input="clearError('sms_no')"
                       placeholder="Enter SMS no"
                       :class="{'border-red-500 ring-red-500/10': errors.sms_no}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.sms_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.sms_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Twitter Id</label>
                <input type="text" name="twitter_id" 
                       x-model="formData.twitter_id"
                       @input="clearError('twitter_id')"
                       placeholder="Enter Twitter Id"
                       :class="{'border-red-500 ring-red-500/10': errors.twitter_id}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.twitter_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.twitter_id[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Emergency Contact No</label>
                <input type="text" name="emergency_contact_no" 
                       x-model="formData.emergency_contact_no"
                       @input="clearError('emergency_contact_no')"
                       placeholder="Emergency Contact no"
                       :class="{'border-red-500 ring-red-500/10': errors.emergency_contact_no}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.emergency_contact_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.emergency_contact_no[0]"></p>
                </template>
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
                <input type="date" name="dob" 
                       x-model="formData.dob"
                       @change="clearError('dob')"
                       :class="{'border-red-500 ring-red-500/10': errors.dob}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.dob">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.dob[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aadhaar No</label>
                <input type="text" name="aadhaar_no" 
                       x-model="formData.aadhaar_no"
                       @input="clearError('aadhaar_no')"
                       placeholder="Aadhaar no of the Students"
                       :class="{'border-red-500 ring-red-500/10': errors.aadhaar_no}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.aadhaar_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.aadhaar_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Grand Father Name</label>
                <input type="text" name="grand_father_name" 
                       x-model="formData.grand_father_name"
                       @input="clearError('grand_father_name')"
                       placeholder="Enter Grand father name"
                       :class="{'border-red-500 ring-red-500/10': errors.grand_father_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.grand_father_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.grand_father_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Annual Income</label>
                <input type="number" step="0.01" name="annual_income" 
                       x-model="formData.annual_income"
                       @input="clearError('annual_income')"
                       placeholder="Enter Annual Income"
                       :class="{'border-red-500 ring-red-500/10': errors.annual_income}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.annual_income">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.annual_income[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">No of Brother's</label>
                <input type="number" step="1" name="no_of_brothers" 
                       x-model="formData.no_of_brothers"
                       @input="clearError('no_of_brothers')"
                       placeholder="Choose No of Brother's"
                       :class="{'border-red-500 ring-red-500/10': errors.no_of_brothers}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.no_of_brothers">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.no_of_brothers[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">No of Sister's</label>
                <input type="number" step="1" name="no_of_sisters" 
                       x-model="formData.no_of_sisters"
                       @input="clearError('no_of_sisters')"
                       placeholder="Choose No of Sister's"
                       :class="{'border-red-500 ring-red-500/10': errors.no_of_sisters}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.no_of_sisters">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.no_of_sisters[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select name="category" 
                        x-model="formData.category"
                        @change="clearError('category')"
                        :class="{'border-red-500 ring-red-500/10': errors.category}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Category</option>
                    <option value="General">General</option>
                    <option value="OBC">OBC</option>
                    <option value="SC">SC</option>
                    <option value="ST">ST</option>
                    <option value="Other">Other</option>
                </select>
                <template x-if="errors.category">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.category[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minority</label>
                <select name="minority" 
                        x-model="formData.minority"
                        @change="clearError('minority')"
                        :class="{'border-red-500 ring-red-500/10': errors.minority}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Minority</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <template x-if="errors.minority">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.minority[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Religion</label>
                <select name="religion" 
                        x-model="formData.religion"
                        @change="clearError('religion')"
                        :class="{'border-red-500 ring-red-500/10': errors.religion}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Religion</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Muslim">Muslim</option>
                    <option value="Christian">Christian</option>
                    <option value="Sikh">Sikh</option>
                    <option value="Other">Other</option>
                </select>
                <template x-if="errors.religion">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.religion[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transport Facility</label>
                <select name="transport_facility" 
                        x-model="formData.transport_facility"
                        @change="clearError('transport_facility')"
                        :class="{'border-red-500 ring-red-500/10': errors.transport_facility}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Transport Facility</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <template x-if="errors.transport_facility">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.transport_facility[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hostel Facility</label>
                <select name="hostel_facility" 
                        x-model="formData.hostel_facility"
                        @change="clearError('hostel_facility')"
                        :class="{'border-red-500 ring-red-500/10': errors.hostel_facility}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Choose Hostel Facility</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <template x-if="errors.hostel_facility">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.hostel_facility[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Previous Class</label>
                <input type="text" name="previous_class" 
                       x-model="formData.previous_class"
                       @input="clearError('previous_class')"
                       placeholder="Choose Previous Class"
                       :class="{'border-red-500 ring-red-500/10': errors.previous_class}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.previous_class">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.previous_class[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Identity Marks</label>
                <textarea name="identity_marks" rows="3" 
                          x-model="formData.identity_marks"
                          @input="clearError('identity_marks')"
                          placeholder="Enter Identity Marks"
                          :class="{'border-red-500 ring-red-500/10': errors.identity_marks}"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"></textarea>
                <template x-if="errors.identity_marks">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.identity_marks[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permanent Address</label>
                <textarea name="permanent_address" rows="3" 
                          x-model="formData.permanent_address"
                          @input="clearError('permanent_address')"
                          placeholder="Enter Permanent Address"
                          :class="{'border-red-500 ring-red-500/10': errors.permanent_address}"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"></textarea>
                <template x-if="errors.permanent_address">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.permanent_address[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country <span class="text-red-500">*</span></label>
                <select name="country_id" 
                        x-model="formData.country_id"
                        @change="clearError('country_id')"
                        :class="{'border-red-500 ring-red-500/10': errors.country_id}"
                        class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.country_id">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.country_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Previous School Name</label>
                <input type="text" name="previous_school_name" 
                       x-model="formData.previous_school_name"
                       @input="clearError('previous_school_name')"
                       placeholder="Previous School Name"
                       :class="{'border-red-500 ring-red-500/10': errors.previous_school_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.previous_school_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.previous_school_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student's Roll No</label>
                <input type="text" name="student_roll_no" 
                       x-model="formData.student_roll_no"
                       @input="clearError('student_roll_no')"
                       placeholder="Student's Roll No"
                       :class="{'border-red-500 ring-red-500/10': errors.student_roll_no}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.student_roll_no">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.student_roll_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Passing Year</label>
                <input type="number" name="passing_year" 
                       x-model="formData.passing_year"
                       @input="clearError('passing_year')"
                       placeholder="Passing Year" 
                       min="1950" max="{{ date('Y') + 20 }}" step="1"
                       :class="{'border-red-500 ring-red-500/10': errors.passing_year}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.passing_year">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.passing_year[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name Of The Exam</label>
                <input type="text" name="exam_name" 
                       x-model="formData.exam_name"
                       @input="clearError('exam_name')"
                       placeholder="Name Of The Exam"
                       :class="{'border-red-500 ring-red-500/10': errors.exam_name}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.exam_name">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.exam_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Board / University Name</label>
                <input type="text" name="board_university" 
                       x-model="formData.board_university"
                       @input="clearError('board_university')"
                       placeholder="Board / University Name"
                       :class="{'border-red-500 ring-red-500/10': errors.board_university}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <template x-if="errors.board_university">
                    <p class="text-red-500 text-xs mt-1" x-text="errors.board_university[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="only_child" value="1" 
                           x-model="formData.only_child"
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
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border-2 border-dashed border-gray-300 dark:border-gray-500">
                        <img id="father-photo-preview" src="#" alt="Father's Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user text-gray-400 text-4xl" id="father-photo-icon"></i>
                        <button type="button" 
                                id="father-photo-remove" 
                                @click.prevent="removePhoto('father_photo', 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="father_photo" accept="image/*" 
                           @change="previewPhoto($event, 'father-photo-preview', 'father-photo-icon', 'father-photo-remove'); clearError('father_photo')"
                           :class="{'ring-red-500/50': errors.father_photo}"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <template x-if="errors.father_photo">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.father_photo[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mother's Photo</label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border-2 border-dashed border-gray-300 dark:border-gray-500">
                        <img id="mother-photo-preview" src="#" alt="Mother's Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user text-gray-400 text-4xl" id="mother-photo-icon"></i>
                        <button type="button" 
                                id="mother-photo-remove" 
                                @click.prevent="removePhoto('mother_photo', 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="mother_photo" accept="image/*" 
                           @change="previewPhoto($event, 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove'); clearError('mother_photo')"
                           :class="{'ring-red-500/50': errors.mother_photo}"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <template x-if="errors.mother_photo">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.mother_photo[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student Photo</label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border-2 border-dashed border-gray-300 dark:border-gray-500">
                        <img id="student-photo-preview" src="#" alt="Student Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user text-gray-400 text-4xl" id="student-photo-icon"></i>
                        <button type="button" 
                                id="student-photo-remove" 
                                @click.prevent="removePhoto('student_photo', 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="student_photo" accept="image/*" 
                           @change="previewPhoto($event, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove'); clearError('student_photo')"
                           :class="{'ring-red-500/50': errors.student_photo}"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <template x-if="errors.student_photo">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.student_photo[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
