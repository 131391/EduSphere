<?php

namespace App\Enums;

enum VisitorStatus: int
{
    case Scheduled = 1;
    case CheckedIn = 2;
    case Completed = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Scheduled',
            self::CheckedIn => 'Checked In',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Scheduled => 'blue',
            self::CheckedIn => 'yellow',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }
}
