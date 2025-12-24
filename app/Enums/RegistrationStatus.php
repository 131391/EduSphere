<?php

namespace App\Enums;

enum RegistrationStatus: int
{
    case Pending = 1;
    case Admitted = 2;
    case Rejected = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Admitted => 'Admitted',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Admitted => 'green',
            self::Rejected => 'red',
            self::Cancelled => 'gray',
        };
    }
}
