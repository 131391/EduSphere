<?php

namespace App\Enums;

enum YesNo: int
{
    case No = 0;
    case Yes = 1;

    public function label(): string
    {
        return match($this) {
            self::No => 'No',
            self::Yes => 'Yes',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::No->value => self::No->label(),
            self::Yes->value => self::Yes->label(),
        ];
    }
}

