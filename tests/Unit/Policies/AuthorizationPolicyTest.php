<?php

namespace Tests\Unit\Policies;

use App\Enums\UserStatus;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\User;
use App\Policies\FeeTypePolicy;
use App\Policies\FeePaymentPolicy;
use App\Policies\PaymentMethodPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentRegistrationPolicy;
use App\Policies\UserPolicy;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    public function test_fee_payment_policy_allows_active_receptionist_in_same_school(): void
    {
        $school = $this->makeSchool(10);
        app()->instance('currentSchool', $school);

        $user = $this->makeUser(UserStatus::Active, Role::RECEPTIONIST, 10);
        $student = new Student(['school_id' => 10]);
        $feePayment = new FeePayment(['school_id' => 10]);

        $policy = new FeePaymentPolicy();

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->collect($user, $student));
        $this->assertTrue($policy->view($user, $feePayment));
    }

    public function test_fee_payment_policy_denies_inactive_or_cross_tenant_users(): void
    {
        $school = $this->makeSchool(10);
        app()->instance('currentSchool', $school);

        $inactiveUser = $this->makeUser(UserStatus::Inactive, Role::RECEPTIONIST, 10);
        $otherSchoolUser = $this->makeUser(UserStatus::Active, Role::RECEPTIONIST, 11);
        $student = new Student(['school_id' => 10]);

        $policy = new FeePaymentPolicy();

        $this->assertFalse($policy->viewAny($inactiveUser));
        $this->assertFalse($policy->collect($inactiveUser, $student));
        $this->assertFalse($policy->viewAny($otherSchoolUser));
        $this->assertFalse($policy->collect($otherSchoolUser, $student));
    }

    public function test_user_policy_allows_school_admin_to_manage_allowed_staff_roles(): void
    {
        $school = $this->makeSchool(20);
        app()->instance('currentSchool', $school);

        $admin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 20);
        $teacher = $this->makeUser(UserStatus::Active, Role::TEACHER, 20);

        $policy = new UserPolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $teacher));
        $this->assertTrue($policy->delete($admin, $teacher));
    }

    public function test_user_policy_denies_school_admin_management_of_other_schools_or_school_admins(): void
    {
        $school = $this->makeSchool(20);
        app()->instance('currentSchool', $school);

        $admin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 20);
        $otherSchoolTeacher = $this->makeUser(UserStatus::Active, Role::TEACHER, 21);
        $sameSchoolAdmin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 20);

        $policy = new UserPolicy();

        $this->assertFalse($policy->update($admin, $otherSchoolTeacher));
        $this->assertFalse($policy->delete($admin, $otherSchoolTeacher));
        $this->assertFalse($policy->update($admin, $sameSchoolAdmin));
        $this->assertFalse($policy->delete($admin, $sameSchoolAdmin));
    }

    public function test_student_policy_allows_active_school_admin_and_receptionist_in_same_school(): void
    {
        $school = $this->makeSchool(30);
        app()->instance('currentSchool', $school);

        $admin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 30);
        $receptionist = $this->makeUser(UserStatus::Active, Role::RECEPTIONIST, 30);
        $student = new Student(['school_id' => 30]);

        $policy = new StudentPolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->view($receptionist, $student));
        $this->assertTrue($policy->update($receptionist, $student));
        $this->assertTrue($policy->delete($receptionist, $student));
    }

    public function test_student_registration_policy_denies_teacher_even_in_same_school(): void
    {
        $school = $this->makeSchool(40);
        app()->instance('currentSchool', $school);

        $teacher = $this->makeUser(UserStatus::Active, Role::TEACHER, 40);
        $registration = new StudentRegistration(['school_id' => 40]);

        $policy = new StudentRegistrationPolicy();

        $this->assertFalse($policy->viewAny($teacher));
        $this->assertFalse($policy->create($teacher));
        $this->assertFalse($policy->view($teacher, $registration));
        $this->assertFalse($policy->update($teacher, $registration));
        $this->assertFalse($policy->delete($teacher, $registration));
    }

    public function test_school_settings_policy_allows_only_active_same_school_admin(): void
    {
        $school = $this->makeSchool(50);
        app()->instance('currentSchool', $school);

        $admin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 50);
        $receptionist = $this->makeUser(UserStatus::Active, Role::RECEPTIONIST, 50);
        $otherSchoolAdmin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 51);

        $policy = new SchoolPolicy();

        $this->assertTrue($policy->viewSettings($admin, $school));
        $this->assertTrue($policy->updateSettings($admin, $school));
        $this->assertFalse($policy->viewSettings($receptionist, $school));
        $this->assertFalse($policy->updateSettings($otherSchoolAdmin, $school));
    }

    public function test_fee_configuration_policies_allow_only_same_school_admin(): void
    {
        $school = $this->makeSchool(60);
        app()->instance('currentSchool', $school);

        $admin = $this->makeUser(UserStatus::Active, Role::SCHOOL_ADMIN, 60);
        $teacher = $this->makeUser(UserStatus::Active, Role::TEACHER, 60);
        $feeType = new FeeType(['school_id' => 60]);
        $paymentMethod = new PaymentMethod(['school_id' => 60]);

        $feeTypePolicy = new FeeTypePolicy();
        $paymentMethodPolicy = new PaymentMethodPolicy();

        $this->assertTrue($feeTypePolicy->viewAny($admin));
        $this->assertTrue($feeTypePolicy->create($admin));
        $this->assertTrue($feeTypePolicy->update($admin, $feeType));
        $this->assertTrue($paymentMethodPolicy->viewAny($admin));
        $this->assertTrue($paymentMethodPolicy->delete($admin, $paymentMethod));
        $this->assertFalse($feeTypePolicy->viewAny($teacher));
        $this->assertFalse($paymentMethodPolicy->create($teacher));
    }

    private function makeUser(UserStatus $status, string $roleSlug, ?int $schoolId): User
    {
        $user = new User([
            'school_id' => $schoolId,
            'status' => $status->value,
        ]);

        $user->setRelation('role', new Role(['slug' => $roleSlug]));

        return $user;
    }

    private function makeSchool(int $id): School
    {
        $school = new School();
        $school->id = $id;

        return $school;
    }
}
