<?php

namespace App\Enums;

enum VisitorMode: int
{
    case Online = 1;
    case Offline = 2;
    case Office = 3;

    public function label(): string
    {
        return match($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
            self::Office => 'Office',
        };
    }
}
