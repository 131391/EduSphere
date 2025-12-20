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
        $school = School::factory()->create([
            'subdomain' => 'testschool',
            'status' => 'active',
        ]);

        $response = $this->get('http://testschool.localhost/dashboard');

        $response->assertStatus(200);
    }

    public function test_tenant_middleware_returns_404_for_invalid_subdomain(): void
    {
        $response = $this->get('http://invalidschool.localhost/dashboard');

        $response->assertStatus(404);
    }

    public function test_tenant_middleware_blocks_inactive_school(): void
    {
        $school = School::factory()->create([
            'subdomain' => 'inactive',
            'status' => 'inactive',
        ]);

        $response = $this->get('http://inactive.localhost/dashboard');

        $response->assertStatus(403);
    }
}

