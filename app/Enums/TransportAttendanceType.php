<?php

namespace App\Enums;

enum TransportAttendanceType: int
{
    case PickupFromBusStop = 1;
    case DropAtSchoolCampus = 2;
    case PickupFromSchoolCampus = 3;
    case DropAtBusStop = 4;

    public function label(): string
    {
        return match($this) {
            self::PickupFromBusStop => 'Pickup (Bus Stop)',
            self::DropAtSchoolCampus => 'Drop (School Campus)',
            self::PickupFromSchoolCampus => 'Pickup (School Campus)',
            self::DropAtBusStop => 'Drop (Bus Stop)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'label', 'value');
    }
}

