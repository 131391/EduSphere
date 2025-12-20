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
        $school = $this->createSchool();
        $user = $this->createUser([
            'school_id' => $school->id,
            'role' => 'school_admin',
        ]);

        $this->setCurrentSchool($school);
        $this->actingAsUser($user);

        $response = $this->get('/school/dashboard');

        $response->assertStatus(200);
    }

    public function test_school_admin_cannot_access_other_school(): void
    {
        $school1 = $this->createSchool();
        $school2 = $this->createSchool();
        
        $user = $this->createUser([
            'school_id' => $school1->id,
            'role' => 'school_admin',
        ]);

        $this->setCurrentSchool($school2);
        $this->actingAsUser($user);

        $response = $this->get('/school/dashboard');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_any_school(): void
    {
        $school = $this->createSchool();
        $user = $this->createUser([
            'school_id' => null,
            'role' => 'super_admin',
        ]);

        $this->setCurrentSchool($school);
        $this->actingAsUser($user);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
    }
}

