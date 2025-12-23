<?php

namespace App\Constants;

class Gender
{
    const MALE = 1;
    const FEMALE = 2;
    const OTHER = 3;

    /**
     * Get all gender labels
     *
     * @return array
     */
    public static function getLabels(): array
    {
        return [
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        ];
    }

    /**
     * Get label for a specific gender value
     *
     * @param int|null $value
     * @return string
     */
    public static function getLabel(?int $value): string
    {
        return self::getLabels()[$value] ?? 'Unknown';
    }

    /**
     * Get all gender options for select dropdowns
     *
     * @return array
     */
    public static function getOptions(): array
    {
        return self::getLabels();
    }

    /**
     * Get gender value from string label
     *
     * @param string $label
     * @return int|null
     */
    public static function getValueFromLabel(string $label): ?int
    {
        $labels = array_flip(self::getLabels());
        return $labels[$label] ?? null;
    }
}
