# Error Text Consistency Audit & Plan

## Target Format (from screenshot - hostel modal)
```
The hostel name field is required.
```
- Color: `text-red-500`
- Size: `text-xs` (0.75rem)
- Weight: `font-medium` or `font-semibold`
- Icon: `fas fa-circle-exclamation` or `fas fa-exclamation-circle` prefix
- Margin: `mt-1`
- Display: inline-flex with icon

## Standard CSS Class (already defined in app.css)
```css
.modal-error-message {
    font-size: 0.75rem;
    font-weight: 700;
    color: #ef4444;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}
.modal-error-message::before {
    content: "\f06a"; /* fa-exclamation-circle */
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
}
```

## Current Inconsistencies Found

### Pattern 1: `modal-error-message` (CORRECT - used in school modals)
```blade
<template x-if="errors.field">
    <p class="modal-error-message" x-text="errors.field[0]"></p>
</template>
```
Files: school/waivers, school/examination/*, school/users, school/late-fee, etc.

### Pattern 2: `text-xs text-red-500 mt-1` (INCONSISTENT)
```blade
<p class="text-red-500 text-xs mt-1" x-text="errors.field[0]"></p>
```
Files: receptionist/student-registrations/partials/step*.blade.php (all steps)

### Pattern 3: `text-[10px] font-bold text-red-500 ml-1` (INCONSISTENT)
```blade
<p x-text="errors.field[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
```
Files: receptionist/hostel-rooms, receptionist/routes, receptionist/vehicles

### Pattern 4: `text-xs text-red-600 font-medium` (INCONSISTENT)
```blade
<p class="mt-1 text-xs text-red-600 font-medium" x-text="getError('field')"></p>
```
Files: admin/schools/create.blade.php, admin/schools/edit.blade.php

### Pattern 5: `text-[11px] font-semibold text-red-500` (INCONSISTENT)
```blade
<p x-show="errors.name" x-text="errors.name" class="text-[11px] font-semibold text-red-500 flex items-center gap-1">
```
Files: admin/profile.blade.php

### Pattern 6: Blade `@error` directive (INCONSISTENT)
```blade
@error('field')
    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
@enderror
```
Files: various settings pages

## Recommended Standard

Use `modal-error-message` CSS class everywhere for consistency.
For `x-if` pattern (removes from DOM when no error - prevents red dot):
```blade
<template x-if="errors.field">
    <p class="modal-error-message" x-text="errors.field[0]"></p>
</template>
```

For Blade `@error` directive:
```blade
@error('field')
    <p class="modal-error-message">{{ $message }}</p>
@enderror
```

For `getError()` Alpine pattern (admin):
```blade
<template x-if="getError('field')">
    <p class="modal-error-message" x-text="getError('field')"></p>
</template>
```

## Files to Update

### High Priority (Modal forms - user-facing)
1. `receptionist/hostel-rooms/index.blade.php` - Pattern 3
2. `receptionist/routes/index.blade.php` - Pattern 3
3. `receptionist/vehicles/index.blade.php` - Pattern 3
4. `receptionist/student-registrations/partials/step1_reg.blade.php` - Pattern 2
5. `receptionist/student-registrations/partials/step2_student.blade.php` - Pattern 2
6. `receptionist/student-registrations/partials/step3_parents.blade.php` - Pattern 2
7. `receptionist/student-registrations/partials/step4_address.blade.php` - Pattern 2
8. `receptionist/student-registrations/partials/step5_media.blade.php` - Pattern 2
9. `receptionist/transport-attendance/month-wise-report.blade.php` - Pattern 2

### Medium Priority (Admin forms)
10. `admin/schools/create.blade.php` - Pattern 4
11. `admin/schools/edit.blade.php` - Pattern 4
12. `admin/profile.blade.php` - Pattern 5
13. `admin/change-password.blade.php` - Pattern 4

### Also check school registration/admission partials
14. `school/student-registrations/partials/step*.blade.php`
15. `school/admission/partials/step*.blade.php`
