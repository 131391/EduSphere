<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            ['iso2' => 'IN'],
            [
                'name' => 'India',
                'status' => 1,
                'phone_code' => '91',
                'iso3' => 'IND',
                'region' => 'Asia',
                'subregion' => 'Southern Asia'
            ]
        );
    }
}