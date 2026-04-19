# EduSphere UX Updates Tracker

## Status Legend
- ✅ Done
- 🔄 In Progress
- ⏳ Pending

---

## Completed Updates

### Hostel Module
- ✅ `receptionist/hostels/index` — AJAX data table, stat cards, page header, dual SSR+Alpine tbody
- ✅ `receptionist/hostel-floors/index` — AJAX data table pattern
- ✅ `receptionist/hostel-rooms/index` — AJAX data table pattern
- ✅ `receptionist/hostel-bed-assignments/index` — AJAX data table, plain language labels
- ✅ `receptionist/hostel-attendance/index` — Card grid UX, live counters, progress bar, save bug fixed
- ✅ `receptionist/hostel-attendance/report` — Clean filter bar, readable table

### Transport Module
- ✅ `receptionist/transport-attendance/index` — Card grid UX matching hostel attendance
- ✅ `receptionist/transport-attendance/month-wise-report` — Clean filter bar, calendar table
- ✅ `receptionist/transport-assignments/index` — Plain language, consistent buttons
- ✅ `receptionist/routes/index` — (existing)
- ✅ `receptionist/vehicles/index` — (existing)
- ✅ `receptionist/bus-stops/index` — (existing)

### Staff Module
- ✅ `receptionist/staff/index` — AJAX data table pattern matching visitor list

### Admission Module (Receptionist)
- ✅ `receptionist/admission/show` — Hero card, 2/3+1/3 grid, detail rows, photos/signatures sidebar
- ✅ `receptionist/student-registrations/show` — Same design as admission show
- ✅ `receptionist/student-registrations/index` — View + PDF + Edit + Delete icons

### Admission Module (School Admin)
- ✅ `school/admission/show` — Hero card, 2/3+1/3 grid, detail rows
- ✅ `school/student-registrations/show` — Same design as admission show
- ✅ `school/student-registrations/index` — View + PDF + Edit + Delete icons

### PDF Documents
- ✅ `pdf/student-admission` — Professional layout: header, hero row, two-column sections, photos, signatures, footer
- ✅ `pdf/student-registration` — Same professional layout as admission PDF

### User Management (School Admin)
- ✅ `school/users/index` — AJAX data table matching visitor pattern, stat cards, modal

### Plain Language / Jargon Removal
- ✅ `receptionist/hostel-attendance/index` — All jargon replaced
- ✅ `receptionist/hostel-attendance/report` — All jargon replaced
- ✅ `receptionist/hostel-bed-assignments/index` — All jargon replaced
- ✅ `receptionist/transport-assignments/index` — All jargon replaced
- ✅ `receptionist/transport-attendance/index` — All jargon replaced
- ✅ `receptionist/staff/index` — Jargon in notice card replaced

### Bug Fixes
- ✅ Hostel attendance save — double blink fixed, toast now shows before navigation
- ✅ Hostel attendance — student_id type mismatch (string vs int) in array_diff fixed
- ✅ Transport attendance — same save bug fixed

---

## Pending Updates

### Student Enquiry Form ⏳ HIGH PRIORITY
- ⏳ `school/student-enquiries/partials/form` — Rewrite with 4-tab stepped layout
- ⏳ `school/student-enquiries/create` — Update wrapper
- ⏳ `school/student-enquiries/edit` — Update wrapper
- ⏳ `receptionist/student-enquiries/partials/form` — Mirror school form
- ⏳ `receptionist/student-enquiries/create` — Update wrapper
- ⏳ `receptionist/student-enquiries/edit` — Update wrapper

**Planned UX for Enquiry Form:**
- Tab 1: Enquiry Info (academic year, class, student name, gender, follow-up date, subject)
- Tab 2: Parent Details (father name/contact required; optional fields collapsible; mother same)
- Tab 3: Contact & Personal (contact no, whatsapp required; DOB, address, religion, category etc.)
- Tab 4: Photos (student, father, mother photo upload with preview)
- Sticky save bar at bottom showing current tab + save button
- `modal-input-premium` styling on all inputs
- Auto-expand to correct tab on validation error
- Tab progress indicator (1 of 4)

### Master Data Listings (School Admin) ⏳
All 13 modules need controller + view update to match blood group AJAX pattern:
- ⏳ `school/academic-years/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/classes/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/sections/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/subjects/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/admission-codes/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/admission-news/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/boarding-types/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/categories/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/qualifications/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/registration-codes/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/school-banks/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/student-types/index` — Add HasAjaxDataTable to controller + rewrite view
- ⏳ `school/miscellaneous-fees/index` — Add HasAjaxDataTable to controller + rewrite view

### Super Admin School Details ⏳
- ⏳ `admin/schools/show` — Hero card, stat cards, 2/3+1/3 grid, subscription card, quick actions
- ⏳ `admin/schools/partials/_detail_row` — Create shared partial

### User Management Filter Bug ⏳
- ⏳ `school/users/index` — 500 error on filter select — fix controller AJAX branch

---

## Design System Reference

### Components Used
- `<x-stat-card>` — stat cards with alpine-text binding
- `<x-page-header>` — page title + action buttons
- `<x-table.search>` — search input
- `<x-table.filter-select>` — filter dropdown
- `<x-table.per-page>` — per page selector
- `<x-table.sort-header>` — sortable column header
- `<x-table.loading-overlay>` — loading spinner
- `<x-table.empty-state>` — empty table state
- `<x-table.pagination>` — pagination controls
- `<x-modal>` — modal wrapper
- `<x-confirm-modal>` — delete confirmation modal

### Input Styling
- All form inputs: `modal-input-premium`
- Error state: `border-red-500 ring-red-500/10`
- Error message: `modal-error-message`
- Labels: `modal-label-premium`

### AJAX Table Pattern (Blood Group Reference)
```js
Object.assign(ajaxDataTable({
    fetchUrl: '{{ route('school.X.fetch') }}',
    defaultSort: 'created_at',
    defaultDirection: 'desc',
    defaultPerPage: 25,
    initialRows: @js($initialData['rows']),
    initialPagination: @js($initialData['pagination']),
    initialStats: @js($initialData['stats']),
}), moduleManagement())
```

### Controller Pattern (Blood Group Reference)
```php
use HasAjaxDataTable;

public function index(Request $request) {
    $transformer = fn($row) => [...];
    $query = Model::where('school_id', $schoolId);
    // apply search/sort
    if ($request->expectsJson() || $request->ajax()) {
        return $this->handleAjaxTable($query, $transformer, $stats);
    }
    $initialData = $this->getHydrationData($query, $transformer, ['stats' => $stats]);
    return view('...', ['initialData' => $initialData, 'stats' => $stats]);
}
```

### Route Pattern
```php
Route::post('module-name/fetch', [Controller::class, 'index'])->name('module-name.fetch');
Route::resource('module-name', Controller::class);
```
