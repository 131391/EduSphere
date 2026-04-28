<?php

namespace App\Policies;

use App\Models\ExamType;
use App\Models\Role;
use App\Models\User;

class ExamTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, ExamType $examType): bool
    {
        return $this->canManage($user)
            && $this->belongsToCurrentSchool($examType->school_id);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, ExamType $examType): bool
    {
        return $this->canManage($user)
            && $this->belongsToCurrentSchool($examType->school_id);
    }

    public function delete(User $user, ExamType $examType): bool
    {
        return $this->update($user, $examType);
    }

    protected function canManage(User $user): bool
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
