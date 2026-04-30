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
        if (!$user->isActive()) {
            return false;
        }

        // Super-admin: platform-wide user listing (Admin/UserController).
        if ($user->isSuperAdmin()) {
            return true;
        }

        // School-admin: scoped to their current school.
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $user->isSchoolAdmin()
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $managedUser): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->canSchoolAdminManage($user, $managedUser);
    }

    public function delete(User $user, User $managedUser): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            // Super-admin cannot delete other super-admins through the
            // standard policy surface; that should be an explicitly audited
            // path, not a generic CRUD action.
            return !$managedUser->isSuperAdmin();
        }

        return $this->canSchoolAdminManage($user, $managedUser)
            && !$managedUser->isSchoolAdmin();
    }

    private function canSchoolAdminManage(User $user, User $managedUser): bool
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
