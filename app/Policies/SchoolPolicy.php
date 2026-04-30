<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\School;
use App\Models\User;

class SchoolPolicy
{
    // ----- School-admin abilities (per-school settings) -----

    public function viewSettings(User $user, School $school): bool
    {
        return $this->canManageSettings($user, $school);
    }

    public function updateSettings(User $user, School $school): bool
    {
        return $this->canManageSettings($user, $school);
    }

    // ----- Super-admin abilities (platform-wide school CRUD) -----

    public function viewAny(User $user): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function view(User $user, School $school): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function update(User $user, School $school): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function delete(User $user, School $school): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function restore(User $user, School $school): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function forceDelete(User $user, School $school): bool
    {
        return $this->isPlatformAdmin($user);
    }

    public function manageFeatures(User $user, School $school): bool
    {
        return $this->isPlatformAdmin($user);
    }

    protected function canManageSettings(User $user, School $school): bool
    {
        return $user->isActive()
            && $user->hasRole(Role::SCHOOL_ADMIN)
            && $user->school_id === $school->id;
    }

    protected function isPlatformAdmin(User $user): bool
    {
        return $user->isActive() && $user->isSuperAdmin();
    }
}
