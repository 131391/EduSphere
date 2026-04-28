<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\Role;
use App\Models\User;

class ResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive()
            && (
                $user->hasRole(Role::SCHOOL_ADMIN)
                || $user->hasRole(Role::TEACHER)
                || $user->hasRole(Role::STUDENT)
                || $user->hasRole(Role::PARENT)
            );
    }

    public function view(User $user, Result $result): bool
    {
        if (!$this->belongsToCurrentSchool($result->school_id)) {
            return false;
        }

        if ($user->hasRole(Role::SCHOOL_ADMIN) || $user->hasRole(Role::TEACHER)) {
            return true;
        }

        if ($user->hasRole(Role::STUDENT)) {
            return optional($user->student)->id === $result->student_id;
        }

        if ($user->hasRole(Role::PARENT)) {
            $parent = $user->parent;
            if (!$parent) {
                return false;
            }

            return $parent->students()
                ->whereKey($result->student_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && (
                $user->hasRole(Role::SCHOOL_ADMIN)
                || $user->hasRole(Role::TEACHER)
            );
    }

    public function update(User $user, Result $result): bool
    {
        if ($result->isLocked()) {
            return $user->hasRole(Role::SCHOOL_ADMIN)
                && $this->belongsToCurrentSchool($result->school_id);
        }

        return $this->create($user)
            && $this->belongsToCurrentSchool($result->school_id);
    }

    public function delete(User $user, Result $result): bool
    {
        return $user->hasRole(Role::SCHOOL_ADMIN)
            && $this->belongsToCurrentSchool($result->school_id)
            && !$result->isLocked();
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
