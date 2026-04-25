<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_middleware_identifies_school_by_subdomain(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $school = School::factory()->create([
            'subdomain' => 'testschool',
            'status' => \App\Enums\SchoolStatus::Active,
        ]);

        $adminRole = \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first();
        $user = \App\Models\User::factory()->create([
            'school_id' => $school->id,
            'role_id' => $adminRole->id,
        ]);
        $response = $this->actingAs($user)->get('http://testschool.localhost/school/dashboard');

        $response->assertStatus(200);
    }

    public function test_tenant_middleware_returns_404_for_invalid_subdomain(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->get('http://invalidschool.localhost/school/dashboard');

        $response->assertStatus(404);
    }

    public function test_tenant_middleware_blocks_inactive_school(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $school = School::factory()->create([
            'subdomain' => 'inactive',
            'status' => \App\Enums\SchoolStatus::Inactive,
        ]);

        $adminRole = \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first();
        $user = \App\Models\User::factory()->create([
            'school_id' => $school->id,
            'role_id' => $adminRole->id,
        ]);
        $response = $this->actingAs($user)->get('http://inactive.localhost/school/dashboard');

        $response->assertStatus(403);
    }
}

