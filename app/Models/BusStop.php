<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'route_id',
        'vehicle_id',
        'bus_stop_no',
        'bus_stop_name',
        'latitude',
        'longitude',
        'distance_from_institute',
        'charge_per_month',
        'area_pin_code',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance_from_institute' => 'decimal:2',
        'charge_per_month' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
