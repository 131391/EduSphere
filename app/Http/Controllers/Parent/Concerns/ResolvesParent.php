<?php

namespace App\Http\Controllers\Parent\Concerns;

use App\Models\StudentParent;
use Illuminate\Support\Facades\Auth;

/**
 * Shared parent-resolution + tenant-scoping helpers for the Parent portal.
 *
 * Every parent-facing controller already runs through the `tenant` and
 * `school.access` middleware, so `app('currentSchool')` is bound. This trait
 * adds the second layer: it ensures the StudentParent record actually belongs
 * to that bound school, and that student IDs are filtered by the same scope.
 */
trait ResolvesParent
{
    protected function currentParentOrFail(): StudentParent
    {
        $parent = optional(Auth::user())->parent;

        if (!$parent || (int) $parent->school_id !== (int) $this->getSchoolId()) {
            abort(403, 'Parent profile not found for the current school.');
        }

        return $parent;
    }

    /**
     * Return the IDs of students linked to this parent AND in the current
     * tenant — so even if the pivot has stale rows pointing to other schools
     * the query stays clean.
     */
    protected function ownedStudentIds(StudentParent $parent)
    {
        return $parent->students()
            ->where('students.school_id', $this->getSchoolId())
            ->pluck('students.id');
    }
}
