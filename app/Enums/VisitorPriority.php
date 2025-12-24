<?php

namespace App\Enums;

enum VisitorPriority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
    case Urgent = 4;

    public function label(): string
    {
        return match($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low => 'gray',
            self::Medium => 'blue',
            self::High => 'orange',
            self::Urgent => 'red',
        };
    }
}
