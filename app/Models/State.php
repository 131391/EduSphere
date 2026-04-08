<?php

namespace App\Models;

use Nnjeim\World\Models\State as WorldState;

class State extends WorldState
{
    protected $fillable = [
        'uuid',
        'country_id',
        'name',
        'status',
        'country_code',
    ];
}
