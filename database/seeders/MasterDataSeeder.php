<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Religion;
use App\Models\BloodGroup;
use App\Models\Qualification;
use App\Models\StudentType;
use App\Models\BoardingType;
use App\Models\Category;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(\App\Models\School $school = null): void
    {
        if (!$school) {
            return;
        }

        $schoolId = $school->id;

        // Religions
        $religions = [
            'Hindu', 'Muslim', 'Christian', 'Sikh', 'Jain', 'Buddhist', 'Parsi', 'Other'
        ];
        foreach ($religions as $name) {
            Religion::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Blood Groups
        $bloodGroups = [
            'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'
        ];
        foreach ($bloodGroups as $name) {
            BloodGroup::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Qualifications
        $qualifications = [
            'High School', 'Intermediate', 'Graduate', 'Post Graduate', 'Doctorate', 'Other'
        ];
        foreach ($qualifications as $name) {
            Qualification::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Student Types
        $studentTypes = [
            'HIGHER ACHIEVERS', 'AVERAGE PERFORMANCE', 'STRUGLING LEARNER', 'N/A'
        ];
        foreach ($studentTypes as $name) {
            StudentType::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Boarding Types
        $boardingTypes = [
            'DAILY BOARDING', 'FULL BOARDING', 'N/A'
        ];
        foreach ($boardingTypes as $name) {
            BoardingType::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Categories
        $categories = [
            'GEN', 'OBC', 'SC', 'ST'
        ];
        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }
    }
}
