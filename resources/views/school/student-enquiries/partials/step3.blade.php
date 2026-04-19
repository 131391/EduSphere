{{-- Step 3: Contact & Personal Details --}}

{{-- Contact Details --}}
<div class="mb-8">
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
        <i class="fas fa-phone-alt text-teal-500"></i> Contact Details
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Contact No <span class="text-red-500">*</span>
            </label>
            <input type="text" name="contact_no"
                   x-model="formData.contact_no"
                   @input="clearError('contact_no')"
                   placeholder="Primary contact number"
                   :class="{'border-red-500': errors.contact_no}"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <template x-if="errors.contact_no">
                <p class="text-red-500 text-xs mt-1" x-text="errors.contact_no[0]"></p>
            </template>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                WhatsApp No <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="text" name="whatsapp_no"
                       x-model="formData.whatsapp_no"
                       @input="clearError('whatsapp_no')"
                       placeholder="WhatsApp number"
                       :class="{'border-red-500': errors.whatsapp_no}"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white pr-36">
                <button type="button"
                        @click="formData.whatsapp_no = formData.contact_no; clearError('whatsapp_no')"
                        x-show="formData.contact_no && formData.whatsapp_no !== formData.contact_no"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-teal-600 bg-teal-50 hover:bg-teal-100 border border-teal-200 px-2 py-1 rounded-md transition-colors whitespace-nowrap">
                    <i class="fas fa-copy mr-1"></i>Same as Contact
                </button>
            </div>
            <template x-if="errors.whatsapp_no">
                <p class="text-red-500 text-xs mt-1" x-text="errors.whatsapp_no[0]"></p>
            </template>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
            <input type="email" name="email_id"
                   x-model="formData.email_id"
                   @input="clearError('email_id')"
                   placeholder="Email address"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Emergency Contact</label>
            <input type="text" name="emergency_contact_no"
                   x-model="formData.emergency_contact_no"
                   placeholder="Emergency contact number"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div x-show="contactExpanded"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="grid grid-cols-1 md:grid-cols-2 gap-5 col-span-full">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMS No</label>
                <input type="text" name="sms_no" x-model="formData.sms_no" placeholder="SMS number"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Facebook</label>
                <input type="text" name="facebook_id" x-model="formData.facebook_id" placeholder="Facebook profile"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Twitter / X</label>
                <input type="text" name="twitter_id" x-model="formData.twitter_id" placeholder="Twitter handle"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            </div>
        </div>
    </div>
    <button type="button" @click="contactExpanded = !contactExpanded"
            class="mt-3 text-xs font-semibold text-teal-600 hover:text-teal-700 flex items-center gap-1">
        <i class="fas text-[10px]" :class="contactExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        <span x-text="contactExpanded ? 'Hide social & SMS fields' : 'Show social & SMS fields'"></span>
    </button>
</div>

{{-- Personal Details --}}
<div>
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
        <i class="fas fa-id-card text-teal-500"></i> Personal Details
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Date of Birth</label>
            <input type="date" name="dob" x-model="formData.dob" @change="clearError('dob')"
                   :class="{'border-red-500': errors.dob}"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Aadhaar No</label>
            <input type="text" name="aadhaar_no" x-model="formData.aadhaar_no" placeholder="12-digit Aadhaar number"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
            <select name="category" x-model="formData.category"
                    class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <option value="">Choose Category</option>
                <option value="General">General</option>
                <option value="OBC">OBC</option>
                <option value="SC">SC</option>
                <option value="ST">ST</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Religion</label>
            <select name="religion" x-model="formData.religion"
                    class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <option value="">Choose Religion</option>
                <option value="Hindu">Hindu</option>
                <option value="Muslim">Muslim</option>
                <option value="Christian">Christian</option>
                <option value="Sikh">Sikh</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Transport Facility</label>
            <select name="transport_facility" x-model="formData.transport_facility"
                    class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Hostel Facility</label>
            <select name="hostel_facility" x-model="formData.hostel_facility"
                    class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. of Brothers</label>
            <input type="number" min="0" step="1" name="no_of_brothers" x-model="formData.no_of_brothers"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. of Sisters</label>
            <input type="number" min="0" step="1" name="no_of_sisters" x-model="formData.no_of_sisters"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Minority</label>
            <select name="minority" x-model="formData.minority"
                    class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Annual Income</label>
            <input type="number" step="0.01" name="annual_income" x-model="formData.annual_income" placeholder="Family annual income"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Grand Father Name</label>
            <input type="text" name="grand_father_name" x-model="formData.grand_father_name" placeholder="Grandfather's name"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Previous Class</label>
            <input type="text" name="previous_class" x-model="formData.previous_class" placeholder="Last class attended"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Previous School</label>
            <input type="text" name="previous_school_name" x-model="formData.previous_school_name" placeholder="Previous school name"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Country <span class="text-red-500">*</span>
            </label>
            <select name="country_id" x-model="formData.country_id" @change="clearError('country_id')"
                    :class="{'border-red-500': errors.country_id}"
                    class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </select>
            <template x-if="errors.country_id">
                <p class="text-red-500 text-xs mt-1" x-text="errors.country_id[0]"></p>
            </template>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Student Roll No</label>
            <input type="text" name="student_roll_no" x-model="formData.student_roll_no" placeholder="Previous roll number"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Passing Year</label>
            <input type="number" name="passing_year" x-model="formData.passing_year" placeholder="Year of passing"
                   min="1950" max="{{ date('Y') + 20 }}" step="1"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Exam Name</label>
            <input type="text" name="exam_name" x-model="formData.exam_name" placeholder="Name of last exam"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Board / University</label>
            <input type="text" name="board_university" x-model="formData.board_university" placeholder="Board or university name"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Permanent Address</label>
            <textarea name="permanent_address" rows="2" x-model="formData.permanent_address" placeholder="Full permanent address"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Identity Marks</label>
            <textarea name="identity_marks" rows="2" x-model="formData.identity_marks" placeholder="Any distinguishing marks"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="only_child" value="1" x-model="formData.only_child"
                       class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Only Child of the Parents</span>
            </label>
        </div>
    </div>
</div>
