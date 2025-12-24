<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    // Fuel Type Constants
    const FUEL_TYPE_DIESEL = 1;
    const FUEL_TYPE_PETROL = 2;
    const FUEL_TYPE_CNG = 3;
    const FUEL_TYPE_ELECTRIC = 4;

    protected $fillable = [
        'school_id',
        'registration_no',
        'vehicle_no',
        'fuel_type',
        'capacity',
        'initial_reading',
        'engine_no',
        'chassis_no',
        'vehicle_type',
        'model_no',
        'date_of_purchase',
        'vehicle_group',
        'imei_gps_device',
        'tracking_url',
        'manufacturing_year',
        'vehicle_create_date',
    ];

    protected $casts = [
        'date_of_purchase' => 'date',
        'vehicle_create_date' => 'date',
        'capacity' => 'integer',
        'initial_reading' => 'integer',
        'manufacturing_year' => 'integer',
    ];

    /**
     * Get the school that owns the vehicle.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the routes for the vehicle.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(TransportRoute::class);
    }

    /**
     * Get fuel type label.
     */
    public function getFuelTypeLabel(): string
    {
        return self::getFuelTypeLabels()[$this->fuel_type] ?? 'Unknown';
    }

    /**
     * Get all fuel type labels.
     */
    public static function getFuelTypeLabels(): array
    {
        return [
            self::FUEL_TYPE_DIESEL => 'Diesel',
            self::FUEL_TYPE_PETROL => 'Petrol',
            self::FUEL_TYPE_CNG => 'CNG',
            self::FUEL_TYPE_ELECTRIC => 'Electric',
        ];
    }

    /**
     * Generate a unique vehicle number for the school.
     */
    public static function generateVehicleNo(int $schoolId): string
    {
        $lastVehicle = self::where('school_id', $schoolId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastVehicle || !$lastVehicle->vehicle_no) {
            return 'VEH-001';
        }

        $lastNumber = (int) substr($lastVehicle->vehicle_no, 4);
        $newNumber = $lastNumber + 1;

        return 'VEH-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
