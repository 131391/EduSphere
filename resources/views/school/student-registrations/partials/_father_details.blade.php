{{-- Father's Details --}}
<div class="mb-6" x-data="{ fatherExpanded: true }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="fatherExpanded = !fatherExpanded">
        <span>Father's Details</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': fatherExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700" x-show="fatherExpanded" x-collapse>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Name (If Staff)
                </label>
                <select name="father_staff_id" x-model="formData.father_staff_id" @change="clearError('father_staff_id')"
                        class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.father_staff_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Father Name (If Staff)</option>
                    {{-- Populate from staff table if needed --}}
                </select>
                <template x-if="errors.father_staff_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_staff_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Initial
                </label>
                <select name="father_name_prefix" x-model="formData.father_name_prefix" @change="clearError('father_name_prefix')"
                        class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.father_name_prefix ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="Mr">Mr</option>
                    <option value="Dr">Dr</option>
                    <option value="Late">Late</option>
                </select>
                <template x-if="errors.father_name_prefix">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_name_prefix[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_first_name" x-model="formData.father_first_name" placeholder="Enter First Name"
                       @input="clearError('father_first_name')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_first_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_first_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_first_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="father_middle_name" x-model="formData.father_middle_name" placeholder="Enter Middle Name"
                       @input="clearError('father_middle_name')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_middle_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_middle_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_middle_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_last_name" x-model="formData.father_last_name" placeholder="Enter Last Name"
                       @input="clearError('father_last_name')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_last_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_last_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_last_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="father_email" x-model="formData.father_email" placeholder="Enter Email Id"
                       @input="clearError('father_email')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_email">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_email[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile Number <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="father_mobile_no" x-model="formData.father_mobile_no" placeholder="Enter Mobile Number" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       @input="clearError('father_mobile_no')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_mobile_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_mobile_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_mobile_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Landline Number
                </label>
                <input type="text" name="father_landline_no" x-model="formData.father_landline_no" placeholder="Enter Landline Number"
                       @input="clearError('father_landline_no')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_landline_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_landline_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_landline_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Occupation/Profession
                </label>
                <input type="text" name="father_occupation" x-model="formData.father_occupation" placeholder="Enter Occupation/Profession"
                       @input="clearError('father_occupation')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_occupation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_occupation">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_occupation[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Organization
                </label>
                <input type="text" name="father_organization" x-model="formData.father_organization" placeholder="Enter Organization"
                       @input="clearError('father_organization')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_organization ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_organization">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_organization[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Office Address
                </label>
                <input type="text" name="father_office_address" x-model="formData.father_office_address" placeholder="Enter Office Address"
                       @input="clearError('father_office_address')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_office_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_office_address">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_office_address[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Qualification
                </label>
                <select name="father_qualification" x-model="formData.father_qualification" @change="clearError('father_qualification')"
                        class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.father_qualification ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Qualification</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->name }}">{{ $qual->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.father_qualification">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_qualification[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Department
                </label>
                <input type="text" name="father_department" x-model="formData.father_department" placeholder="Enter Department"
                       @input="clearError('father_department')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_department ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_department">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_department[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Designation
                </label>
                <input type="text" name="father_designation" x-model="formData.father_designation" placeholder="Enter Designation"
                       @input="clearError('father_designation')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_designation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_designation">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_designation[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Aadhaar No
                </label>
                <input type="text" name="father_aadhaar_no" x-model="formData.father_aadhaar_no" placeholder="Enter Father Aadhaar No"
                       @input="clearError('father_aadhaar_no')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_aadhaar_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_aadhaar_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_aadhaar_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Annual Income
                </label>
                <input type="number" step="0.01" name="father_annual_income" x-model="formData.father_annual_income" placeholder="Enter Annual Income"
                       @input="clearError('father_annual_income')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_annual_income ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_annual_income">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_annual_income[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Age
                </label>
                <input type="number" step="0.01" name="father_age" x-model="formData.father_age" placeholder="Enter Father Age"
                       @input="clearError('father_age')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.father_age ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.father_age">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_age[0]"></p>
                </template>
            </div>
        </div>
    </div>
</div>
