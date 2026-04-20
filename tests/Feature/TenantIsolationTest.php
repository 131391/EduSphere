<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\Fee;
use App\Enums\SchoolStatus;
use App\Enums\StudentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_from_school_a_not_visible_in_school_b(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $schoolA = School::factory()->create(['subdomain' => 'school-a']);
        $schoolB = School::factory()->create(['subdomain' => 'school-b']);

        $studentA = Student::factory()->create([
            'school_id' => $schoolA->id,
            'admission_no' => 'STU001',
        ]);

        $this->app->instance('currentSchool', $schoolB);

        $found = Student::where('admission_no', 'STU001')->first();
        
        $this->assertNull($found);
    }

    public function test_fee_from_school_a_not_visible_in_school_b(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $schoolA = School::factory()->create(['subdomain' => 'school-a']);
        $schoolB = School::factory()->create(['subdomain' => 'school-b']);

        $feeA = Fee::factory()->create([
            'school_id' => $schoolA->id,
            'bill_no' => 'BILL001',
        ]);

        $this->app->instance('currentSchool', $schoolB);

        $found = Fee::where('bill_no', 'BILL001')->first();
        
        $this->assertNull($found);
    }

    public function test_user_cannot_access_other_school_data(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $schoolA = School::factory()->create(['subdomain' => 'school-a']);
        $schoolB = School::factory()->create(['subdomain' => 'school-b']);

        $adminRole = \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first();
        
        $userA = User::factory()->create([
            'school_id' => $schoolA->id,
            'role_id' => $adminRole->id,
        ]);

        Student::factory()->create([
            'school_id' => $schoolB->id,
            'admission_no' => 'STU002',
        ]);

        $this->app->instance('currentSchool', $schoolB);
        $this->actingAs($userA);

        $student = Student::where('admission_no', 'STU002')->first();
        
        $this->assertNull($student);
    }
}