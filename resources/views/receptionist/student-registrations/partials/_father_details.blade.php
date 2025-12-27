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
                <select name="father_staff_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Father Name (If Staff)</option>
                    {{-- Populate from staff table if needed --}}
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Initial <span class="text-red-500">*</span>
                </label>
                <select name="father_name_prefix" class="w-full px-4 py-2 border {{ $errors->has('father_name_prefix') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="Mr" {{ (old('father_name_prefix', $studentRegistration->father_name_prefix ?? '') == 'Mr') ? 'selected' : '' }}>Mr</option>
                    <option value="Dr" {{ (old('father_name_prefix', $studentRegistration->father_name_prefix ?? '') == 'Dr') ? 'selected' : '' }}>Dr</option>
                    <option value="Late" {{ (old('father_name_prefix', $studentRegistration->father_name_prefix ?? '') == 'Late') ? 'selected' : '' }}>Late</option>
                </select>
                @error('father_name_prefix')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_first_name" value="{{ old('father_first_name', $studentRegistration->father_first_name ?? '') }}" placeholder="Enter First Name"
                       class="w-full px-4 py-2 border {{ $errors->has('father_first_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('father_first_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="father_middle_name" value="{{ old('father_middle_name', $studentRegistration->father_middle_name ?? '') }}" placeholder="Enter Middle Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="father_last_name" value="{{ old('father_last_name', $studentRegistration->father_last_name ?? '') }}" placeholder="Enter Last Name"
                       class="w-full px-4 py-2 border {{ $errors->has('father_last_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('father_last_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="father_email" value="{{ old('father_email', $studentRegistration->father_email ?? '') }}" placeholder="Enter Email Id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile Number <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="father_mobile_no" value="{{ old('father_mobile_no', $studentRegistration->father_mobile_no ?? '') }}" placeholder="Enter Mobile Number" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       class="w-full px-4 py-2 border {{ $errors->has('father_mobile_no') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('father_mobile_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Landline Number
                </label>
                <input type="text" name="father_landline_no" value="{{ old('father_landline_no', $studentRegistration->father_landline_no ?? '') }}" placeholder="Enter Landline Number"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Occupation/Profession
                </label>
                <input type="text" name="father_occupation" value="{{ old('father_occupation', $studentRegistration->father_occupation ?? '') }}" placeholder="Enter Occupation/Profession"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Organization
                </label>
                <input type="text" name="father_organization" value="{{ old('father_organization', $studentRegistration->father_organization ?? '') }}" placeholder="Enter Organization"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Office Address
                </label>
                <input type="text" name="father_office_address" value="{{ old('father_office_address', $studentRegistration->father_office_address ?? '') }}" placeholder="Enter Office Address"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Qualification
                </label>
                <select name="father_qualification" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Qualification</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->name }}" {{ (old('father_qualification', $studentRegistration->father_qualification ?? '') == $qual->name) ? 'selected' : '' }}>{{ $qual->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Department
                </label>
                <input type="text" name="father_department" value="{{ old('father_department', $studentRegistration->father_department ?? '') }}" placeholder="Enter Department"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Designation
                </label>
                <input type="text" name="father_designation" value="{{ old('father_designation', $studentRegistration->father_designation ?? '') }}" placeholder="Enter Designation"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Aadhar No
                </label>
                <input type="text" name="father_aadhar_no" value="{{ old('father_aadhar_no', $studentRegistration->father_aadhar_no ?? '') }}" placeholder="Enter Father Aadhar No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Annual Income
                </label>
                <input type="number" step="0.01" name="father_annual_income" value="{{ old('father_annual_income', $studentRegistration->father_annual_income ?? '') }}" placeholder="Enter Annual Income"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Age
                </label>
                <input type="number" step="0.01" name="father_age" value="{{ old('father_age', $studentRegistration->father_age ?? '') }}" placeholder="Enter Father Age"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    </div>
</div>
