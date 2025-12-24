<?php

namespace App\Enums;

enum TeacherStatus: int
{
    case Active = 1;
    case Inactive = 2;
    case OnLeave = 3;

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::OnLeave => 'On Leave',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::OnLeave => 'yellow',
        };
    }
}
