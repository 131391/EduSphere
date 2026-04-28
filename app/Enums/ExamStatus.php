<?php

namespace App\Enums;

enum ExamStatus: int
{
    case Scheduled = 1;
    case Ongoing = 2;
    case Completed = 3;
    case Cancelled = 4;
    case Locked = 5;

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Scheduled',
            self::Ongoing => 'Ongoing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Locked => 'Locked',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Scheduled => 'blue',
            self::Ongoing => 'yellow',
            self::Completed => 'green',
            self::Cancelled => 'red',
            self::Locked => 'slate',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Cancelled || $this === self::Locked;
    }

    public function acceptsMarkEntry(): bool
    {
        return !$this->isTerminal();
    }
}
