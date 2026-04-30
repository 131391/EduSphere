<?php

namespace App\Policies;

use App\Models\LateFee;
use App\Models\Role;
use App\Models\User;

class LateFeePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageLateFees($user);
    }

    public function view(User $user, LateFee $lateFee): bool
    {
        return $this->canManageLateFees($user)
            && $this->belongsToCurrentSchool($lateFee->school_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageLateFees($user);
    }

    public function update(User $user, LateFee $lateFee): bool
    {
        return $this->canManageLateFees($user)
            && $this->belongsToCurrentSchool($lateFee->school_id);
    }

    public function delete(User $user, LateFee $lateFee): bool
    {
        return $this->update($user, $lateFee);
    }

    protected function canManageLateFees(User $user): bool
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

        return app('currentSchool')?->id;
    }
}
