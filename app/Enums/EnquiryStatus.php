<?php

namespace App\Enums;

enum EnquiryStatus: int
{
    case Pending = 1;
    case Completed = 2;
    case Cancelled = 3;
    case Admitted = 4;

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Admitted => 'Admitted',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Completed => 'blue',
            self::Cancelled => 'red',
            self::Admitted => 'green',
        };
    }
}
