<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\School;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class SchoolCountryConfigTest extends TestCase
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

    public function test_create_school_saves_country_id_and_accessor_works()
    {
        Storage::fake('public');
        
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $superAdminRole->id]);

        $countryId = 1; // India
        $countryName = config('countries')[$countryId];

        $response = $this->actingAs($admin)->post(route('admin.schools.store'), [
            'name' => 'Config Country School',
            'code' => 'CCS001',
            'subdomain' => 'ccschool',
            'email' => 'cc@school.com',
            'phone' => '1234567890',
            'status' => 'active',
            'country_id' => $countryId,
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@ccschool.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('admin.schools.index'));
        
        $school = School::where('code', 'CCS001')->first();
        $this->assertNotNull($school);
        $this->assertEquals($countryId, $school->country_id);
        $this->assertEquals($countryName, $school->country_name);
    }

    public function test_update_school_updates_country_id()
    {
        Storage::fake('public');
        
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $superAdminRole->id]);

        $school = School::create([
            'name' => 'Update Config School',
            'code' => 'UCS001',
            'subdomain' => 'ucschool',
            'email' => 'uc@school.com',
            'phone' => '1234567890',
            'status' => \App\Enums\SchoolStatus::Active,
            'country_id' => 1, // India
        ]);

        $newCountryId = 2; // United States
        $newCountryName = config('countries')[$newCountryId];

        $response = $this->actingAs($admin)->put(route('admin.schools.update', $school->id), [
            'name' => 'Update Config School',
            'code' => 'UCS001',
            'subdomain' => 'ucschool',
            'email' => 'uc@school.com',
            'phone' => '1234567890',
            'status' => 'active',
            'country_id' => $newCountryId,
        ]);

        $response->assertRedirect(route('admin.schools.index'));
        
        $school->refresh();
        $this->assertEquals($newCountryId, $school->country_id);
        $this->assertEquals($newCountryName, $school->country_name);
    }
}
