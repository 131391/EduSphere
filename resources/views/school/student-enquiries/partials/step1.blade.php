{{-- Step 1: Basic Info --}}
@isset($studentEnquiry)
@php $isAdmitted = $studentEnquiry->form_status === \App\Enums\EnquiryStatus::Admitted; @endphp
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Enquiry Status</label>
    <div class="flex flex-wrap gap-2">
        @foreach(\App\Enums\EnquiryStatus::cases() as $s)
        @php $c = $s->color(); @endphp
        <label class="relative cursor-pointer {{ $isAdmitted && $s !== \App\Enums\EnquiryStatus::Admitted ? 'opacity-40 cursor-not-allowed' : '' }}">
            <input type="radio" name="form_status" value="{{ $s->value }}"
                   x-model="formData.form_status"
                   {{ $isAdmitted && $s !== \App\Enums\EnquiryStatus::Admitted ? 'disabled' : '' }}
                   class="sr-only peer">
            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border-2 text-sm font-semibold transition-all cursor-pointer
                         border-gray-200 text-gray-500 bg-white dark:bg-gray-800 dark:border-gray-600
                         peer-checked:border-{{ $c }}-500 peer-checked:bg-{{ $c }}-50 peer-checked:text-{{ $c }}-700">
                <i class="fas {{ match($s) {
                    \App\Enums\EnquiryStatus::Pending   => 'fa-clock',
                    \App\Enums\EnquiryStatus::Completed => 'fa-check-circle',
                    \App\Enums\EnquiryStatus::Cancelled => 'fa-times-circle',
                    \App\Enums\EnquiryStatus::Admitted  => 'fa-user-check',
                } }} text-xs"></i>
                {{ $s->label() }}
            </span>
        </label>
        @endforeach
    </div>
    @if($isAdmitted)
    <p class="text-xs text-amber-600 mt-2"><i class="fas fa-lock mr-1"></i>Status is locked for admitted enquiries.</p>
    @endif
</div>
@endisset

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Academic Year <span class="text-red-500">*</span>
        </label>
        <select name="academic_year_id"
                x-model="formData.academic_year_id"
                @change="clearError('academic_year_id')"
                :class="{'border-red-500': errors.academic_year_id}"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Academic Year</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.academic_year_id">
            <p class="text-red-500 text-xs mt-1" x-text="errors.academic_year_id[0]"></p>
        </template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Class <span class="text-red-500">*</span>
        </label>
        <select name="class_id"
                x-model="formData.class_id"
                @change="clearError('class_id')"
                :class="{'border-red-500': errors.class_id}"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.class_id">
            <p class="text-red-500 text-xs mt-1" x-text="errors.class_id[0]"></p>
        </template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Student's Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="student_name"
               x-model="formData.student_name"
               @input="clearError('student_name')"
               placeholder="Full name of the student"
               :class="{'border-red-500': errors.student_name}"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.student_name">
            <p class="text-red-500 text-xs mt-1" x-text="errors.student_name[0]"></p>
        </template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gender</label>
        <select name="gender"
                x-model="formData.gender"
                @change="clearError('gender')"
                :class="{'border-red-500': errors.gender}"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
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
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Enquiry Source / Reason
            <span class="ml-1 text-gray-400 text-xs font-normal">(optional)</span>
        </label>
        <input type="text" name="subject_name"
               x-model="formData.subject_name"
               @input="clearError('subject_name')"
               placeholder="e.g. Walk-in, Referral, Online"
               :class="{'border-red-500': errors.subject_name}"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.subject_name">
            <p class="text-red-500 text-xs mt-1" x-text="errors.subject_name[0]"></p>
        </template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Follow Up Date</label>
        <input type="date" name="follow_up_date"
               x-model="formData.follow_up_date"
               @change="clearError('follow_up_date')"
               :class="{'border-red-500': errors.follow_up_date}"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.follow_up_date">
            <p class="text-red-500 text-xs mt-1" x-text="errors.follow_up_date[0]"></p>
        </template>
    </div>
</div>
