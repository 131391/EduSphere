<?php

namespace App\Enums;

enum FuelType: int
{
    case Diesel = 1;
    case Petrol = 2;
    case CNG = 3;
    case Electric = 4;

    public function label(): string
    {
        return match($this) {
            self::Diesel => 'Diesel',
            self::Petrol => 'Petrol',
            self::CNG => 'CNG',
            self::Electric => 'Electric',
        };
    }
}

