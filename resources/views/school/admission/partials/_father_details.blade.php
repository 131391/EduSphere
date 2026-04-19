{{-- Father's Details --}}
<div class="mb-6" x-data="{ fatherExpanded: false }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="fatherExpanded = !fatherExpanded">
        <span>Father's Details</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': fatherExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Always Visible: First Row --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Father Name (If Staff)
                    </label>
                    <select name="father_staff_id" x-model="formData.father_staff_id" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Choose Father Name (If Staff)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Initial <span class="text-red-500">*</span>
                    </label>
                    <select name="father_name_prefix" x-model="formData.father_name_prefix" @change="clearError('father_name_prefix')"
                            class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                            :class="errors.father_name_prefix ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <option value="Mr">Mr</option>
                        <option value="Dr">Dr</option>
                        <option value="Late">Late</option>
                    </select>
                    <template x-if="errors.father_name_prefix">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.father_name_prefix[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="father_first_name" x-model="formData.father_first_name" @input="clearError('father_first_name')" placeholder="Enter First Name"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.father_first_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <template x-if="errors.father_first_name">
                        <p class="text-red-500 text-xs mt-1" x-text="errors.father_first_name[0]"></p>
                    </template>
                </div>
            </div>
        </div>

        {{-- Collapsible Details --}}
        <div x-show="fatherExpanded" x-collapse>
            <div class="px-6 pb-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Middle Name
                        </label>
                        <input type="text" name="father_middle_name" x-model="formData.father_middle_name" placeholder="Enter Middle Name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="father_last_name" x-model="formData.father_last_name" @input="clearError('father_last_name')" placeholder="Enter Last Name"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                               :class="errors.father_last_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.father_last_name">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.father_last_name[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Id
                        </label>
                        <input type="email" name="father_email" x-model="formData.father_email" placeholder="Enter Email Id"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mobile No <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="father_mobile_no" x-model="formData.father_mobile_no" @input="clearError('father_mobile_no')" placeholder="Enter Mobile No" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                               :class="errors.father_mobile_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.father_mobile_no">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.father_mobile_no[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Occupation
                        </label>
                        <input type="text" name="father_occupation" x-model="formData.father_occupation" placeholder="Enter Occupation"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Qualification
                        </label>
                        <select name="father_qualification_id" x-model="formData.father_qualification_id" class="no-select2 w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Choose Qualification</option>
                            @foreach($qualifications as $qualification)
                                <option value="{{ $qualification->id }}">{{ $qualification->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Annual Income
                        </label>
                        <input type="number" step="0.01" name="father_annual_income" x-model="formData.father_annual_income" placeholder="Enter Annual Income"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Aadhaar No
                        </label>
                        <input type="text" name="father_aadhaar_no" x-model="formData.father_aadhaar_no" placeholder="Enter Aadhaar No"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            PAN No
                        </label>
                        <input type="text" name="father_pan" x-model="formData.father_pan" placeholder="Enter PAN No"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
