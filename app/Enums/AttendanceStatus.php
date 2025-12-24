<?php

namespace App\Enums;

enum AttendanceStatus: int
{
    case Present = 1;
    case Absent = 2;
    case Late = 3;
    case Excused = 4;
    case HalfDay = 5;

    public function label(): string
    {
        return match($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::Excused => 'Excused',
            self::HalfDay => 'Half Day',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Present => 'green',
            self::Absent => 'red',
            self::Late => 'yellow',
            self::Excused => 'blue',
            self::HalfDay => 'orange',
        };
    }
}
