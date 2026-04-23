<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\School;
use App\Models\User;

class SchoolPolicy
{
    public function viewSettings(User $user, School $school): bool
    {
        return $this->canManageSettings($user, $school);
    }

    public function updateSettings(User $user, School $school): bool
    {
        return $this->canManageSettings($user, $school);
    }

    protected function canManageSettings(User $user, School $school): bool
    {
        return $user->isActive()
            && $user->hasRole(Role::SCHOOL_ADMIN)
            && $user->school_id === $school->id;
    }
}
