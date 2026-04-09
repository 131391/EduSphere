<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;
use App\Models\Role;
use App\Enums\SchoolStatus;

class TestSchoolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolAdminRole = Role::where('slug', Role::SCHOOL_ADMIN)->first();

        // 1. Create Delhi Public School
        $dps = School::updateOrCreate(
            ['subdomain' => 'dps'],
            [
                'name' => 'Delhi Public School',
                'code' => 'DPS-SCH-01',
                'email' => 'contact@dps.edusphere.com',
                'status' => SchoolStatus::Active,
                'domain' => 'dps.edusphere.com',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@dps.edusphere.com'],
            [
                'name' => 'DPS Admin',
                'password' => bcrypt('password'),
                'school_id' => $dps->id,
                'role_id' => $schoolAdminRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        // 2. Create DAV Public School
        $dav = School::updateOrCreate(
            ['subdomain' => 'dav'],
            [
                'name' => 'DAV Public School',
                'code' => 'DAV-SCH-01',
                'email' => 'contact@dav.edusphere.com',
                'status' => SchoolStatus::Active,
                'domain' => 'dav.edusphere.com',
            ]
        );


        User::updateOrCreate(
            ['email' => 'admin@dav.edusphere.com'],
            [
                'name' => 'DAV Admin',
                'password' => bcrypt('password'),
                'school_id' => $dav->id,
                'role_id' => $schoolAdminRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $this->command->info('Test schools (DPS and DAV) created successfully!');
    }
}
