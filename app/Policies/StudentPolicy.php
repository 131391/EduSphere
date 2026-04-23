<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $this->canManageAdmissions($user)
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, Student $student): bool
    {
        return $this->canManageAdmissions($user)
            && $user->canAccessSchool($student->school_id);
    }

    public function update(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    private function canManageAdmissions(User $user): bool
    {
        return $user->isActive()
            && ($user->isSchoolAdmin() || $user->isReceptionist());
    }
}
