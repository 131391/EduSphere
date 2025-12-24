<?php

namespace App\Enums;

enum ParentStatus: int
{
    case Active = 1;
    case Inactive = 2;

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
        };
    }
}
