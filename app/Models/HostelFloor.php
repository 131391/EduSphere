<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Hostel Floor Model
 * 
 * @property int $id
 * @property int $school_id
 * @property int $hostel_id
 * @property string $floor_name
 * @property int|null $total_room
 * @property \Carbon\Carbon|null $floor_create_date
 */
class HostelFloor extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'hostel_id',
        'floor_name',
        'total_room',
        'floor_create_date',
    ];

    protected $casts = [
        'total_room' => 'integer',
        'floor_create_date' => 'date',
    ];

    /**
     * Scope to filter by hostel
     */
    public function scopeForHostel(Builder $query, int $hostelId): Builder
    {
        return $query->where('hostel_id', $hostelId);
    }

    /**
     * Scope to search by floor name
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('floor_name', 'like', "%{$search}%");
    }

    /**
     * Get the school that owns the floor.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the hostel that owns the floor.
     */
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    /**
     * Get the rooms for the floor.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class, 'hostel_floor_id');
    }

    /**
     * Get active bed assignments for this floor
     */
    public function bedAssignments(): HasMany
    {
        return $this->hasMany(HostelBedAssignment::class, 'hostel_floor_id')->active();
    }

    /**
     * Get current occupancy count
     */
    public function getOccupancyCountAttribute(): int
    {
        return $this->bedAssignments()->count();
    }
}
