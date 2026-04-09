<?php

namespace App\Enums;

enum GeneralStatus: int
{
    case Inactive = 0;
    case Active = 1;
    case Pending = 2;
    case Completed = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Pending => 'yellow',
            self::Completed => 'blue',
            self::Cancelled => 'red',
        };
    }
}
