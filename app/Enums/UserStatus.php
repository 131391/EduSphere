<?php

namespace App\Enums;

enum UserStatus: int
{
    case Active = 1;
    case Inactive = 0;
    case Suspended = 2;
    case Pending = 3;

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
            self::Pending => 'Pending',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Suspended => 'red',
            self::Pending => 'yellow',
        };
    }
}
