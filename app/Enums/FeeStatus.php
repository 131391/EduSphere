<?php

namespace App\Enums;

enum FeeStatus: int
{
    case Pending = 1;
    case Partial = 2;
    case Paid = 3;
    case Overdue = 4;

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Partial => 'Partial',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Partial => 'blue',
            self::Paid => 'green',
            self::Overdue => 'red',
        };
    }
}
