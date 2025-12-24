<?php

namespace App\Enums;

enum SchoolStatus: int
{
    case Active = 1;
    case Inactive = 2;
    case Suspended = 3;

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Suspended => 'red',
        };
    }
}
