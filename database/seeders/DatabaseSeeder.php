<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;
use App\Models\Role;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Section;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Create super admin
        $this->createSuperAdmin();

        // Create demo school
        $this->createDemoSchool();
    }

    protected function createSuperAdmin(): void
    {
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@edusphere.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => $superAdminRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }

    protected function createDemoSchool(): void
    {
        $school = School::firstOrCreate(
            ['code' => 'DEMO001'],
            [
                'name' => 'Demo School',
                'subdomain' => 'demo',
                'email' => 'demo@school.com',
                'phone' => '1234567890',
                'status' => 'active',
            ]
        );

        // Get roles
        $schoolAdminRole = Role::where('slug', Role::SCHOOL_ADMIN)->first();
        $receptionistRole = Role::where('slug', Role::RECEPTIONIST)->first();

        // Create school admin
        $schoolAdmin = User::firstOrCreate(
            ['email' => 'admin@demo.school.com'],
            [
                'school_id' => $school->id,
                'name' => 'School Admin',
                'password' => bcrypt('password'),
                'role_id' => $schoolAdminRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        // Create receptionist
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@demo.school.com'],
            [
                'school_id' => $school->id,
                'name' => 'Receptionist',
                'password' => bcrypt('password'),
                'role_id' => $receptionistRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        // Create academic year
        AcademicYear::firstOrCreate(
            [
                'school_id' => $school->id,
                'name' => '2025-2026',
            ],
            [
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_current' => true,
            ]
        );

        // Create classes
        $classes = ['NURSERY', 'KG', 'UKG', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
        
        foreach ($classes as $index => $className) {
            $class = ClassModel::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => $className,
                ],
                [
                    'order' => $index + 1,
                    'is_available' => true,
                ]
            );

            // Create section for each class
            Section::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'class_id' => $class->id,
                    'name' => 'A',
                ],
                [
                    'capacity' => 50,
                    'current_strength' => 0,
                ]
            );
        }
    }
}

