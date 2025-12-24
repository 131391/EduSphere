<?php

namespace App\Enums;

enum RelationType: int
{
    case Father = 1;
    case Mother = 2;
    case Guardian = 3;
    case Other = 4;

    public function label(): string
    {
        return match($this) {
            self::Father => 'Father',
            self::Mother => 'Mother',
            self::Guardian => 'Guardian',
            self::Other => 'Other',
        };
    }
}
