<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\School;
use App\Models\Religion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class SchoolMasterDataTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Role::where('slug', Role::SCHOOL_ADMIN)->exists()) {
            Role::create(['name' => 'School Admin', 'slug' => Role::SCHOOL_ADMIN, 'guard_name' => 'web']);
        }
        if (!Role::where('slug', Role::SUPER_ADMIN)->exists()) {
            Role::create(['name' => 'Super Admin', 'slug' => Role::SUPER_ADMIN, 'guard_name' => 'web']);
        }
    }

    public function test_store_seeds_master_data_for_new_school()
    {
        Storage::fake('public');
        
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $superAdminRole->id]);

        $response = $this->actingAs($admin)->post(route('admin.schools.store'), [
            'name' => 'Seeding Test School',
            'code' => 'STS001',
            'subdomain' => 'stschool',
            'email' => 'st@school.com',
            'phone' => '1234567890',
            'status' => 'active',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@stschool.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('admin.schools.index'));
        
        $school = School::where('code', 'STS001')->first();
        $this->assertNotNull($school);

        // Verify Religion data seeded for this school
        $this->assertDatabaseHas('religions', [
            'name' => 'Hindu',
            'school_id' => $school->id,
        ]);

        // Verify count of seeded items
        $this->assertEquals(8, Religion::where('school_id', $school->id)->count());
    }
}
