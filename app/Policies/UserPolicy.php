<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    private const MANAGEABLE_ROLE_SLUGS = [
        'teacher',
        'receptionist',
        'accountant',
        'librarian',
    ];

    public function viewAny(User $user): bool
    {
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $user->isActive()
            && $user->isSchoolAdmin()
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $managedUser): bool
    {
        return $this->canManageUser($user, $managedUser);
    }

    public function delete(User $user, User $managedUser): bool
    {
        return $this->canManageUser($user, $managedUser)
            && !$managedUser->isSchoolAdmin();
    }

    private function canManageUser(User $user, User $managedUser): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        if (!$user->canAccessSchool($managedUser->school_id)) {
            return false;
        }

        return in_array($managedUser->role?->slug, self::MANAGEABLE_ROLE_SLUGS, true);
    }
}
