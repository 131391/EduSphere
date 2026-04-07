<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\State;
use App\Models\Country;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('iso2', 'IN')->first();
        $state = State::where('name', 'Bihar')->first();

        if (!$country || !$state) return;

        $cities = [
            'Araria','Arwal','Aurangabad','Banka','Begusarai','Bhagalpur',
            'Bhojpur','Buxar','Darbhanga','East Champaran','Gaya',
            'Gopalganj','Jamui','Jehanabad','Kaimur','Katihar',
            'Khagaria','Kishanganj','Lakhisarai','Madhepura','Madhubani',
            'Munger','Muzaffarpur','Nalanda','Nawada','Patna',
            'Purnia','Rohtas','Saharsa','Samastipur','Saran',
            'Sheikhpura','Sheohar','Sitamarhi','Siwan','Supaul',
            'Vaishali','West Champaran'
        ];

        foreach ($cities as $cityName) {
            City::firstOrCreate(
                [
                    'name' => $cityName,
                    'state_id' => $state->id
                ],
                [
                    'country_id' => $country->id,
                    'country_code' => 'IN'
                ]
            );
        }
    }
}