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
                <input type="text" name="first_name" value="{{ old('first_name', $studentRegistration->first_name ?? '') }}"  placeholder="Enter First Name"
                       class="w-full px-4 py-2 border @error('first_name') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('first_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $studentRegistration->middle_name ?? '') }}" placeholder="Enter Middle Name"
                       class="w-full px-4 py-2 border @error('middle_name') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('middle_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="last_name" value="{{ old('last_name', $studentRegistration->last_name ?? '') }}"  placeholder="Enter Last Name"
                       class="w-full px-4 py-2 border @error('last_name') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('last_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gender <span class="text-red-500">*</span>
                </label>
                <select name="gender"  
                        class="w-full px-4 py-2 border @error('gender') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Gender</option>
                    @foreach(\App\Constants\Gender::getOptions() as $value => $label)
                        @php
                            $currentGender = old('gender', $studentRegistration->gender ?? '');
                            $currentValue = $currentGender instanceof \App\Enums\Gender ? $currentGender->value : $currentGender;
                        @endphp
                        <option value="{{ $value }}" {{ $currentValue == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date of Birth
                </label>
                <input type="date" name="dob" value="{{ old('dob', isset($studentRegistration->dob) ? $studentRegistration->dob->format('Y-m-d') : '') }}"
                       class="w-full px-4 py-2 border @error('dob') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('dob')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="email" value="{{ old('email', $studentRegistration->email ?? '') }}" placeholder="Enter Email Id"
                       class="w-full px-4 py-2 border @error('email') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile No <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="mobile_no" value="{{ old('mobile_no', $studentRegistration->mobile_no ?? '') }}"  placeholder="Enter Mobile No" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       class="w-full px-4 py-2 border @error('mobile_no') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('mobile_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Type
                </label>
                <select name="student_type" 
                        class="w-full px-4 py-2 border @error('student_type') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Student Type</option>
                    @foreach($studentTypes as $type)
                        <option value="{{ $type->name }}" {{ (old('student_type', $studentRegistration->student_type ?? '') == $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('student_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Aadhar No
                </label>
                <input type="text" name="aadhar_no" value="{{ old('aadhar_no', $studentRegistration->aadhar_no ?? '') }}" placeholder="Enter Aadhaar No"
                       class="w-full px-4 py-2 border @error('aadhar_no') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('aadhar_no')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Place of Birth
                </label>
                <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $studentRegistration->place_of_birth ?? '') }}" placeholder="Enter Place of Birth"
                       class="w-full px-4 py-2 border @error('place_of_birth') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('place_of_birth')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nationality <span class="text-red-500">*</span>
                </label>
                <select name="nationality"  
                        class="w-full px-4 py-2 border @error('nationality') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="Indian" {{ (old('nationality', $studentRegistration->nationality ?? 'Indian') == 'Indian') ? 'selected' : '' }}>Indian</option>
                    <option value="Nepal" {{ (old('nationality', $studentRegistration->nationality ?? '') == 'Nepal') ? 'selected' : '' }}>Nepal</option>
                    <option value="Pakistan" {{ (old('nationality', $studentRegistration->nationality ?? '') == 'Pakistan') ? 'selected' : '' }}>Pakistan</option>
                </select>
                @error('nationality')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Religion <span class="text-red-500">*</span>
                </label>
                <select name="religion" 
                        class="w-full px-4 py-2 border @error('religion') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Religion</option>
                    @foreach($religions as $rel)
                        <option value="{{ $rel->name }}" {{ (old('religion', $studentRegistration->religion ?? '') == $rel->name) ? 'selected' : '' }}>{{ $rel->name }}</option>
                    @endforeach
                </select>
                @error('religion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="category" 
                        class="w-full px-4 py-2 border @error('category') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ (old('category', $studentRegistration->category ?? '') == $cat->name) ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Special Needs If any
                </label>
                <input type="text" name="special_needs" value="{{ old('special_needs', $studentRegistration->special_needs ?? '') }}" placeholder="Enter Special Needs if any"
                       class="w-full px-4 py-2 border @error('special_needs') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('special_needs')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Tongue
                </label>
                <input type="text" name="mother_tongue" value="{{ old('mother_tongue', $studentRegistration->mother_tongue ?? '') }}" placeholder="Enter Mother Tongue"
                       class="w-full px-4 py-2 border @error('mother_tongue') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('mother_tongue')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Remarks
                </label>
                <input type="text" name="remarks" value="{{ old('remarks', $studentRegistration->remarks ?? '') }}" placeholder="Enter Remarks"
                       class="w-full px-4 py-2 border @error('remarks') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('remarks')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Number of Brothers
                </label>
                <input type="number" step="0.01" name="number_of_brothers" value="{{ old('number_of_brothers', $studentRegistration->number_of_brothers ?? 0) }}"
                       class="w-full px-4 py-2 border @error('number_of_brothers') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('number_of_brothers')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Number of Sisters
                </label>
                <input type="number" step="0.01" name="number_of_sisters" value="{{ old('number_of_sisters', $studentRegistration->number_of_sisters ?? 0) }}"
                       class="w-full px-4 py-2 border @error('number_of_sisters') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('number_of_sisters')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Is Single Parent
                </label>
                <select name="is_single_parent" 
                        class="w-full px-4 py-2 border @error('is_single_parent') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="0" {{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
                @error('is_single_parent')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Corresponding Relative
                </label>
                <select name="corresponding_relative" 
                        class="w-full px-4 py-2 border @error('corresponding_relative') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Corresponding Relative</option>
                    @foreach($correspondingRelatives as $rel)
                        <option value="{{ $rel->name }}" {{ (old('corresponding_relative', $studentRegistration->corresponding_relative ?? '') == $rel->name) ? 'selected' : '' }}>{{ $rel->name }}</option>
                    @endforeach
                </select>
                @error('corresponding_relative')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Is Transport Required
                </label>
                <select name="is_transport_required" 
                        class="w-full px-4 py-2 border @error('is_transport_required') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="0" {{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
                @error('is_transport_required')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bus / Van Req.
                </label>
                <select name="bus_stop" 
                        class="w-full px-4 py-2 border @error('bus_stop') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Bus Stop</option>
                    {{-- This would normally be populated from a transport module --}}
                </select>
                @error('bus_stop')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Other Stop
                </label>
                <input type="text" name="other_stop" value="{{ old('other_stop', $studentRegistration->other_stop ?? '') }}" placeholder="Enter Other Stop"
                       class="w-full px-4 py-2 border @error('other_stop') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('other_stop')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Boarding Type
                </label>
                <select name="boarding_type" 
                        class="w-full px-4 py-2 border @error('boarding_type') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Boarding Type</option>
                    @foreach($boardingTypes as $type)
                        <option value="{{ $type->name }}" {{ (old('boarding_type', $studentRegistration->boarding_type ?? '') == $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('boarding_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
