<?php

namespace App\Policies;

use App\Models\Fee;
use App\Models\Role;
use App\Models\User;

class FeePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageFees($user);
    }

    public function view(User $user, Fee $fee): bool
    {
        return $this->canManageFees($user)
            && $this->belongsToCurrentSchool($fee->school_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageFees($user);
    }

    public function update(User $user, Fee $fee): bool
    {
        return $this->canManageFees($user)
            && $this->belongsToCurrentSchool($fee->school_id);
    }

    public function delete(User $user, Fee $fee): bool
    {
        return $this->update($user, $fee);
    }

    protected function canManageFees(User $user): bool
    {
        $currentSchoolId = $this->currentSchoolId();

        return $user->isActive()
            && ($user->hasRole(Role::SCHOOL_ADMIN) || $user->hasRole(Role::RECEPTIONIST))
            && !is_null($currentSchoolId)
            && (int) $user->school_id === (int) $currentSchoolId;
    }

    protected function belongsToCurrentSchool(?int $schoolId): bool
    {
        $currentSchoolId = $this->currentSchoolId();

        return !is_null($schoolId)
            && !is_null($currentSchoolId)
            && (int) $schoolId === (int) $currentSchoolId;
    }

    private function currentSchoolId(): ?int
    {
        if (!app()->bound('currentSchool')) {
            return null;
        }

        $school = app('currentSchool');

        return $school?->id;
    }
}
