{{-- Step 2: Parent Details --}}
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
                <template x-if="errors.father_name || errors.father_contact">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                </template>
            </div>
            <i class="fas fa-chevron-down text-blue-500 text-xs transition-transform duration-200" :class="{'rotate-180': fatherExpanded}"></i>
        </div>
        <div class="p-5 space-y-4">
            {{-- Always visible --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="father_name" x-model="formData.father_name" @input="clearError('father_name')"
                           placeholder="Father's full name"
                           :class="{'border-red-500': errors.father_name}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.father_name"><p class="text-red-500 text-xs mt-1" x-text="errors.father_name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Contact <span class="text-red-500">*</span></label>
                    <input type="text" name="father_contact" x-model="formData.father_contact" @input="clearError('father_contact')"
                           placeholder="Mobile number"
                           :class="{'border-red-500': errors.father_contact}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.father_contact"><p class="text-red-500 text-xs mt-1" x-text="errors.father_contact[0]"></p></template>
                </div>
            </div>

            {{-- Expandable --}}
            <div x-show="fatherExpanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" name="father_email" x-model="formData.father_email" @input="clearError('father_email')"
                           placeholder="Email address"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Occupation</label>
                    <input type="text" name="father_occupation" x-model="formData.father_occupation" @input="clearError('father_occupation')"
                           placeholder="Occupation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Qualification</label>
                    <input type="text" name="father_qualification" x-model="formData.father_qualification"
                           placeholder="Highest qualification"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Annual Income</label>
                    <input type="number" step="0.01" name="father_annual_income" x-model="formData.father_annual_income"
                           placeholder="Annual income"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Organization</label>
                    <input type="text" name="father_organization" x-model="formData.father_organization"
                           placeholder="Organization name"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Designation</label>
                    <input type="text" name="father_designation" x-model="formData.father_designation"
                           placeholder="Job designation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Department</label>
                    <input type="text" name="father_department" x-model="formData.father_department"
                           placeholder="Department"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Office Address</label>
                    <input type="text" name="father_office_address" x-model="formData.father_office_address"
                           placeholder="Office address"
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
                <template x-if="errors.mother_name || errors.mother_contact">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                </template>
            </div>
            <i class="fas fa-chevron-down text-pink-500 text-xs transition-transform duration-200" :class="{'rotate-180': motherExpanded}"></i>
        </div>
        <div class="p-5 space-y-4">
            {{-- Always visible --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="mother_name" x-model="formData.mother_name" @input="clearError('mother_name')"
                           placeholder="Mother's full name"
                           :class="{'border-red-500': errors.mother_name}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.mother_name"><p class="text-red-500 text-xs mt-1" x-text="errors.mother_name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Contact <span class="text-red-500">*</span></label>
                    <input type="text" name="mother_contact" x-model="formData.mother_contact" @input="clearError('mother_contact')"
                           placeholder="Mobile number"
                           :class="{'border-red-500': errors.mother_contact}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                    <template x-if="errors.mother_contact"><p class="text-red-500 text-xs mt-1" x-text="errors.mother_contact[0]"></p></template>
                </div>
            </div>

            {{-- Expandable --}}
            <div x-show="motherExpanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" name="mother_email" x-model="formData.mother_email"
                           placeholder="Email address"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Occupation</label>
                    <input type="text" name="mother_occupation" x-model="formData.mother_occupation"
                           placeholder="Occupation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Qualification</label>
                    <input type="text" name="mother_qualification" x-model="formData.mother_qualification"
                           placeholder="Highest qualification"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Annual Income</label>
                    <input type="number" step="0.01" name="mother_annual_income" x-model="formData.mother_annual_income"
                           placeholder="Annual income"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Organization</label>
                    <input type="text" name="mother_organization" x-model="formData.mother_organization"
                           placeholder="Organization name"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Designation</label>
                    <input type="text" name="mother_designation" x-model="formData.mother_designation"
                           placeholder="Job designation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Department</label>
                    <input type="text" name="mother_department" x-model="formData.mother_department"
                           placeholder="Department"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Office Address</label>
                    <input type="text" name="mother_office_address" x-model="formData.mother_office_address"
                           placeholder="Office address"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
        </div>
    </div>
</div>
