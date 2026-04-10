<div class="space-y-6">
    @include('receptionist.student-registrations.partials._registration_info')
    @include('receptionist.student-registrations.partials._personal_info')
    @include('receptionist.student-registrations.partials._father_details')
    @include('receptionist.student-registrations.partials._mother_details')
    @include('receptionist.student-registrations.partials._permanent_address')
    @include('receptionist.student-registrations.partials._correspondence_address')
    @include('receptionist.student-registrations.partials._photo_details')
    @include('receptionist.student-registrations.partials._signature_details')

    <div class="flex justify-end gap-4 mt-8 hidden">
        {{-- Legacy buttons hidden, parent view handles modern action buttons --}}
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
