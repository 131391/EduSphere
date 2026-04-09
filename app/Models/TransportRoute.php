<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\RouteStatus;

class TransportRoute extends Model
{
    use HasFactory, Tenantable;

    // Status Constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'school_id',
        'route_name',
        'vehicle_id',
        'route_create_date',
        'status',
    ];

    protected $casts = [
        'route_create_date' => 'date',
        'status' => RouteStatus::class,
    ];

    /**
     * Get the school that owns the route.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the vehicle assigned to the route.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope a query to only include active routes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', RouteStatus::Active);
    }

    /**
     * Scope a query to only include inactive routes.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', RouteStatus::Inactive);
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return $this->status instanceof RouteStatus ? $this->status->label() : 'Unknown';
    }

    /**
     * Get all status labels.
     */
    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
        ];
    }
}
