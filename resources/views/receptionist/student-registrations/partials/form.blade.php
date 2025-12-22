<div class="space-y-6">
    @include('receptionist.student-registrations.partials._registration_info')
    @include('receptionist.student-registrations.partials._personal_info')
    @include('receptionist.student-registrations.partials._father_details')
    @include('receptionist.student-registrations.partials._mother_details')
    @include('receptionist.student-registrations.partials._permanent_address')
    @include('receptionist.student-registrations.partials._correspondence_address')
    @include('receptionist.student-registrations.partials._photo_details')
    @include('receptionist.student-registrations.partials._signature_details')

    <div class="flex justify-end gap-4 mt-8">
        <button type="reset" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            Reset
        </button>
        <button type="submit" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
            {{ isset($studentRegistration) ? 'Update Registration' : 'Submit Registration' }}
        </button>
    </div>
</div>
