# Exam Module - Phase 3a Implementation Plan

## Overview
Surface the existing backend functionality in the UI. This unblocks real users without needing teacher role or PDF work.

## Current State
- ✅ Backend: Exam CRUD, cancel, lock routes exist
- ✅ Backend: ResultService accepts `is_absent`
- ❌ UI: No edit modal, no cancel/lock buttons, no absent checkbox

---

## Task 1: Add Edit Exam Modal to exams/index.blade.php

### Files to Modify
- `resources/views/school/examination/exams/index.blade.php`
- `app/Http/Controllers/School/Examination/ExamController.php`

### Implementation Steps

1. **Add edit endpoint in ExamController**:
   ```php
   public function edit(Exam $exam) // returns JSON for modal
   ```

2. **Add edit route** (already exists in routes/school.php - PUT/PATCH)

3. **Update blade JavaScript**:
   - Add `openEditModal(exam)` function
   - Set formData with exam data when editing
   - Add hidden `_method` field for PUT
   - Change modal title/button based on mode

4. **Update modal**:
   - Dynamic title: "Schedule Exam" vs "Edit Exam"
   - Dynamic button: "Lock Schedule" vs "Update Schedule"

---

## Task 2: Add Cancel/Lock Action Buttons

### Files to Modify
- `resources/views/school/examination/exams/index.blade.php`

### Implementation Steps

1. **Add buttons in table row actions**:
   - Cancel button (for Scheduled/Ongoing status)
   - Lock button (for Completed status)
   - Use conditional rendering based on status

2. **Add JavaScript handlers**:
   - `cancelExam(exam)` - POST to cancel route
   - `lockExam(exam)` - POST to lock route

3. **Style buttons appropriately**:
   - Cancel: red/rose colors
   - Lock: purple/indigo colors (frozen)

---

## Task 3: Add Absent Checkbox to Marks Entry

### Files to Modify
- `resources/views/school/examination/marks/entry.blade.php`
- `app/Services/School/Examination/ResultService.php` (verify)

### Implementation Steps

1. **Add checkbox column in marks grid**:
   - New table header column
   - Checkbox per student row
   - When checked, disable marks input

2. **Update JavaScript**:
   - Add `is_absent` to scores object
   - Toggle marks input disabled state
   - Include in save payload

3. **Verify ResultService**:
   - Confirm `is_absent` is accepted in saveMarks

---

## Task 4: Test All UI Changes

### Verification Checklist
- [ ] Edit modal opens with correct data
- [ ] Edit submits successfully via PUT
- [ ] Cancel button appears for non-terminal exams
- [ ] Lock button appears for completed exams
- [ ] Absent checkbox disables marks input
- [ ] Absent students saved correctly
- [ ] No console errors on page load

---

## Dependencies
- ExamService: update(), cancel(), lock() - already exist
- ResultService: saveMarks() - already accepts is_absent
- Routes: already defined in routes/school.php

## Estimated Effort
- Task 1: ~1 hour
- Task 2: ~30 minutes
- Task 3: ~1 hour
- Task 4: ~30 minutes
- **Total: ~3 hours**