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
                <select name="mother_staff_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Mother Name (If Staff)</option>
                    {{-- Populate from staff table if needed --}}
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Initial <span class="text-red-500">*</span>
                </label>
                <select name="mother_name_prefix" class="w-full px-4 py-2 border {{ $errors->has('mother_name_prefix') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="Mrs" {{ old('mother_name_prefix') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                    <option value="Ms" {{ old('mother_name_prefix') == 'Ms' ? 'selected' : '' }}>Ms</option>
                    <option value="Dr" {{ old('mother_name_prefix') == 'Dr' ? 'selected' : '' }}>Dr</option>
                    <option value="Late" {{ old('mother_name_prefix') == 'Late' ? 'selected' : '' }}>Late</option>
                </select>
                @error('mother_name_prefix')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_first_name" value="{{ old('mother_first_name', isset($student) ? $student->mother_first_name : '') }}" placeholder="Enter First Name"
                       class="w-full px-4 py-2 border {{ $errors->has('mother_first_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('mother_first_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="mother_middle_name" value="{{ old('mother_middle_name', isset($student) ? $student->mother_middle_name : '') }}" placeholder="Enter Middle Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_last_name" value="{{ old('mother_last_name', isset($student) ? $student->mother_last_name : '') }}" placeholder="Enter Last Name"
                       class="w-full px-4 py-2 border {{ $errors->has('mother_last_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('mother_last_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="mother_email" value="{{ old('mother_email', isset($student) ? $student->mother_email : '') }}" placeholder="Enter Email Id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mother_mobile" value="{{ old('mother_mobile', isset($student) ? $student->mother_mobile : '') }}" placeholder="Enter Mobile No"
                       class="w-full px-4 py-2 border {{ $errors->has('mother_mobile') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('mother_mobile')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Occupation
                </label>
                <input type="text" name="mother_occupation" value="{{ old('mother_occupation', isset($student) ? $student->mother_occupation : '') }}" placeholder="Enter Occupation"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Qualification
                </label>
                <select name="mother_qualification" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Qualification</option>
                    @foreach($qualifications as $qualification)
                        <option value="{{ $qualification->name }}" {{ old('mother_qualification', isset($student) ? $student->mother_qualification : '') == $qualification->name ? 'selected' : '' }}>{{ $qualification->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Annual Income
                </label>
                <input type="number" step="0.01" name="mother_annual_income" value="{{ old('mother_annual_income', isset($student) ? $student->mother_annual_income : '') }}" placeholder="Enter Annual Income"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Aadhaar No
                </label>
                <input type="text" name="mother_aadhaar" value="{{ old('mother_aadhaar', isset($student) ? $student->mother_aadhaar : '') }}" placeholder="Enter Aadhaar No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    PAN No
                </label>
                <input type="text" name="mother_pan" value="{{ old('mother_pan', isset($student) ? $student->mother_pan : '') }}" placeholder="Enter PAN No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    </div>
</div>
