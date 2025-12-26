<?php

namespace App\Enums;

enum StaffPost: int
{
    case Principal = 1;
    case Teacher = 2;
    case Assistant = 3;
    case Counselor = 4;
    case CrossingGuard = 5;
    case SchoolBusDriver = 6;
    case FoodServiceWorker = 7;

    public function label(): string
    {
        return match($this) {
            self::Principal => 'Principal',
            self::Teacher => 'Teacher',
            self::Assistant => 'Assistant',
            self::Counselor => 'Counselor',
            self::CrossingGuard => 'Crossing guard',
            self::SchoolBusDriver => 'School bus driver',
            self::FoodServiceWorker => 'Food service worker',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}

