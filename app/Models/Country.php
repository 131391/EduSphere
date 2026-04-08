<?php

namespace App\Models;

use Nnjeim\World\Models\Country as WorldCountry;

class Country extends WorldCountry
{
    protected $fillable = [
        'uuid',
        'iso2',
        'name',
        'status',
        'phone_code',
        'iso3',
        'region',
        'subregion',
    ];
}
