<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Religion;
use App\Models\BloodGroup;
use App\Models\Qualification;
use App\Models\StudentType;
use App\Models\BoardingType;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\FeeName;
use App\Models\FeeType;

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

        // Payment Methods
        $paymentMethods = [
            'CASH', 'ONLINE', 'QR.CODE'
        ];
        foreach ($paymentMethods as $name) {
            PaymentMethod::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Fee Names
        $feeNames = [
            'April', 'MAY 2', 'JUNE 3', 'JULY 4', 'AUGUST 5', 'SEPTEMBER 6', 
            'OCTOBER 7', 'NOVEMBER 8', 'DECEMBER 9', 'JANUARY 10', 
            'FEBRUARY 11', 'MARCH 12', 'EXAM FEE'
        ];
        foreach ($feeNames as $name) {
            FeeName::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Fee Types
        $feeTypes = [
            'Monthly', 'Qtrly', 'Haf-Yearly', 'Yearly'
        ];
        foreach ($feeTypes as $name) {
            FeeType::firstOrCreate(['name' => $name, 'school_id' => $schoolId]);
        }

        // Classes
        (new ClassSeeder())->run($school);
    }
}
