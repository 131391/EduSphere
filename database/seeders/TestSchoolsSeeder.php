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

        // Create default Fee Type and Names for DPS
        $dpsFeeType = \App\Models\FeeType::updateOrCreate(
            ['school_id' => $dps->id, 'name' => 'Compulsory Fees'],
            ['is_active' => \App\Enums\YesNo::Yes]
        );

        foreach (['Registration Fee', 'Admission Fee', 'Tuition Fee'] as $name) {
            \App\Models\FeeName::updateOrCreate(
                ['school_id' => $dps->id, 'name' => $name],
                ['fee_type_id' => $dpsFeeType->id, 'is_active' => \App\Enums\YesNo::Yes]
            );
        }


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

        // Create default Fee Type and Names for DAV
        $davFeeType = \App\Models\FeeType::updateOrCreate(
            ['school_id' => $dav->id, 'name' => 'Compulsory Fees'],
            ['is_active' => \App\Enums\YesNo::Yes]
        );

        foreach (['Registration Fee', 'Admission Fee', 'Tuition Fee'] as $name) {
            \App\Models\FeeName::updateOrCreate(
                ['school_id' => $dav->id, 'name' => $name],
                ['fee_type_id' => $davFeeType->id, 'is_active' => \App\Enums\YesNo::Yes]
            );
        }


        $this->command->info('Test schools (DPS and DAV) created successfully!');
    }
}
