<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Waiver;

class WaiverPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageWaivers($user);
    }

    public function view(User $user, Waiver $waiver): bool
    {
        return $this->canManageWaivers($user)
            && $this->belongsToCurrentSchool($waiver->school_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageWaivers($user);
    }

    public function update(User $user, Waiver $waiver): bool
    {
        return $this->canManageWaivers($user)
            && $this->belongsToCurrentSchool($waiver->school_id);
    }

    public function delete(User $user, Waiver $waiver): bool
    {
        return $this->update($user, $waiver);
    }

    protected function canManageWaivers(User $user): bool
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
