<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(\App\Models\School $school = null): void
    {
        if (!$school) {
            return;
        }

        $classes = [
            ['name' => 'NURSERY', 'order' => 1],
            ['name' => 'LKG', 'order' => 2],
            ['name' => 'UKG', 'order' => 3],
            ['name' => 'KG', 'order' => 4],
            ['name' => 'I', 'order' => 5],
            ['name' => 'II', 'order' => 6],
            ['name' => 'III', 'order' => 7],
            ['name' => 'IV', 'order' => 8],
            ['name' => 'V', 'order' => 9],
            ['name' => 'VI', 'order' => 10],
            ['name' => 'VII', 'order' => 11],
            ['name' => 'VIII', 'order' => 12],
        ];

        foreach ($classes as $classData) {
            ClassModel::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => $classData['name']
                ],
                [
                    'order' => $classData['order'],
                    'is_available' => true
                ]
            );
        }
    }
}
