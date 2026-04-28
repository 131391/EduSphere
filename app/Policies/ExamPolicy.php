<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\Role;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageExams($user);
    }

    public function view(User $user, Exam $exam): bool
    {
        return $this->canManageExams($user)
            && $this->belongsToCurrentSchool($exam->school_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageExams($user);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $this->canManageExams($user)
            && $this->belongsToCurrentSchool($exam->school_id);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $this->update($user, $exam);
    }

    public function cancel(User $user, Exam $exam): bool
    {
        return $this->update($user, $exam);
    }

    public function lock(User $user, Exam $exam): bool
    {
        return $this->update($user, $exam);
    }

    public function enterMarks(User $user, Exam $exam): bool
    {
        return $this->belongsToCurrentSchool($exam->school_id)
            && $user->isActive()
            && (
                $user->hasRole(Role::SCHOOL_ADMIN)
                || $user->hasRole(Role::TEACHER)
            );
    }

    protected function canManageExams(User $user): bool
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
