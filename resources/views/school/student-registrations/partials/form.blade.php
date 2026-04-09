<div class="space-y-6">
    @include('school.student-registrations.partials._registration_info')
    @include('school.student-registrations.partials._personal_info')
    @include('school.student-registrations.partials._father_details')
    @include('school.student-registrations.partials._mother_details')
    @include('school.student-registrations.partials._permanent_address')
    @include('school.student-registrations.partials._correspondence_address')
    @include('school.student-registrations.partials._photo_details')
    @include('school.student-registrations.partials._signature_details')

    <div class="flex justify-end gap-4 mt-8">
        <button type="reset" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            Reset
        </button>
        <button type="submit" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
            {{ isset($studentRegistration) ? 'Update Registration' : 'Submit Registration' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
/**
 * Fix form submission issues caused by Select2 and disabled selects:
 * 1. Select2-wrapped selects with required attribute cannot be focused by
 *    native browser validation → silently blocks submission.
 * 2. Disabled selects (state/city before selection) don't submit values →
 *    server never receives them, validation errors appear but old() is empty.
 * We strip required from Select2 elements and enable disabled selects
 * right before submit, letting Laravel handle validation server-side.
 */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            // Fix Select2 hidden selects blocking browser validation
            form.querySelectorAll('select.select2-hidden-accessible').forEach(function(select) {
                select.removeAttribute('required');
            });
            // Fix disabled selects not sending values
            form.querySelectorAll('select[disabled]').forEach(function(select) {
                select.removeAttribute('disabled');
            });
        }, true);
    });
});
</script>
@endpush
