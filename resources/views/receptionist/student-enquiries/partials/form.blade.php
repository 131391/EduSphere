{{-- receptionist/student-enquiries/partials/form.blade.php --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Academic Year --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Academic Year <span class="text-red-500">*</span></label>
        <select name="academic_year_id" x-model="formData.academic_year_id" @change="clearError('academic_year_id')"
            :class="{'border-red-500': errors.academic_year_id}"
            class="no-select2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
            <option value="">Choose Academic Year</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.academic_year_id">
            <p class="text-xs text-red-500 mt-1" x-text="errors.academic_year_id[0]"></p>
        </template>
    </div>

    {{-- Class --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Class <span class="text-red-500">*</span></label>
        <select name="class_id" x-model="formData.class_id" @change="clearError('class_id')"
            :class="{'border-red-500': errors.class_id}"
            class="no-select2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
            <option value="">Choose Class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.class_id">
            <p class="text-xs text-red-500 mt-1" x-text="errors.class_id[0]"></p>
        </template>
    </div>

    {{-- Student Name --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Student Name <span class="text-red-500">*</span></label>
        <input type="text" name="student_name" x-model="formData.student_name" @input="clearError('student_name')"
            placeholder="Full name of the student"
            :class="{'border-red-500': errors.student_name}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
        <template x-if="errors.student_name">
            <p class="text-xs text-red-500 mt-1" x-text="errors.student_name[0]"></p>
        </template>
    </div>

    {{-- Gender --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Gender</label>
        <select name="gender" x-model="formData.gender" @change="clearError('gender')"
            :class="{'border-red-500': errors.gender}"
            class="no-select2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
            <option value="">Choose Gender</option>
            @foreach(\App\Enums\Gender::options() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <template x-if="errors.gender">
            <p class="text-xs text-red-500 mt-1" x-text="errors.gender[0]"></p>
        </template>
    </div>

    {{-- Father Name --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Father Name <span class="text-red-500">*</span></label>
        <input type="text" name="father_name" x-model="formData.father_name" @input="clearError('father_name')"
            placeholder="Father's full name"
            :class="{'border-red-500': errors.father_name}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
        <template x-if="errors.father_name">
            <p class="text-xs text-red-500 mt-1" x-text="errors.father_name[0]"></p>
        </template>
    </div>

    {{-- Father Contact --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Father Contact <span class="text-red-500">*</span></label>
        <input type="tel" name="father_contact" x-model="formData.father_contact" @input="clearError('father_contact')"
            placeholder="Father's mobile number"
            :class="{'border-red-500': errors.father_contact}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
        <template x-if="errors.father_contact">
            <p class="text-xs text-red-500 mt-1" x-text="errors.father_contact[0]"></p>
        </template>
    </div>

    {{-- Contact No --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Contact No <span class="text-red-500">*</span></label>
        <input type="tel" name="contact_no" x-model="formData.contact_no" @input="clearError('contact_no')"
            placeholder="Primary contact number"
            :class="{'border-red-500': errors.contact_no}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
        <template x-if="errors.contact_no">
            <p class="text-xs text-red-500 mt-1" x-text="errors.contact_no[0]"></p>
        </template>
    </div>

    {{-- Follow Up Date --}}
    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Follow Up Date</label>
        <input type="date" name="follow_up_date" x-model="formData.follow_up_date" @change="clearError('follow_up_date')"
            :class="{'border-red-500': errors.follow_up_date}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
        <template x-if="errors.follow_up_date">
            <p class="text-xs text-red-500 mt-1" x-text="errors.follow_up_date[0]"></p>
        </template>
    </div>

    {{-- Enquiry Source --}}
    <div class="space-y-1 md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Enquiry Source / Reason <span class="text-gray-400 text-xs">(optional)</span></label>
        <input type="text" name="subject_name" x-model="formData.subject_name" @input="clearError('subject_name')"
            placeholder="e.g. Walk-in, Referral, Online"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
    </div>

    {{-- Status (edit mode only) --}}
    <template x-if="editMode">
        <div class="space-y-1 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Enquiry Status</label>
            <select name="form_status" x-model="formData.form_status" @change="clearError('form_status')"
                class="no-select2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                @foreach(\App\Enums\EnquiryStatus::cases() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
    </template>

</div>
