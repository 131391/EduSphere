<?php

namespace App\Http\Controllers\Teacher\Concerns;

use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

trait ResolvesTeacher
{
    protected function currentTeacherOrFail(): Teacher
    {
        $teacher = optional(Auth::user())->teacher;

        if (!$teacher || (int) $teacher->school_id !== (int) $this->getSchoolId()) {
            abort(403, 'Teacher profile not found for the current school.');
        }

        return $teacher;
    }
}
