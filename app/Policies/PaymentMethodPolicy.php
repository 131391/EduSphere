<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManagePaymentMethods($user);
    }

    public function create(User $user): bool
    {
        return $this->canManagePaymentMethods($user);
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->canManagePaymentMethods($user)
            && $this->belongsToCurrentSchool($user, $paymentMethod->school_id);
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->update($user, $paymentMethod);
    }

    protected function canManagePaymentMethods(User $user): bool
    {
        return $user->isActive()
            && $user->hasRole(Role::SCHOOL_ADMIN)
            && !is_null(app('currentSchool', [])->id ?? null)
            && $user->school_id === app('currentSchool')->id;
    }

    protected function belongsToCurrentSchool(User $user, ?int $schoolId): bool
    {
        return !is_null($schoolId)
            && $this->canManagePaymentMethods($user)
            && $schoolId === app('currentSchool')->id;
    }
}
