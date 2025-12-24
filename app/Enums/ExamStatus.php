<?php

namespace App\Enums;

enum ExamStatus: int
{
    case Scheduled = 1;
    case Ongoing = 2;
    case Completed = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Scheduled',
            self::Ongoing => 'Ongoing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Scheduled => 'blue',
            self::Ongoing => 'yellow',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }
}
