<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);
        
        // Seed geographic data
        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);

        // Create default school for development
        $this->createDefaultSchool();

        // Create super admin only
        $this->createSuperAdmin();
    }

    protected function createDefaultSchool(): void
    {
        $school = \App\Models\School::firstOrCreate(
            ['subdomain' => 'admin'],
            [
                'name' => 'Default School',
                'code' => 'DEFAULT001',
                'status' => \App\Enums\SchoolStatus::Active,
                'email' => 'school@example.com',
            ]
        );

        // Create Admin User for this school if it doesn't exist
        $schoolAdminRole = Role::where('slug', Role::SCHOOL_ADMIN)->first();
        if ($schoolAdminRole) {
            User::firstOrCreate(
                ['email' => 'admin@dps.school.com'],
                [
                    'name' => 'DPS Admin',
                    'password' => bcrypt('password'),
                    'school_id' => $school->id,
                    'role_id' => $schoolAdminRole->id,
                    'status' => User::STATUS_ACTIVE,
                ]
            );
        }
    }

    protected function createSuperAdmin(): void
    {
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();

        User::firstOrCreate(
            ['email' => 'admin@edusphere.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => $superAdminRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }
}
