# YesNo Enum Standardization Plan

## Current State Analysis

### ✅ Already Using YesNo Enum Correctly
- `StudentRegistration`: `is_single_parent`, `is_transport_required` → cast to `YesNo::class`
- `Student`: `is_single_parent`, `is_transport_required` → cast to `YesNo::class`
- `AcademicYear`: `is_current` → cast to `YesNo::class`
- `HostelRoom`: `ac`, `cooler`, `fan` → using `YesNo::options()` in views

### ❌ Using String "Yes"/"No" (Should Use YesNo Enum)
**StudentEnquiry Model** - 3 fields storing strings:
- `transport_facility` → stores "Yes"/"No" strings
- `hostel_facility` → stores "Yes"/"No" strings  
- `minority` → stores "Yes"/"No" strings

**Views affected:**
- `resources/views/school/student-enquiries/partials/step3.blade.php` (lines 145-146, 154-155, 173-174)

### 🔧 Using Integer 0/1 (Already Compatible with YesNo Enum)
**Views using value="0"/"1" for YesNo enum fields:**
- `resources/views/receptionist/student-registrations/partials/step2_student.blade.php` (lines 143-144, 164-165)
- `resources/views/school/student-registrations/partials/step2_student.blade.php` (lines 143-144, 164-165)

These are **already correct** since YesNo enum is backed by integers (0=No, 1=Yes).

---

## Recommended Actions

### Phase 1: Fix StudentEnquiry Model (Breaking Change)
1. Add migration to change column types from `string` to `tinyint`:
   ```php
   $table->tinyInteger('transport_facility')->default(0)->change();
   $table->tinyInteger('hostel_facility')->default(0)->change();
   $table->tinyInteger('minority')->default(0)->change();
   ```

2. Add data migration to convert existing strings to integers:
   ```php
   DB::table('student_enquiries')
       ->update([
           'transport_facility' => DB::raw("CASE WHEN transport_facility = 'Yes' THEN 1 ELSE 0 END"),
           'hostel_facility' => DB::raw("CASE WHEN hostel_facility = 'Yes' THEN 1 ELSE 0 END"),
           'minority' => DB::raw("CASE WHEN minority = 'Yes' THEN 1 ELSE 0 END"),
       ]);
   ```

3. Update StudentEnquiry model casts:
   ```php
   protected $casts = [
       // ... existing casts
       'transport_facility' => YesNo::class,
       'hostel_facility' => YesNo::class,
       'minority' => YesNo::class,
   ];
   ```

4. Update view to use YesNo::options():
   ```blade
   @foreach(YesNo::options() as $value => $label)
       <option value="{{ $value }}">{{ $label }}</option>
   @endforeach
   ```

### Phase 2: Verify All YesNo Enum Usage
Search for all `@if($model->field)` checks on YesNo enum fields and replace with:
```blade
@if($model->field === \App\Enums\YesNo::Yes)
```

### Phase 3: Document Standard Pattern
Add to project documentation:
- All boolean-like fields MUST use `YesNo` enum (backed by tinyint 0/1)
- Never use string "Yes"/"No" or boolean true/false for database storage
- Always use `YesNo::options()` in Blade dropdowns
- Always use strict comparison `=== YesNo::Yes` in conditionals

---

## Impact Assessment

### Low Risk (Already Working)
- `is_transport_required`, `is_single_parent` in Student/StudentRegistration
- `is_current` in AcademicYear
- `ac`, `cooler`, `fan` in HostelRoom

### Medium Risk (Requires Migration)
- `transport_facility`, `hostel_facility`, `minority` in StudentEnquiry
- Need to migrate existing string data to integers
- Need to update validation rules if they check for "Yes"/"No" strings

### Files to Update
1. **Migration**: Create `convert_student_enquiry_yes_no_to_enum.php`
2. **Model**: `app/Models/StudentEnquiry.php` - add casts
3. **Views**: `resources/views/school/student-enquiries/partials/step3.blade.php` - use YesNo::options()
4. **Validation**: Check `StudentEnquiryRequest` for string validation rules

---

## Recommendation

**Proceed with Phase 1** to standardize StudentEnquiry fields. This is the only model storing yes/no as strings. All other models already use the enum correctly.

The integer 0/1 values in views are already compatible with YesNo enum (it's backed by int), so no changes needed there.
