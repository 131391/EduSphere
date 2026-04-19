{{-- Step 1: Registration Info --}}

{{-- Edit-only: Status radio pills --}}
@isset($studentRegistration)
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admission Status</label>
    <div class="flex flex-wrap gap-2">
        @foreach(\App\Enums\AdmissionStatus::cases() as $s)
        @php $c = $s->color(); @endphp
        <label class="relative cursor-pointer">
            <input type="radio" name="admission_status" value="{{ $s->value }}"
                   x-model="formData.admission_status"
                   class="sr-only peer">
            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border-2 text-sm font-semibold transition-all cursor-pointer
                         border-gray-200 text-gray-500 bg-white dark:bg-gray-800 dark:border-gray-600
                         peer-checked:border-{{ $c }}-500 peer-checked:bg-{{ $c }}-50 peer-checked:text-{{ $c }}-700">
                <i class="fas {{ match($s) {
                    \App\Enums\AdmissionStatus::Pending   => 'fa-clock',
                    \App\Enums\AdmissionStatus::Admitted  => 'fa-user-check',
                    \App\Enums\AdmissionStatus::Cancelled => 'fa-times-circle',
                } }} text-xs"></i>
                {{ $s->label() }}
            </span>
        </label>
        @endforeach
    </div>
    <template x-if="errors.admission_status">
        <p class="text-red-500 text-xs mt-1" x-text="errors.admission_status[0]"></p>
    </template>
</div>
@endisset

{{-- Enquiry auto-fill banner --}}
<div x-show="autofillBanner" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
    <i class="fas fa-magic text-emerald-500"></i>
    <span>Form auto-filled from enquiry data. Review and adjust as needed.</span>
    <button type="button" @click="autofillBanner = false" class="ml-auto text-emerald-400 hover:text-emerald-600">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Enquiry No --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Enquiry No
            <span class="ml-1 text-gray-400 text-xs font-normal">(optional — auto-fills form)</span>
        </label>
        @isset($studentRegistration)
            <input type="text" readonly
                   value="{{ $studentRegistration->enquiry ? $studentRegistration->enquiry->enquiry_no . ' — ' . $studentRegistration->enquiry->student_name : 'N/A' }}"
                   class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-500 cursor-not-allowed text-sm">
            <input type="hidden" name="enquiry_id" value="{{ $studentRegistration->enquiry_id }}">
        @else
            <select name="enquiry_id" x-model="formData.enquiry_id"
                    @change="fetchEnquiryData(); clearError('enquiry_id')"
                    :class="errors.enquiry_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                    class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
                <option value="">Select Enquiry (optional)</option>
                @foreach($enquiries as $enquiry)
                    <option value="{{ $enquiry->id }}">{{ $enquiry->enquiry_no }} — {{ $enquiry->student_name }}</option>
                @endforeach
            </select>
            <template x-if="errors.enquiry_id">
                <p class="text-red-500 text-xs mt-1" x-text="errors.enquiry_id[0]"></p>
            </template>
        @endisset
    </div>

    {{-- Academic Year --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Academic Year <span class="text-red-500">*</span>
        </label>
        <select name="academic_year_id" x-model="formData.academic_year_id"
                @change="clearError('academic_year_id')"
                :class="errors.academic_year_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Select Academic Year</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.academic_year_id">
            <p class="text-red-500 text-xs mt-1" x-text="errors.academic_year_id[0]"></p>
        </template>
    </div>

    {{-- Class --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            For Class <span class="text-red-500">*</span>
        </label>
        <select name="class_id" x-model="formData.class_id"
                @change="updateFee(); clearError('class_id')"
                :class="errors.class_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Select Class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.class_id">
            <p class="text-red-500 text-xs mt-1" x-text="errors.class_id[0]"></p>
        </template>
    </div>

    {{-- Registration Fee --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Registration Fee</label>
        <div class="relative">
            <input type="number" step="0.01" name="registration_fee" x-model="formData.registration_fee" readonly
                   placeholder="Auto-calculated from class"
                   class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 cursor-not-allowed text-gray-600 dark:text-gray-400">
            <span x-show="formData.registration_fee > 0"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                Auto
            </span>
        </div>
        <p class="text-[10px] text-gray-400 mt-1">Auto-calculated based on selected class</p>
        <template x-if="errors.registration_fee">
            <p class="text-red-500 text-xs mt-1" x-text="errors.registration_fee[0]"></p>
        </template>
    </div>

</div>
