<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchoolAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_access_their_school(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $school = $this->createSchool();
        $user = $this->createUser([
            'school_id' => $school->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first()->id,
        ]);

        $this->setCurrentSchool($school);
        $this->actingAsUser($user);

        $response = $this->get('http://' . $school->subdomain . '.localhost/school/dashboard');

        $response->assertStatus(200);
    }

    public function test_school_admin_cannot_access_other_school(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $school1 = $this->createSchool();
        $school2 = $this->createSchool();
        
        $user = $this->createUser([
            'school_id' => $school1->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first()->id,
        ]);

        $this->setCurrentSchool($school2);
        $this->actingAsUser($user);

        $response = $this->get('http://' . $school2->subdomain . '.localhost/school/dashboard');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_any_school(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $school = $this->createSchool();
        $user = $this->createUser([
            'school_id' => null,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::SUPER_ADMIN)->first()->id,
        ]);

        $this->setCurrentSchool($school);
        $this->actingAsUser($user);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
    }
}

