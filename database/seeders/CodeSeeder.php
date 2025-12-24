<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\AdmissionCode;
use App\Models\RegistrationCode;
use App\Models\School;

class CodeSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) return;

        AdmissionCode::updateOrCreate(
            ['school_id' => $school->id, 'code' => 'RSIV202526/01'],
            ['is_active' => true]
        );

        RegistrationCode::updateOrCreate(
            ['school_id' => $school->id, 'code' => 'REG202526/01'],
            ['is_active' => true]
        );
    }
}
