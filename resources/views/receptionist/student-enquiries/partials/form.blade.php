{{-- Enquiry Form Section --}}
<div class="space-y-6">
    <div class="bg-teal-50 dark:bg-teal-900/30 p-6 rounded-xl border border-teal-100 dark:border-teal-800">
        <h4 class="text-lg font-bold text-teal-800 dark:text-teal-200 mb-6 flex items-center">
            <i class="fas fa-clipboard-list mr-2"></i>
            Enquiry Form
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-calendar-alt mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Academic Year <span class="text-red-500">*</span>
                </label>
                <select name="academic_year_id" 
                        x-model="formData.academic_year_id"
                        @change="clearError('academic_year_id')"
                        class="modal-input-premium"
                        :class="errors.academic_year_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
                <template x-if="errors.academic_year_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.academic_year_id[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-chalkboard-user mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Class <span class="text-red-500">*</span>
                </label>
                <select name="class_id" 
                        x-model="formData.class_id"
                        @change="clearError('class_id')"
                        class="modal-input-premium"
                        :class="errors.class_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                <template x-if="errors.class_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.class_id[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-book mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Subject Name
                </label>
                <input type="text" 
                       name="subject_name" 
                       x-model="formData.subject_name"
                       placeholder="Subject Name" 
                       @input="clearError('subject_name')"
                       class="modal-input-premium"
                       :class="errors.subject_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.subject_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.subject_name[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-user-graduate mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Student's Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="student_name" 
                       x-model="formData.student_name"
                       placeholder="Student's Name" 
                       @input="clearError('student_name')"
                       class="modal-input-premium"
                       :class="errors.student_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.student_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_name[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-venus-mars mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Gender
                </label>
                <select name="gender" 
                        x-model="formData.gender"
                        @change="clearError('gender')"
                        class="modal-input-premium"
                        :class="errors.gender ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Gender</option>
                    @foreach(\App\Constants\Gender::getOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <template x-if="errors.gender">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.gender[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-calendar-check mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Follow Up Date
                </label>
                <input type="date" 
                       name="follow_up_date" 
                       x-model="formData.follow_up_date"
                       @change="clearError('follow_up_date')"
                       class="modal-input-premium"
                       :class="errors.follow_up_date ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.follow_up_date">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.follow_up_date[0]"></p>
                </template>
            </div>
        </div>
    </div>

    <div class="bg-indigo-50 dark:bg-indigo-900/30 p-6 rounded-xl border border-indigo-100 dark:border-indigo-800" x-data="{ fatherExpanded: false }">
        <h4 class="text-lg font-bold text-indigo-800 dark:text-indigo-200 mb-6 flex items-center justify-between cursor-pointer" @click="fatherExpanded = !fatherExpanded">
            <span class="flex items-center">
                <i class="fas fa-user-tie mr-2"></i>
                Father's Details
            </span>
            <i class="fas fa-chevron-down text-sm transition-transform duration-200" :class="{ 'rotate-180': fatherExpanded }"></i>
        </h4>
        <div class="space-y-6">
        <!-- Always Visible: Name and Contact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-user-tie mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father's Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="father_name" 
                           x-model="formData.father_name"
                           placeholder="Enter Father's Name" 
                           @input="clearError('father_name')"
                           class="modal-input-premium"
                           :class="errors.father_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_name">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_name[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-phone mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father Contact No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="father_contact" 
                           x-model="formData.father_contact"
                           placeholder="Enter Father contact no" 
                           @input="clearError('father_contact')"
                           class="modal-input-premium"
                           :class="errors.father_contact ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_contact">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_contact[0]"></p>
                    </template>
                </div>
            </div>

            <div x-show="fatherExpanded" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-indigo-100 dark:border-indigo-800/50">
                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-envelope mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father Email Id
                    </label>
                    <input type="email" 
                           name="father_email" 
                           x-model="formData.father_email"
                           placeholder="Enter Father Email id" 
                           @input="clearError('father_email')"
                           class="modal-input-premium"
                           :class="errors.father_email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_email">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_email[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-graduation-cap mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father Qualification
                    </label>
                    <input type="text" 
                           name="father_qualification" 
                           x-model="formData.father_qualification"
                           placeholder="Enter Father qualification" 
                           @input="clearError('father_qualification')"
                           class="modal-input-premium"
                           :class="errors.father_qualification ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_qualification">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_qualification[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-briefcase mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father Occupation
                    </label>
                    <input type="text" 
                           name="father_occupation" 
                           x-model="formData.father_occupation"
                           placeholder="Enter Father Occupation" 
                           @input="clearError('father_occupation')"
                           class="modal-input-premium"
                           :class="errors.father_occupation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_occupation">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_occupation[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-sack-dollar mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father Annual Income
                    </label>
                    <input type="number" 
                           step="0.01" 
                           name="father_annual_income" 
                           x-model="formData.father_annual_income"
                           placeholder="Enter Father Annual Income" 
                           @input="clearError('father_annual_income')"
                           class="modal-input-premium"
                           :class="errors.father_annual_income ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_annual_income">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_annual_income[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-building mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Organization
                    </label>
                    <input type="text" 
                           name="father_organization" 
                           x-model="formData.father_organization"
                           placeholder="Organization" 
                           @input="clearError('father_organization')"
                           class="modal-input-premium"
                           :class="errors.father_organization ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_organization">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_organization[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-map-location-dot mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Father Office Address
                    </label>
                    <input type="text" 
                           name="father_office_address" 
                           x-model="formData.father_office_address"
                           placeholder="Enter Father Office Address" 
                           @input="clearError('father_office_address')"
                           class="modal-input-premium"
                           :class="errors.father_office_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_office_address">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_office_address[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-sitemap mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Department
                    </label>
                    <input type="text" 
                           name="father_department" 
                           x-model="formData.father_department"
                           placeholder="Enter Department" 
                           @input="clearError('father_department')"
                           class="modal-input-premium"
                           :class="errors.father_department ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_department">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_department[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-id-badge mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Designation
                    </label>
                    <input type="text" 
                           name="father_designation" 
                           x-model="formData.father_designation"
                           placeholder="Enter Designation" 
                           @input="clearError('father_designation')"
                           class="modal-input-premium"
                           :class="errors.father_designation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.father_designation">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_designation[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-rose-50 dark:bg-rose-900/30 p-6 rounded-xl border border-rose-100 dark:border-rose-800" x-data="{ motherExpanded: false }">
        <h4 class="text-lg font-bold text-rose-800 dark:text-rose-200 mb-6 flex items-center justify-between cursor-pointer" @click="motherExpanded = !motherExpanded">
            <span class="flex items-center">
                <i class="fas fa-user-dress mr-2"></i>
                Mother's Details
            </span>
            <i class="fas fa-chevron-down text-sm transition-transform duration-200" :class="{ 'rotate-180': motherExpanded }"></i>
        </h4>
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-user-dress mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother's Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="mother_name" 
                           x-model="formData.mother_name"
                           placeholder="Enter mother's Name" 
                           @input="clearError('mother_name')"
                           class="modal-input-premium"
                           :class="errors.mother_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_name">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_name[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-phone mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother Contact No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="mother_contact" 
                           x-model="formData.mother_contact"
                           placeholder="Enter Mother contact no" 
                           @input="clearError('mother_contact')"
                           class="modal-input-premium"
                           :class="errors.mother_contact ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_contact">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_contact[0]"></p>
                    </template>
                </div>
            </div>

            <div x-show="motherExpanded" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-rose-100 dark:border-rose-800/50">
                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-envelope mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother Email Id
                    </label>
                    <input type="email" 
                           name="mother_email" 
                           x-model="formData.mother_email"
                           placeholder="Enter Mother Email id" 
                           @input="clearError('mother_email')"
                           class="modal-input-premium"
                           :class="errors.mother_email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_email">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_email[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-graduation-cap mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother Qualification
                    </label>
                    <input type="text" 
                           name="mother_qualification" 
                           x-model="formData.mother_qualification"
                           placeholder="Enter Mother qualification" 
                           @input="clearError('mother_qualification')"
                           class="modal-input-premium"
                           :class="errors.mother_qualification ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_qualification">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_qualification[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-briefcase mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother Occupation
                    </label>
                    <input type="text" 
                           name="mother_occupation" 
                           x-model="formData.mother_occupation"
                           placeholder="Enter Mother Occupation" 
                           @input="clearError('mother_occupation')"
                           class="modal-input-premium"
                           :class="errors.mother_occupation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_occupation">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_occupation[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-sack-dollar mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother Annual Income
                    </label>
                    <input type="number" 
                           step="0.01" 
                           name="mother_annual_income" 
                           x-model="formData.mother_annual_income"
                           placeholder="Enter Mother Annual Income" 
                           @input="clearError('mother_annual_income')"
                           class="modal-input-premium"
                           :class="errors.mother_annual_income ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_annual_income">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_annual_income[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-building mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Organization
                    </label>
                    <input type="text" 
                           name="mother_organization" 
                           x-model="formData.mother_organization"
                           placeholder="Organization" 
                           @input="clearError('mother_organization')"
                           class="modal-input-premium"
                           :class="errors.mother_organization ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_organization">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_organization[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-map-location-dot mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Mother Office Address
                    </label>
                    <input type="text" 
                           name="mother_office_address" 
                           x-model="formData.mother_office_address"
                           placeholder="Enter Mother Office Address" 
                           @input="clearError('mother_office_address')"
                           class="modal-input-premium"
                           :class="errors.mother_office_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_office_address">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_office_address[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-sitemap mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Department
                    </label>
                    <input type="text" 
                           name="mother_department" 
                           x-model="formData.mother_department"
                           placeholder="Enter Department" 
                           @input="clearError('mother_department')"
                           class="modal-input-premium"
                           :class="errors.mother_department ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_department">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_department[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-id-badge mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Designation
                    </label>
                    <input type="text" 
                           name="mother_designation" 
                           x-model="formData.mother_designation"
                           placeholder="Enter Designation" 
                           @input="clearError('mother_designation')"
                           class="modal-input-premium"
                           :class="errors.mother_designation ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.mother_designation">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_designation[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-amber-50 dark:bg-amber-900/30 p-6 rounded-xl border border-amber-100 dark:border-amber-800" x-data="{ contactExpanded: false }">
        <h4 class="text-lg font-bold text-amber-800 dark:text-amber-200 mb-6 flex items-center justify-between cursor-pointer" @click="contactExpanded = !contactExpanded">
            <span class="flex items-center">
                <i class="fas fa-address-book mr-2"></i>
                Contact Details
            </span>
            <i class="fas fa-chevron-down text-sm transition-transform duration-200" :class="{ 'rotate-180': contactExpanded }"></i>
        </h4>
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-phone mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Contact No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="contact_no" 
                           x-model="formData.contact_no"
                           placeholder="Enter Contact no" 
                           @input="clearError('contact_no')"
                           class="modal-input-premium"
                           :class="errors.contact_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.contact_no">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.contact_no[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fab fa-whatsapp mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Whatsapp No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="whatsapp_no" 
                           x-model="formData.whatsapp_no"
                           placeholder="Enter whatsapp  no" 
                           @input="clearError('whatsapp_no')"
                           class="modal-input-premium"
                           :class="errors.whatsapp_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.whatsapp_no">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.whatsapp_no[0]"></p>
                    </template>
                </div>
            </div>

            <div x-show="contactExpanded" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-amber-100 dark:border-amber-800/50">
                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fab fa-facebook mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Facebook Id
                    </label>
                    <input type="text" 
                           name="facebook_id" 
                           x-model="formData.facebook_id"
                           placeholder="Enter Facebook Id" 
                           @input="clearError('facebook_id')"
                           class="modal-input-premium"
                           :class="errors.facebook_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.facebook_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.facebook_id[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-envelope mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Email Id
                    </label>
                    <input type="email" 
                           name="email_id" 
                           x-model="formData.email_id"
                           placeholder="Enter Email id" 
                           @input="clearError('email_id')"
                           class="modal-input-premium"
                           :class="errors.email_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.email_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.email_id[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-comment-sms mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>SMS No
                    </label>
                    <input type="text" 
                           name="sms_no" 
                           x-model="formData.sms_no"
                           placeholder="Enter SMS no" 
                           @input="clearError('sms_no')"
                           class="modal-input-premium"
                           :class="errors.sms_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.sms_no">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.sms_no[0]"></p>
                    </template>
                </div>

                <div class="group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fab fa-twitter mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Twitter Id
                    </label>
                    <input type="text" 
                           name="twitter_id" 
                           x-model="formData.twitter_id"
                           placeholder="Enter Twitter Id" 
                           @input="clearError('twitter_id')"
                           class="modal-input-premium"
                           :class="errors.twitter_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.twitter_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.twitter_id[0]"></p>
                    </template>
                </div>

                <div class="md:col-span-2 group">
                    <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                        <i class="fas fa-kit-medical mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Emergency Contact No
                    </label>
                    <input type="text" 
                           name="emergency_contact_no" 
                           x-model="formData.emergency_contact_no"
                           placeholder="Emergency Contact no" 
                           @input="clearError('emergency_contact_no')"
                           class="modal-input-premium"
                           :class="errors.emergency_contact_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.emergency_contact_no">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.emergency_contact_no[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div>

{{-- Continue in next part due to character limit --}}

    <div class="bg-cyan-50 dark:bg-cyan-900/30 p-6 rounded-xl border border-cyan-100 dark:border-cyan-800">
        <h4 class="text-lg font-bold text-cyan-800 dark:text-cyan-200 mb-6 flex items-center">
            <i class="fas fa-user-check mr-2"></i>
            Personal Details
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-calendar-alt mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>DOB
                </label>
                <input type="date" 
                       name="dob" 
                       x-model="formData.dob"
                       @change="clearError('dob')"
                       class="modal-input-premium"
                       :class="errors.dob ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.dob">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.dob[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-id-card mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Aadhar No
                </label>
                <input type="text" 
                       name="aadhar_no" 
                       x-model="formData.aadhar_no"
                       placeholder="Aadhar no of the Students" 
                       @input="clearError('aadhar_no')"
                       class="modal-input-premium"
                       :class="errors.aadhar_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.aadhar_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.aadhar_no[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-user-clock mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Grand Father Name
                </label>
                <input type="text" 
                       name="grand_father_name" 
                       x-model="formData.grand_father_name"
                       placeholder="Enter Grand father name" 
                       @input="clearError('grand_father_name')"
                       class="modal-input-premium"
                       :class="errors.grand_father_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.grand_father_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.grand_father_name[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-sack-dollar mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Annual Income
                </label>
                <div class="relative group/input">
                    <input type="number" 
                           step="0.01" 
                           name="annual_income" 
                           x-model="formData.annual_income"
                           placeholder="Enter Annual Income" 
                           @input="clearError('annual_income')"
                           class="modal-input-premium pr-10"
                           :class="errors.annual_income ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-emerald-500">
                        <i class="fas fa-indian-rupee-sign text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.annual_income">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.annual_income[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-people-arrows mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>No of Brother's
                </label>
                <input type="number" 
                       name="no_of_brothers" 
                       x-model="formData.no_of_brothers"
                       placeholder="Choose No of Brother's" 
                       @input="clearError('no_of_brothers')"
                       class="modal-input-premium"
                       :class="errors.no_of_brothers ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.no_of_brothers">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.no_of_brothers[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-people-arrows mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>No of Sister's
                </label>
                <input type="number" 
                       name="no_of_sisters" 
                       x-model="formData.no_of_sisters"
                       placeholder="Choose No of Sister's" 
                       @input="clearError('no_of_sisters')"
                       class="modal-input-premium"
                       :class="errors.no_of_sisters ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.no_of_sisters">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.no_of_sisters[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-layer-group mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Category
                </label>
                <select name="category" 
                        x-model="formData.category"
                        @change="clearError('category')"
                        class="modal-input-premium"
                        :class="errors.category ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Category</option>
                    <option value="General">General</option>
                    <option value="OBC">OBC</option>
                    <option value="SC">SC</option>
                    <option value="ST">ST</option>
                    <option value="Other">Other</option>
                </select>
                <template x-if="errors.category">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.category[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-users-viewfinder mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Minority
                </label>
                <select name="minority" 
                        x-model="formData.minority"
                        @change="clearError('minority')"
                        class="modal-input-premium"
                        :class="errors.minority ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Minority</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <template x-if="errors.minority">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.minority[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-om mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Religion
                </label>
                <select name="religion" 
                        x-model="formData.religion"
                        @change="clearError('religion')"
                        class="modal-input-premium"
                        :class="errors.religion ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Religion</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Muslim">Muslim</option>
                    <option value="Christian">Christian</option>
                    <option value="Sikh">Sikh</option>
                    <option value="Other">Other</option>
                </select>
                <template x-if="errors.religion">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.religion[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-bus mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Transport Facility
                </label>
                <select name="transport_facility" 
                        x-model="formData.transport_facility"
                        @change="clearError('transport_facility')"
                        class="modal-input-premium"
                        :class="errors.transport_facility ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Transport Facility</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <template x-if="errors.transport_facility">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.transport_facility[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-hotel mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Hostel Facility
                </label>
                <select name="hostel_facility" 
                        x-model="formData.hostel_facility"
                        @change="clearError('hostel_facility')"
                        class="modal-input-premium"
                        :class="errors.hostel_facility ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    <option value="">Choose Hostel Facility</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <template x-if="errors.hostel_facility">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.hostel_facility[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-graduation-cap mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Previous Class
                </label>
                <input type="text" 
                       name="previous_class" 
                       x-model="formData.previous_class"
                       placeholder="Enter Previous Class" 
                       @input="clearError('previous_class')"
                       class="modal-input-premium"
                       :class="errors.previous_class ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.previous_class">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.previous_class[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2 group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-fingerprint mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Identity Marks
                </label>
                <textarea name="identity_marks" 
                          x-model="formData.identity_marks"
                          rows="2" 
                          placeholder="Enter Identity Marks" 
                          @input="clearError('identity_marks')"
                          class="modal-input-premium"
                          :class="errors.identity_marks ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''"></textarea>
                <template x-if="errors.identity_marks">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.identity_marks[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2 group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-map-location mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Permanent Address
                </label>
                <textarea name="permanent_address" 
                          x-model="formData.permanent_address"
                          rows="2" 
                          placeholder="Enter Permanent Address" 
                          @input="clearError('permanent_address')"
                          class="modal-input-premium"
                          :class="errors.permanent_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''"></textarea>
                <template x-if="errors.permanent_address">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.permanent_address[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-globe mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Country <span class="text-red-500">*</span>
                </label>
                <select name="country_id" 
                        x-model="formData.country_id"
                        @change="clearError('country_id')"
                        class="modal-input-premium"
                        :class="errors.country_id ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                    @foreach(config('countries') as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.country_id">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.country_id[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-school mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Previous School Name
                </label>
                <input type="text" 
                       name="previous_school_name" 
                       x-model="formData.previous_school_name"
                       placeholder="Previous School Name" 
                       @input="clearError('previous_school_name')"
                       class="modal-input-premium"
                       :class="errors.previous_school_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.previous_school_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.previous_school_name[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-list-ol mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Student's Roll No
                </label>
                <input type="text" 
                       name="student_roll_no" 
                       x-model="formData.student_roll_no"
                       placeholder="Student's Roll No" 
                       @input="clearError('student_roll_no')"
                       class="modal-input-premium"
                       :class="errors.student_roll_no ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.student_roll_no">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_roll_no[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-calendar-check mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Passing Year
                </label>
                <input type="number" 
                       name="passing_year" 
                       x-model="formData.passing_year"
                       placeholder="Passing Year" 
                       min="1950" max="{{ date('Y') + 20 }}" step="1" 
                       @input="clearError('passing_year')"
                       class="modal-input-premium"
                       :class="errors.passing_year ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.passing_year">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.passing_year[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name Of The Exam</label>
                <input type="text" name="exam_name" value="{{ old('exam_name') }}" placeholder="Name Of The Exam" @input="clearError('exam_name')"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white transition-all shadow-sm"
                       :class="errors.exam_name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.exam_name">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.exam_name[0]"></p>
                </template>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within:text-emerald-500 transition-colors duration-200">
                    <i class="fas fa-file-signature mr-2 text-gray-400 group-focus-within:text-emerald-500"></i>Board / University Name
                </label>
                <input type="text" 
                       name="board_university" 
                       x-model="formData.board_university"
                       placeholder="Board / University Name" 
                       @input="clearError('board_university')"
                       class="modal-input-premium"
                       :class="errors.board_university ? 'border-red-500 ring-red-500/5 bg-red-50/20' : ''">
                <template x-if="errors.board_university">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.board_university[0]"></p>
                </template>
            </div>

            <div class="md:col-span-2 group">
                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors duration-200 border border-transparent hover:border-emerald-100 dark:hover:border-emerald-800">
                    <div class="relative flex items-center">
                        <input type="checkbox" 
                               name="only_child" 
                               x-model="formData.only_child"
                               value="1"
                               class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-gray-300 dark:border-gray-600 checked:bg-emerald-500 checked:border-emerald-500 transition-all duration-200">
                        <i class="fas fa-check absolute scale-0 peer-checked:scale-100 text-white text-[10px] left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 transition-transform duration-200 pointer-events-none"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 transition-colors duration-200">
                        Only Child of the Parents
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>

    <div class="bg-teal-50 dark:bg-teal-900/30 p-6 rounded-xl border border-teal-100 dark:border-teal-800">
        <h4 class="text-lg font-bold text-teal-800 dark:text-teal-200 mb-6 flex items-center">
            <i class="fas fa-camera mr-2"></i>
            Upload Photos
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="group">
                <label class="modal-label-premium group-focus-within/file:text-emerald-500 text-center mb-4">Father's Photo</label>
                <div class="flex flex-col items-center group/file">
                    <div class="w-32 h-32 bg-white dark:bg-gray-800 rounded-2xl mb-4 flex items-center justify-center overflow-hidden relative border-2 border-dashed border-gray-200 dark:border-gray-700 group-hover/file:border-emerald-500 transition-colors duration-200 shadow-sm">
                        <img id="father-photo-preview" src="#" alt="Father's Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user-tie text-gray-300 text-4xl group-hover/file:scale-110 transition-transform duration-300" id="father-photo-icon"></i>
                        <button type="button" 
                                id="father-photo-remove" 
                                onclick="removeImage(event, 'father_photo', 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                                class="hidden absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-200">
                            <i class="fas fa-trash-alt text-white text-xl"></i>
                        </button>
                    </div>
                    <input type="file" name="father_photo" accept="image/*" 
                           onchange="previewImage(event, 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                           @change="clearError('father_photo')"
                           class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all cursor-pointer">
                    <template x-if="errors.father_photo">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_photo[0]"></p>
                    </template>
                </div>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within/file:text-emerald-500 text-center mb-4">Mother's Photo</label>
                <div class="flex flex-col items-center group/file">
                    <div class="w-32 h-32 bg-white dark:bg-gray-800 rounded-2xl mb-4 flex items-center justify-center overflow-hidden relative border-2 border-dashed border-gray-200 dark:border-gray-700 group-hover/file:border-emerald-500 transition-colors duration-200 shadow-sm">
                        <img id="mother-photo-preview" src="#" alt="Mother's Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user-dress text-gray-300 text-4xl group-hover/file:scale-110 transition-transform duration-300" id="mother-photo-icon"></i>
                        <button type="button" 
                                id="mother-photo-remove" 
                                onclick="removeImage(event, 'mother_photo', 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                                class="hidden absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-200">
                            <i class="fas fa-trash-alt text-white text-xl"></i>
                        </button>
                    </div>
                    <input type="file" name="mother_photo" accept="image/*" 
                           onchange="previewImage(event, 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                           @change="clearError('mother_photo')"
                           class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all cursor-pointer">
                    <template x-if="errors.mother_photo">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_photo[0]"></p>
                    </template>
                </div>
            </div>

            <div class="group">
                <label class="modal-label-premium group-focus-within/file:text-emerald-500 text-center mb-4">Student Photo</label>
                <div class="flex flex-col items-center group/file">
                    <div class="w-32 h-32 bg-white dark:bg-gray-800 rounded-2xl mb-4 flex items-center justify-center overflow-hidden relative border-2 border-dashed border-gray-200 dark:border-gray-700 group-hover/file:border-emerald-500 transition-colors duration-200 shadow-sm">
                        <img id="student-photo-preview" src="#" alt="Student Photo" class="hidden w-full h-full object-cover">
                        <i class="fas fa-user-graduate text-gray-300 text-4xl group-hover/file:scale-110 transition-transform duration-300" id="student-photo-icon"></i>
                        <button type="button" 
                                id="student-photo-remove" 
                                onclick="removeImage(event, 'student_photo', 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                                class="hidden absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-200">
                            <i class="fas fa-trash-alt text-white text-xl"></i>
                        </button>
                    </div>
                    <input type="file" name="student_photo" accept="image/*" 
                           onchange="previewImage(event, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                           @change="clearError('student_photo')"
                           class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all cursor-pointer">
                    <template x-if="errors.student_photo">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_photo[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId, iconId, removeBtnId) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            const icon = document.getElementById(iconId);
            const removeBtn = document.getElementById(removeBtnId);
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) {
                icon.classList.add('hidden');
            }
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
            }
            
            // Store image in sessionStorage to preserve on validation errors
            const inputName = event.target.name;
            sessionStorage.setItem(`enquiry_${inputName}`, e.target.result);
        };
        reader.readAsDataURL(file);
    }
}

function removeImage(event, inputName, previewId, iconId, removeBtnId) {
    event.preventDefault();
    event.stopPropagation();
    
    const input = document.querySelector(`input[name="${inputName}"]`);
    const preview = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    const removeBtn = document.getElementById(removeBtnId);
    
    // Reset file input
    if (input) {
        input.value = '';
    }
    
    // Remove from sessionStorage
    sessionStorage.removeItem(`enquiry_${inputName}`);
    
    // Hide preview and show icon
    if (preview) {
        preview.src = '#';
        preview.classList.add('hidden');
    }
    if (icon) {
        icon.classList.remove('hidden');
    }
    if (removeBtn) {
        removeBtn.classList.add('hidden');
    }
}
</script>
