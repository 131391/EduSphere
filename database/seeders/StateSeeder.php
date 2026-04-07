<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\Country;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('iso2', 'IN')->first();

        if (!$country) return;

        $states = [
            'Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh',
            'Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand',
            'Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur',
            'Meghalaya','Mizoram','Nagaland','Odisha','Punjab',
            'Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura',
            'Uttar Pradesh','Uttarakhand','West Bengal',

            // UT
            'Andaman and Nicobar Islands','Chandigarh',
            'Dadra and Nagar Haveli and Daman and Diu','Delhi',
            'Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'
        ];

        foreach ($states as $stateName) {
            State::firstOrCreate(
                [
                    'name' => $stateName,
                    'country_id' => $country->id
                ],
                [
                    'country_code' => 'IN'
                ]
            );
        }
    }
}