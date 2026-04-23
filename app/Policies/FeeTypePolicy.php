<?php

namespace App\Policies;

use App\Models\FeeType;
use App\Models\Role;
use App\Models\User;

class FeeTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageFeeTypes($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageFeeTypes($user);
    }

    public function update(User $user, FeeType $feeType): bool
    {
        return $this->canManageFeeTypes($user)
            && $this->belongsToCurrentSchool($user, $feeType->school_id);
    }

    public function delete(User $user, FeeType $feeType): bool
    {
        return $this->update($user, $feeType);
    }

    protected function canManageFeeTypes(User $user): bool
    {
        return $user->isActive()
            && $user->hasRole(Role::SCHOOL_ADMIN)
            && !is_null(app('currentSchool', [])->id ?? null)
            && $user->school_id === app('currentSchool')->id;
    }

    protected function belongsToCurrentSchool(User $user, ?int $schoolId): bool
    {
        return !is_null($schoolId)
            && $this->canManageFeeTypes($user)
            && $schoolId === app('currentSchool')->id;
    }
}
