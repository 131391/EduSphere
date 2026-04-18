@php
    use App\Enums\Gender;
@endphp
{{-- Personal Information --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between">
        <span>Personal Information</span>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Always Visible: First Row --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" x-model="formData.first_name" @input="clearError('first_name')" placeholder="Enter First Name"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.first_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <template x-if="errors.first_name">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Middle Name
                    </label>
                    <input type="text" name="middle_name" x-model="formData.middle_name" placeholder="Enter Middle Name"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" x-model="formData.last_name" @input="clearError('last_name')" placeholder="Enter Last Name"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.last_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <template x-if="errors.last_name">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                    </template>
                </div>
            </div>
        </div>

        <div class="px-6 pb-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select name="gender" x-model="formData.gender" @change="clearError('gender')" class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.gender ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Date of Birth <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_of_birth" x-model="formData.date_of_birth" @input="clearError('date_of_birth')"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                               :class="errors.date_of_birth ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.date_of_birth">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.date_of_birth[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Id
                        </label>
                        <input type="email" name="email" x-model="formData.email" placeholder="Enter Email Id"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mobile No <span class="text-red-500">*</span>
                        </label>
                        <x-phone-input 
                            name="phone" 
                            x-model="formData.phone"
                            @input="clearError('phone')"
                            :value="isset($student) ? $student->phone : ''" 
                        />
                        <template x-if="errors.phone">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Student Type
                        </label>
                        <select name="student_type" x-model="formData.student_type" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Student Type</option>
                            @foreach($studentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Blood Group
                        </label>
                        <select name="blood_group" x-model="formData.blood_group" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Blood Group</option>
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->name }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            DOB Certificate No
                        </label>
                        <input type="text" name="dob_certificate_no" value="{{ old('dob_certificate_no', isset($student) ? $student->dob_certificate_no : '') }}" placeholder="Enter DOB Certificate No"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Aadhaar No
                        </label>
                        <input type="text" name="aadhaar_no" value="{{ old('aadhaar_no', isset($student) ? $student->aadhaar_no : '') }}" placeholder="Enter Aadhaar No"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Place of Birth
                        </label>
                        <input type="text" name="place_of_birth" value="{{ old('place_of_birth', isset($student) ? $student->place_of_birth : '') }}" placeholder="Enter Place of Birth"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nationality
                        </label>
                        <input type="text" name="nationality" x-model="formData.nationality" @input="clearError('nationality')"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Religion
                        </label>
                        <select name="religion" x-model="formData.religion" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Religion</option>
                            @foreach($religions as $religion)
                                <option value="{{ $religion->name }}">{{ $religion->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Category
                        </label>
                        <select name="category" x-model="formData.category" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mother Tongue
                        </label>
                        <input type="text" name="mother_tongue" x-model="formData.mother_tongue" placeholder="Enter Mother Tongue"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Special Needs if any
                        </label>
                        <input type="text" name="special_needs" value="{{ old('special_needs', isset($student) ? $student->special_needs : '') }}" placeholder="Enter Special Needs if any"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Corresponding Relative
                        </label>
                        <select name="corresponding_relative" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Corresponding Relative</option>
                            @foreach($correspondingRelatives as $relative)
                                <option value="{{ $relative->name }}" {{ old('corresponding_relative', isset($student) ? $student->corresponding_relative : '') == $relative->name ? 'selected' : '' }}>{{ $relative->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
