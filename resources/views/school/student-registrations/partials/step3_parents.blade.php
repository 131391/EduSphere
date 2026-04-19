{{-- Step 3: Parent Details --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Father --}}
    <div class="bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-900/30 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 bg-blue-100/60 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-900/30 cursor-pointer"
             @click="fatherExpanded = !fatherExpanded">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center">
                    <i class="fas fa-user-tie text-white text-xs"></i>
                </div>
                <span class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Father's Details</span>
                <template x-if="errors.father_first_name || errors.father_last_name || errors.father_mobile_no">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                </template>
            </div>
            <i class="fas fa-chevron-down text-blue-500 text-xs transition-transform duration-200" :class="{'rotate-180': fatherExpanded}"></i>
        </div>
        <div class="p-5">
            {{-- Always visible: required fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Initial
                    </label>
                    <select name="father_name_prefix" x-model="formData.father_name_prefix"
                            class="no-select2 w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                        <option value="Mr">Mr</option>
                        <option value="Dr">Dr</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="father_first_name" x-model="formData.father_first_name" @input="clearError('father_first_name')"
                           placeholder="First name"
                           :class="errors.father_first_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.father_first_name"><p class="text-red-500 text-xs mt-1" x-text="errors.father_first_name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="father_last_name" x-model="formData.father_last_name" @input="clearError('father_last_name')"
                           placeholder="Last name"
                           :class="errors.father_last_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.father_last_name"><p class="text-red-500 text-xs mt-1" x-text="errors.father_last_name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Mobile <span class="text-red-500">*</span></label>
                    <input type="tel" name="father_mobile_no" x-model="formData.father_mobile_no" @input="clearError('father_mobile_no')"
                           placeholder="Mobile number" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           :class="errors.father_mobile_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.father_mobile_no"><p class="text-red-500 text-xs mt-1" x-text="errors.father_mobile_no[0]"></p></template>
                </div>
            </div>

            {{-- Expandable additional fields --}}
            <div x-show="fatherExpanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Middle Name</label>
                    <input type="text" name="father_middle_name" x-model="formData.father_middle_name" placeholder="Middle name"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" name="father_email" x-model="formData.father_email" placeholder="Email"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Occupation</label>
                    <input type="text" name="father_occupation" x-model="formData.father_occupation" placeholder="Occupation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Organization</label>
                    <input type="text" name="father_organization" x-model="formData.father_organization" placeholder="Organization"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Qualification</label>
                    <select name="father_qualification_id" x-model="formData.father_qualification_id"
                            class="no-select2 w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                        <option value="">Choose</option>
                        @foreach($qualifications as $qual)
                            <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Department</label>
                    <input type="text" name="father_department" x-model="formData.father_department" placeholder="Department"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Annual Income</label>
                    <input type="number" step="0.01" name="father_annual_income" x-model="formData.father_annual_income" placeholder="Annual income"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Aadhaar No</label>
                    <input type="text" name="father_aadhaar_no" x-model="formData.father_aadhaar_no" placeholder="Aadhaar number"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Age</label>
                    <input type="number" name="father_age" x-model="formData.father_age" placeholder="Age"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Landline</label>
                    <input type="text" name="father_landline_no" x-model="formData.father_landline_no" placeholder="Landline number"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Office Address</label>
                    <input type="text" name="father_office_address" x-model="formData.father_office_address" placeholder="Office address"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
        </div>
    </div>

    {{-- Mother --}}
    <div class="bg-pink-50/50 dark:bg-pink-900/10 rounded-xl border border-pink-100 dark:border-pink-900/30 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 bg-pink-100/60 dark:bg-pink-900/20 border-b border-pink-100 dark:border-pink-900/30 cursor-pointer"
             @click="motherExpanded = !motherExpanded">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-pink-500 flex items-center justify-center">
                    <i class="fas fa-user text-white text-xs"></i>
                </div>
                <span class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Mother's Details</span>
                <template x-if="errors.mother_first_name || errors.mother_last_name || errors.mother_mobile_no">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                </template>
            </div>
            <i class="fas fa-chevron-down text-pink-500 text-xs transition-transform duration-200" :class="{'rotate-180': motherExpanded}"></i>
        </div>
        <div class="p-5">
            {{-- Always visible: required fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Initial</label>
                    <select name="mother_name_prefix" x-model="formData.mother_name_prefix"
                            class="no-select2 w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                        <option value="Mrs">Mrs</option>
                        <option value="Dr">Dr</option>
                        <option value="Ms">Ms</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="mother_first_name" x-model="formData.mother_first_name" @input="clearError('mother_first_name')"
                           placeholder="First name"
                           :class="errors.mother_first_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.mother_first_name"><p class="text-red-500 text-xs mt-1" x-text="errors.mother_first_name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="mother_last_name" x-model="formData.mother_last_name" @input="clearError('mother_last_name')"
                           placeholder="Last name"
                           :class="errors.mother_last_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.mother_last_name"><p class="text-red-500 text-xs mt-1" x-text="errors.mother_last_name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Mobile <span class="text-red-500">*</span></label>
                    <input type="tel" name="mother_mobile_no" x-model="formData.mother_mobile_no" @input="clearError('mother_mobile_no')"
                           placeholder="Mobile number" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           :class="errors.mother_mobile_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.mother_mobile_no"><p class="text-red-500 text-xs mt-1" x-text="errors.mother_mobile_no[0]"></p></template>
                </div>
            </div>

            {{-- Expandable --}}
            <div x-show="motherExpanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Middle Name</label>
                    <input type="text" name="mother_middle_name" x-model="formData.mother_middle_name" placeholder="Middle name"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" name="mother_email" x-model="formData.mother_email" placeholder="Email"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Occupation</label>
                    <input type="text" name="mother_occupation" x-model="formData.mother_occupation" placeholder="Occupation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Organization</label>
                    <input type="text" name="mother_organization" x-model="formData.mother_organization" placeholder="Organization"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Qualification</label>
                    <select name="mother_qualification_id" x-model="formData.mother_qualification_id"
                            class="no-select2 w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                        <option value="">Choose</option>
                        @foreach($qualifications as $qual)
                            <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Designation</label>
                    <input type="text" name="mother_designation" x-model="formData.mother_designation" placeholder="Designation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Department</label>
                    <input type="text" name="mother_department" x-model="formData.mother_department" placeholder="Department"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Annual Income</label>
                    <input type="number" step="0.01" name="mother_annual_income" x-model="formData.mother_annual_income" placeholder="Annual income"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Aadhaar No</label>
                    <input type="text" name="mother_aadhaar_no" x-model="formData.mother_aadhaar_no" placeholder="Aadhaar number"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Age</label>
                    <input type="number" name="mother_age" x-model="formData.mother_age" placeholder="Age"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Landline</label>
                    <input type="text" name="mother_landline_no" x-model="formData.mother_landline_no" placeholder="Landline number"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Office Address</label>
                    <input type="text" name="mother_office_address" x-model="formData.mother_office_address" placeholder="Office address"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
        </div>
    </div>

</div>
