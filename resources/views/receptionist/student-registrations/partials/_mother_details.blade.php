{{-- Mother's Details --}}
<div class="mb-6" x-data="{ motherExpanded: true }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="motherExpanded = !motherExpanded">
        <span>Mother's Details</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': motherExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700" x-show="motherExpanded" x-collapse>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Name (If Staff)
                </label>
                <select name="mother_staff_id" @change="delete errors.mother_staff_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.mother_staff_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Mother Name (If Staff)</option>
                    {{-- Populate from staff table if needed --}}
                </select>
                <template x-if="errors.mother_staff_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_staff_id[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Initial
                </label>
                <select name="mother_name_prefix" @change="delete errors.mother_name_prefix"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.mother_name_prefix ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="Mrs" {{ (old('mother_name_prefix', $studentRegistration->mother_name_prefix ?? '') == 'Mrs') ? 'selected' : '' }}>Mrs</option>
                    <option value="Dr" {{ (old('mother_name_prefix', $studentRegistration->mother_name_prefix ?? '') == 'Dr') ? 'selected' : '' }}>Dr</option>
                    <option value="Ms" {{ (old('mother_name_prefix', $studentRegistration->mother_name_prefix ?? '') == 'Ms') ? 'selected' : '' }}>Ms</option>
                    <option value="Late" {{ (old('mother_name_prefix', $studentRegistration->mother_name_prefix ?? '') == 'Late') ? 'selected' : '' }}>Late</option>
                </select>
                <template x-if="errors.mother_name_prefix">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_name_prefix[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_first_name" value="{{ old('mother_first_name', $studentRegistration->mother_first_name ?? '') }}"  placeholder="Enter First Name"
                       @input="delete errors.mother_first_name"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_first_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_first_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_first_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="mother_middle_name" value="{{ old('mother_middle_name', $studentRegistration->mother_middle_name ?? '') }}" placeholder="Enter Middle Name"
                       @input="delete errors.mother_middle_name"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_middle_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_middle_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_middle_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_last_name" value="{{ old('mother_last_name', $studentRegistration->mother_last_name ?? '') }}"  placeholder="Enter Last Name"
                       @input="delete errors.mother_last_name"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_last_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_last_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_last_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="mother_email" value="{{ old('mother_email', $studentRegistration->mother_email ?? '') }}" placeholder="Enter Email Id"
                       @input="delete errors.mother_email"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_email">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_email[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile Number <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="mother_mobile_no" value="{{ old('mother_mobile_no', $studentRegistration->mother_mobile_no ?? '') }}" placeholder="Enter Mobile Number" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       @input="delete errors.mother_mobile_no"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_mobile_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_mobile_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_mobile_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Landline Number
                </label>
                <input type="text" name="mother_landline_no" value="{{ old('mother_landline_no', $studentRegistration->mother_landline_no ?? '') }}" placeholder="Enter Landline Number"
                       @input="delete errors.mother_landline_no"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_landline_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_landline_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_landline_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Occupation/Profession
                </label>
                <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $studentRegistration->mother_occupation ?? '') }}" placeholder="Enter Occupation/Profession"
                       @input="delete errors.mother_occupation"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_occupation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_occupation">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_occupation[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Organization
                </label>
                <input type="text" name="mother_organization" value="{{ old('mother_organization', $studentRegistration->mother_organization ?? '') }}" placeholder="Enter Organization"
                       @input="delete errors.mother_organization"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_organization ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_organization">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_organization[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Office Address
                </label>
                <input type="text" name="mother_office_address" value="{{ old('mother_office_address', $studentRegistration->mother_office_address ?? '') }}" placeholder="Enter Office Address"
                       @input="delete errors.mother_office_address"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_office_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_office_address">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_office_address[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Qualification
                </label>
                <select name="mother_qualification" @change="delete errors.mother_qualification"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.mother_qualification ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Qualification</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->name }}" {{ (old('mother_qualification', $studentRegistration->mother_qualification ?? '') == $qual->name) ? 'selected' : '' }}>{{ $qual->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.mother_qualification">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_qualification[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Department
                </label>
                <input type="text" name="mother_department" value="{{ old('mother_department', $studentRegistration->mother_department ?? '') }}" placeholder="Enter Department"
                       @input="delete errors.mother_department"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_department ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_department">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_department[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Designation
                </label>
                <input type="text" name="mother_designation" value="{{ old('mother_designation', $studentRegistration->mother_designation ?? '') }}" placeholder="Enter Designation"
                       @input="delete errors.mother_designation"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_designation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_designation">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_designation[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Aadhar No
                </label>
                <input type="text" name="mother_aadhar_no" value="{{ old('mother_aadhar_no', $studentRegistration->mother_aadhar_no ?? '') }}" placeholder="Enter Mother Aadhar No"
                       @input="delete errors.mother_aadhar_no"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_aadhar_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_aadhar_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_aadhar_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Annual Income
                </label>
                <input type="number" step="0.01" name="mother_annual_income" value="{{ old('mother_annual_income', $studentRegistration->mother_annual_income ?? '') }}" placeholder="Enter Annual Income"
                       @input="delete errors.mother_annual_income"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_annual_income ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_annual_income">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_annual_income[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Age
                </label>
                <input type="number" step="0.01" name="mother_age" value="{{ old('mother_age', $studentRegistration->mother_age ?? '') }}" placeholder="Enter Mother Age"
                       @input="delete errors.mother_age"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_age ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_age">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_age[0]"></p>
                </template>
            </div>
        </div>
    </div>
</div>
