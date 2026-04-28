<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Role;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageGrades($user);
    }

    public function view(User $user, Grade $grade): bool
    {
        return $this->canManageGrades($user)
            && $this->belongsToCurrentSchool($grade->school_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageGrades($user);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $this->canManageGrades($user)
            && $this->belongsToCurrentSchool($grade->school_id);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }

    protected function canManageGrades(User $user): bool
    {
        $currentSchoolId = $this->currentSchoolId();

        return $user->isActive()
            && $user->hasRole(Role::SCHOOL_ADMIN)
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

        return optional(app('currentSchool'))->id;
    }
}
