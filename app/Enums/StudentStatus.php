<?php

namespace App\Enums;

enum StudentStatus: int
{
    case Active = 1;
    case Graduated = 2;
    case Transferred = 3;
    case Inactive = 4;

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Graduated => 'Graduated',
            self::Transferred => 'Transferred',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Graduated => 'blue',
            self::Transferred => 'orange',
            self::Inactive => 'gray',
        };
    }
}
