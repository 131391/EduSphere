{{-- Step 1: Admission Info --}}

{{-- Autofill banner --}}
<div x-show="autofillBanner" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
    <i class="fas fa-magic text-emerald-500"></i>
    <span>Form auto-filled from registration. Review admission details below.</span>
    <button type="button" @click="autofillBanner = false" class="ml-auto text-emerald-400 hover:text-emerald-600">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>

@isset($student)
{{-- ── EDIT MODE: show locked registration info ── --}}
@php
    $regLabel = $student->registration_no ?? 'N/A';
    if ($student->registration_no) {
        $reg = $registrations->firstWhere('registration_no', $student->registration_no);
        if ($reg) $regLabel .= ' — ' . $reg->first_name . ' ' . $reg->last_name;
    }
@endphp
<div class="mb-5 p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-xl flex items-center gap-3">
    <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-file-alt text-white text-xs"></i>
    </div>
    <div>
        <p class="text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wide">Linked Registration</p>
        <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $regLabel }}</p>
    </div>
</div>
<input type="hidden" name="registration_no" value="{{ $student->registration_no }}">

@else
{{-- ── CREATE MODE: registration picker (required) ── --}}

@if($registrations->isEmpty())
{{-- No pending registrations — block the form --}}
<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
        <i class="fas fa-exclamation-triangle text-amber-500 text-2xl"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">No Pending Registrations</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6">
        Every admission requires a completed student registration first. There are no pending registrations available to admit.
    </p>
    <div class="flex items-center gap-3">
        @php
            $regCreateRoute = Route::has('receptionist.student-registrations.create')
                ? route('receptionist.student-registrations.create')
                : route('school.student-registrations.create');
            $regIndexRoute = Route::has('receptionist.student-registrations.index')
                ? route('receptionist.student-registrations.index')
                : route('school.student-registrations.index');
        @endphp
        <a href="{{ $regCreateRoute }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
            <i class="fas fa-plus text-xs"></i> Create Registration First
        </a>
        <a href="{{ $regIndexRoute }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-all">
            View Registrations
        </a>
    </div>
</div>
@else

<div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl flex items-start gap-3">
    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
    <p class="text-sm text-blue-700 dark:text-blue-300 dark:text-blue-300">
        Select a registration to begin. All student details will be auto-filled from the registration record.
        <strong>A registration is required to proceed.</strong>
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Registration picker — REQUIRED --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Registration <span class="text-red-500">*</span>
        </label>
        <select name="registration_id" x-model="formData.registration_id"
                @change="fetchRegistrationData(); clearError('registration_id')"
                :class="errors.registration_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">— Select a pending registration —</option>
            @foreach($registrations as $reg)
                <option value="{{ $reg->id }}">
                    {{ $reg->registration_no }} — {{ $reg->first_name }} {{ $reg->last_name }}
                    ({{ $reg->class?->name ?? 'N/A' }})
                </option>
            @endforeach
        </select>
        <input type="hidden" name="registration_no" x-model="formData.registration_no">
        <template x-if="errors.registration_id">
            <template x-if="errors.registration_id[0]"><p class="modal-error-message" x-text="errors.registration_id[0]"></p></template>
        </template>
    </div>

    {{-- The rest of step 1 is only shown after a registration is selected --}}
    <template x-if="formData.registration_id">
        <div class="md:col-span-2 contents">
    </template>

</div>

{{-- Fields shown only after registration selected --}}
<div x-show="formData.registration_id" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

    {{-- Academic Year (locked from registration) --}}
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
            <template x-if="errors.academic_year_id[0]"><p class="modal-error-message" x-text="errors.academic_year_id[0]"></p></template>
        </template>
    </div>

    {{-- Class (locked from registration) --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Class <span class="text-red-500">*</span>
        </label>
        <select name="class_id" x-model="formData.class_id"
                @change="loadClassData(formData.class_id); clearError('class_id')"
                :class="errors.class_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Select Class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <template x-if="errors.class_id">
            <template x-if="errors.class_id[0]"><p class="modal-error-message" x-text="errors.class_id[0]"></p></template>
        </template>
    </div>

    {{-- Section --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Section <span class="text-red-500">*</span>
        </label>
        <select name="section_id" x-model="formData.section_id"
                @change="clearError('section_id')"
                :class="errors.section_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Select Section</option>
            <template x-for="section in sections" :key="section.id">
                <option :value="section.id" x-text="section.name" :selected="section.id == formData.section_id"></option>
            </template>
        </select>
        <template x-if="errors.section_id">
            <template x-if="errors.section_id[0]"><p class="modal-error-message" x-text="errors.section_id[0]"></p></template>
        </template>
    </div>

    {{-- Admission Fee (auto from class) --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Admission Fee</label>
        <div class="relative">
            <input type="number" step="0.01" name="admission_fee" x-model="formData.admission_fee" readonly
                   placeholder="Auto-calculated from class"
                   class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 cursor-not-allowed text-gray-600 dark:text-gray-400">
            <span x-show="formData.admission_fee > 0"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Auto</span>
        </div>
        <p class="text-[10px] text-gray-400 mt-1">Auto-calculated based on selected class</p>
    </div>

    {{-- Roll No --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Roll No <span class="text-red-500">*</span>
        </label>
        <input type="text" name="roll_no" x-model="formData.roll_no" @input="clearError('roll_no')"
               placeholder="Enter roll number"
               :class="errors.roll_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
               class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.roll_no">
            <template x-if="errors.roll_no[0]"><p class="modal-error-message" x-text="errors.roll_no[0]"></p></template>
        </template>
    </div>

    {{-- Receipt No --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Receipt No <span class="text-red-500">*</span>
        </label>
        <input type="text" name="receipt_no" x-model="formData.receipt_no" @input="clearError('receipt_no')"
               placeholder="Enter receipt number"
               :class="errors.receipt_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
               class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.receipt_no">
            <template x-if="errors.receipt_no[0]"><p class="modal-error-message" x-text="errors.receipt_no[0]"></p></template>
        </template>
    </div>

    {{-- Admission Date --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Admission Date <span class="text-red-500">*</span>
        </label>
        <input type="date" name="admission_date" x-model="formData.admission_date" @input="clearError('admission_date')"
               :class="errors.admission_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
               class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.admission_date">
            <template x-if="errors.admission_date[0]"><p class="modal-error-message" x-text="errors.admission_date[0]"></p></template>
        </template>
    </div>

    {{-- Referred By --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Referred By</label>
        <select name="referred_by" x-model="formData.referred_by"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Referred By</option>
            <option value="Staff">Staff</option>
            <option value="Parent">Parent</option>
            <option value="Other">Other</option>
        </select>
    </div>

</div>
@endif
@endisset
