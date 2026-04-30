<?php

namespace App\Policies;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;

class FeePaymentPolicy
{
    /**
     * Determine whether the user can view fee payment dashboards and lists.
     */
    public function viewAny(User $user): bool
    {
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $this->isFinanceOperator($user)
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    /**
     * Determine whether the user can collect a payment for the given student.
     */
    public function collect(User $user, Student $student): bool
    {
        return $this->isFinanceOperator($user)
            && $user->canAccessSchool($student->school_id);
    }

    /**
     * Determine whether the user can view a specific fee payment record or receipt.
     */
    public function view(User $user, FeePayment $feePayment): bool
    {
        return $this->isFinanceOperator($user)
            && $user->canAccessSchool($feePayment->school_id);
    }

    /**
     * Determine whether the user can revert/void a receipt.
     *
     * Reverts are addressed by receipt_no (multiple FeePayment rows), so the
     * controller authorizes against the class rather than an instance.
     */
    public function delete(User $user): bool
    {
        $currentSchool = app()->bound('currentSchool') ? app('currentSchool') : null;

        return $this->isFinanceOperator($user)
            && $currentSchool
            && $user->canAccessSchool($currentSchool->id);
    }

    private function isFinanceOperator(User $user): bool
    {
        return $user->isActive()
            && ($user->isSchoolAdmin() || $user->isReceptionist());
    }
}
