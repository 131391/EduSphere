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
                       @input="delete errors.first_name"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.first_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.first_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.first_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Middle Name
                </label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $studentRegistration->middle_name ?? '') }}" placeholder="Enter Middle Name"
                       @input="delete errors.middle_name"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.middle_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.middle_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.middle_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="last_name" value="{{ old('last_name', $studentRegistration->last_name ?? '') }}"  placeholder="Enter Last Name"
                       @input="delete errors.last_name"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.last_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.last_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.last_name[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gender <span class="text-red-500">*</span>
                </label>
                <select name="gender" @change="delete errors.gender"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.gender ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Gender</option>
                    @foreach(\App\Constants\Gender::getOptions() as $value => $label)
                        @php
                            $currentGender = old('gender', $studentRegistration->gender ?? '');
                            $currentValue = $currentGender instanceof \App\Enums\Gender ? $currentGender->value : $currentGender;
                        @endphp
                        <option value="{{ $value }}" {{ $currentValue == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <template x-if="errors.gender">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.gender[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date of Birth
                </label>
                <input type="date" name="dob" value="{{ old('dob', isset($studentRegistration->dob) ? $studentRegistration->dob->format('Y-m-d') : '') }}"
                       @change="delete errors.dob"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.dob ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.dob">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.dob[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Id
                </label>
                <input type="email" name="email" value="{{ old('email', $studentRegistration->email ?? '') }}" placeholder="Enter Email Id"
                       @input="delete errors.email"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.email">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.email[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile No <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="mobile_no" value="{{ old('mobile_no', $studentRegistration->mobile_no ?? '') }}"  placeholder="Enter Mobile No" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       @input="delete errors.mobile_no"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mobile_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mobile_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mobile_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Type
                </label>
                <select name="student_type" @change="delete errors.student_type"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.student_type ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Student Type</option>
                    @foreach($studentTypes as $type)
                        <option value="{{ $type->name }}" {{ (old('student_type', $studentRegistration->student_type ?? '') == $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.student_type">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_type[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Aadhar No
                </label>
                <input type="text" name="aadhar_no" value="{{ old('aadhar_no', $studentRegistration->aadhar_no ?? '') }}" placeholder="Enter Aadhaar No"
                       @input="delete errors.aadhar_no"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.aadhar_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.aadhar_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.aadhar_no[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Place of Birth
                </label>
                <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $studentRegistration->place_of_birth ?? '') }}" placeholder="Enter Place of Birth"
                       @input="delete errors.place_of_birth"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.place_of_birth ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.place_of_birth">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.place_of_birth[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nationality
                </label>
                <select name="nationality" @change="delete errors.nationality"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.nationality ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="Indian" {{ (old('nationality', $studentRegistration->nationality ?? 'Indian') == 'Indian') ? 'selected' : '' }}>Indian</option>
                    <option value="Nepal" {{ (old('nationality', $studentRegistration->nationality ?? '') == 'Nepal') ? 'selected' : '' }}>Nepal</option>
                    <option value="Pakistan" {{ (old('nationality', $studentRegistration->nationality ?? '') == 'Pakistan') ? 'selected' : '' }}>Pakistan</option>
                </select>
                <template x-if="errors.nationality">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.nationality[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Religion
                </label>
                <select name="religion" @change="delete errors.religion"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.religion ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Religion</option>
                    @foreach($religions as $rel)
                        <option value="{{ $rel->name }}" {{ (old('religion', $studentRegistration->religion ?? '') == $rel->name) ? 'selected' : '' }}>{{ $rel->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.religion">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.religion[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Category
                </label>
                <select name="category" @change="delete errors.category"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.category ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ (old('category', $studentRegistration->category ?? '') == $cat->name) ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.category">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.category[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Special Needs If any
                </label>
                <input type="text" name="special_needs" value="{{ old('special_needs', $studentRegistration->special_needs ?? '') }}" placeholder="Enter Special Needs if any"
                       @input="delete errors.special_needs"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.special_needs ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.special_needs">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.special_needs[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Tongue
                </label>
                <input type="text" name="mother_tongue" value="{{ old('mother_tongue', $studentRegistration->mother_tongue ?? '') }}" placeholder="Enter Mother Tongue"
                       @input="delete errors.mother_tongue"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.mother_tongue ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.mother_tongue">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_tongue[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Remarks
                </label>
                <input type="text" name="remarks" value="{{ old('remarks', $studentRegistration->remarks ?? '') }}" placeholder="Enter Remarks"
                       @input="delete errors.remarks"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.remarks ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.remarks">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.remarks[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Number of Brothers
                </label>
                <input type="number" step="0.01" name="number_of_brothers" value="{{ old('number_of_brothers', $studentRegistration->number_of_brothers ?? 0) }}"
                       @input="delete errors.number_of_brothers"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.number_of_brothers ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.number_of_brothers">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.number_of_brothers[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Number of Sisters
                </label>
                <input type="number" step="0.01" name="number_of_sisters" value="{{ old('number_of_sisters', $studentRegistration->number_of_sisters ?? 0) }}"
                       @input="delete errors.number_of_sisters"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.number_of_sisters ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.number_of_sisters">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.number_of_sisters[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Is Single Parent
                </label>
                <select name="is_single_parent" @change="delete errors.is_single_parent"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.is_single_parent ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="0" {{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_single_parent', $studentRegistration->is_single_parent ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
                <template x-if="errors.is_single_parent">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.is_single_parent[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Corresponding Relative
                </label>
                <select name="corresponding_relative" @change="delete errors.corresponding_relative"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.corresponding_relative ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Corresponding Relative</option>
                    @foreach($correspondingRelatives as $rel)
                        <option value="{{ $rel->name }}" {{ (old('corresponding_relative', $studentRegistration->corresponding_relative ?? '') == $rel->name) ? 'selected' : '' }}>{{ $rel->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.corresponding_relative">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.corresponding_relative[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Is Transport Required
                </label>
                <select name="is_transport_required" @change="delete errors.is_transport_required"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.is_transport_required ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="0" {{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_transport_required', $studentRegistration->is_transport_required ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
                <template x-if="errors.is_transport_required">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.is_transport_required[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bus / Van Req.
                </label>
                <select name="bus_stop" @change="delete errors.bus_stop"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.bus_stop ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Bus Stop</option>
                    {{-- This would normally be populated from a transport module --}}
                </select>
                <template x-if="errors.bus_stop">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.bus_stop[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Other Stop
                </label>
                <input type="text" name="other_stop" value="{{ old('other_stop', $studentRegistration->other_stop ?? '') }}" placeholder="Enter Other Stop"
                       @input="delete errors.other_stop"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.other_stop ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.other_stop">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.other_stop[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Boarding Type
                </label>
                <select name="boarding_type" @change="delete errors.boarding_type"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.boarding_type ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Choose Boarding Type</option>
                    @foreach($boardingTypes as $type)
                        <option value="{{ $type->name }}" {{ (old('boarding_type', $studentRegistration->boarding_type ?? '') == $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.boarding_type">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.boarding_type[0]"></p>
                </template>
            </div>
        </div>
    </div>
</div>
