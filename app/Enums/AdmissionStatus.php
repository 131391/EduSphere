<?php

namespace App\Enums;

enum AdmissionStatus: int
{
    case Pending = 1;
    case Admitted = 2;
    case Cancelled = 3;

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Admitted => 'Admitted',
            self::Cancelled => 'Cancelled',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::Pending => 'orange',
            self::Admitted => 'teal',
            self::Cancelled => 'red',
        };
    }
}
