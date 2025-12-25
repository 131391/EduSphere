{{-- Personal Information --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Personal Information
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="first_name" value="{{ old('first_name', $studentRegistration->first_name ?? '') }}" required placeholder="Enter First Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $studentRegistration->middle_name ?? '') }}" placeholder="Enter Middle Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="last_name" value="{{ old('last_name', $studentRegistration->last_name ?? '') }}" required placeholder="Enter Last Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gender <span class="text-red-500">*</span>
                </label>
                <select name="gender" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Gender</option>
                    @foreach(\App\Constants\Gender::getOptions() as $value => $label)
                        @php
                            $currentGender = old('gender', $studentRegistration->gender ?? '');
                            $currentValue = $currentGender instanceof \App\Enums\Gender ? $currentGender->value : $currentGender;
                        @endphp
                        <option value="{{ $value }}" {{ $currentValue == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date of Birth
                </label>
                <input type="date" name="dob" value="{{ old('dob', isset($studentRegistration->dob) ? $studentRegistration->dob->format('Y-m-d') : '') }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="email" value="{{ old('email', $studentRegistration->email ?? '') }}" placeholder="Enter Email Id"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="mobile_no" value="{{ old('mobile_no', $studentRegistration->mobile_no ?? '') }}" required placeholder="Enter Mobile No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Type
                </label>
                <select name="student_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Student Type</option>
                    @foreach($studentTypes as $type)
                        <option value="{{ $type->name }}" {{ (old('student_type', $studentRegistration->student_type ?? '') == $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Blood Group
                </label>
                <select name="blood_group" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Blood Group</option>
                    @foreach($bloodGroups as $bg)
                        <option value="{{ $bg->name }}" {{ (old('blood_group', $studentRegistration->blood_group ?? '') == $bg->name) ? 'selected' : '' }}>{{ $bg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    DOB Certificate No
                </label>
                <input type="text" name="dob_certificate_no" value="{{ old('dob_certificate_no', $studentRegistration->dob_certificate_no ?? '') }}" placeholder="Enter DOB Certificate No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Aadhar No
                </label>
                <input type="text" name="aadhar_no" value="{{ old('aadhar_no', $studentRegistration->aadhar_no ?? '') }}" placeholder="Enter Aadhaar No"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Place of Birth
                </label>
                <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $studentRegistration->place_of_birth ?? '') }}" placeholder="Enter Place of Birth"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nationality <span class="text-red-500">*</span>
                </label>
                <select name="nationality" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="Indian" {{ (old('nationality', $studentRegistration->nationality ?? 'Indian') == 'Indian') ? 'selected' : '' }}>Indian</option>
                    <option value="Nepal" {{ (old('nationality', $studentRegistration->nationality ?? '') == 'Nepal') ? 'selected' : '' }}>Nepal</option>
                    <option value="Pakistan" {{ (old('nationality', $studentRegistration->nationality ?? '') == 'Pakistan') ? 'selected' : '' }}>Pakistan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Religion
                </label>
                <select name="religion" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Religion</option>
                    @foreach($religions as $rel)
                        <option value="{{ $rel->name }}" {{ (old('religion', $studentRegistration->religion ?? '') == $rel->name) ? 'selected' : '' }}>{{ $rel->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Category
                </label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ (old('category', $studentRegistration->category ?? '') == $cat->name) ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Special Needs If any
                </label>
                <input type="text" name="special_needs" value="{{ old('special_needs', $studentRegistration->special_needs ?? '') }}" placeholder="Enter Special Needs if any"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Tongue
                </label>
                <input type="text" name="mother_tongue" value="{{ old('mother_tongue', $studentRegistration->mother_tongue ?? '') }}" placeholder="Enter Mother Tongue"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Remarks
                </label>
                <input type="text" name="remarks" value="{{ old('remarks', $studentRegistration->remarks ?? '') }}" placeholder="Enter Remarks"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Number of Brothers
                </label>
                <input type="number" step="0.01" name="number_of_brothers" value="{{ old('number_of_brothers', $studentRegistration->number_of_brothers ?? 0) }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Number of Sisters
                </label>
                <input type="number" step="0.01" name="number_of_sisters" value="{{ old('number_of_sisters', $studentRegistration->number_of_sisters ?? 0) }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Is Single Parent
                </label>
                <select name="is_single_parent" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="0" {{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Corresponding Relative
                </label>
                <select name="corresponding_relative" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Corresponding Relative</option>
                    @foreach($correspondingRelatives as $rel)
                        <option value="{{ $rel->name }}" {{ (old('corresponding_relative', $studentRegistration->corresponding_relative ?? '') == $rel->name) ? 'selected' : '' }}>{{ $rel->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Is Transport Required
                </label>
                <select name="is_transport_required" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="0" {{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bus Stop
                </label>
                <select name="bus_stop" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Bus Stop</option>
                    {{-- This would normally be populated from a transport module --}}
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Other Stop
                </label>
                <input type="text" name="other_stop" value="{{ old('other_stop', $studentRegistration->other_stop ?? '') }}" placeholder="Enter Other Stop"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Boarding Type
                </label>
                <select name="boarding_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Boarding Type</option>
                    @foreach($boardingTypes as $type)
                        <option value="{{ $type->name }}" {{ (old('boarding_type', $studentRegistration->boarding_type ?? '') == $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
