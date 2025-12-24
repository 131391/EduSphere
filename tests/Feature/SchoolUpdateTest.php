<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\School;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class SchoolUpdateTest extends TestCase
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

    public function test_update_school_saves_country()
    {
        Storage::fake('public');
        
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $superAdminRole->id]);

        // Create a school with null country
        $school = School::create([
            'name' => 'Update Test School',
            'code' => 'UTS001',
            'subdomain' => 'utschool',
            'email' => 'ut@school.com',
            'phone' => '1234567890',
            'status' => \App\Enums\SchoolStatus::Active,
            'country' => null,
        ]);

        // Update the school with a country
        $response = $this->actingAs($admin)->put(route('admin.schools.update', $school->id), [
            'name' => 'Update Test School',
            'code' => 'UTS001',
            'subdomain' => 'utschool',
            'email' => 'ut@school.com',
            'phone' => '1234567890',
            'status' => 'active',
            'country' => 'India',
            'admin_name' => 'Admin User', // These might not be needed for update but good to have if validation requires
        ]);

        $response->assertRedirect(route('admin.schools.index'));
        
        $school->refresh();
        $this->assertEquals('India', $school->country);
    }
}
