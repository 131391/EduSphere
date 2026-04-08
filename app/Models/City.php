<?php

namespace App\Models;

use Nnjeim\World\Models\City as WorldCity;

class City extends WorldCity
{
    protected $fillable = [
        'uuid',
        'country_id',
        'state_id',
        'name',
        'status',
        'country_code',
    ];
}
