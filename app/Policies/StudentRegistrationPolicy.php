<?php

namespace App\Policies;

use App\Models\StudentRegistration;
use App\Models\User;

class StudentRegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $this->canManageRegistrations($user)
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, StudentRegistration $studentRegistration): bool
    {
        return $this->canManageRegistrations($user)
            && $user->canAccessSchool($studentRegistration->school_id);
    }

    public function update(User $user, StudentRegistration $studentRegistration): bool
    {
        return $this->view($user, $studentRegistration);
    }

    public function delete(User $user, StudentRegistration $studentRegistration): bool
    {
        return $this->view($user, $studentRegistration);
    }

    private function canManageRegistrations(User $user): bool
    {
        return $user->isActive()
            && ($user->isSchoolAdmin() || $user->isReceptionist());
    }
}
