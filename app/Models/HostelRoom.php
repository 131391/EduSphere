<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\YesNo;

/**
 * Hostel Room Model
 * 
 * @property int $id
 * @property int $school_id
 * @property int $hostel_id
 * @property int $hostel_floor_id
 * @property string $room_name
 * @property YesNo $ac
 * @property YesNo $cooler
 * @property YesNo $fan
 * @property int $no_of_beds
 * @property \Carbon\Carbon|null $room_create_date
 * @property-read int $available_beds
 */
class HostelRoom extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'hostel_id',
        'hostel_floor_id',
        'room_name',
        'no_of_beds',
        'ac',
        'cooler',
        'fan',
        'room_create_date',
    ];

    protected $casts = [
        'ac' => YesNo::class,
        'cooler' => YesNo::class,
        'fan' => YesNo::class,
        'no_of_beds' => 'integer',
        'room_create_date' => 'date',
    ];

    /**
     * Scope to filter by floor
     */
    public function scopeForFloor(Builder $query, int $floorId): Builder
    {
        return $query->where('hostel_floor_id', $floorId);
    }

    /**
     * Scope to filter by hostel
     */
    public function scopeForHostel(Builder $query, int $hostelId): Builder
    {
        return $query->where('hostel_id', $hostelId);
    }

    /**
     * Scope to search by room name
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('room_name', 'like', "%{$search}%");
    }

    /**
     * Scope to filter rooms with AC
     */
    public function scopeWithAc(Builder $query): Builder
    {
        return $query->where('ac', YesNo::Yes);
    }

    /**
     * Get the school that owns the room.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the hostel that owns the room.
     */
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    /**
     * Get the floor that owns the room.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(HostelFloor::class, 'hostel_floor_id');
    }

    /**
     * Get the bed assignments for the room.
     */
    public function bedAssignments(): HasMany
    {
        return $this->hasMany(HostelBedAssignment::class, 'hostel_room_id');
    }

    /**
     * Get active bed assignments for this room
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(HostelBedAssignment::class, 'hostel_room_id')->active();
    }

    /**
     * Get current occupancy count
     */
    public function getOccupancyCountAttribute(): int
    {
        return $this->assignments()->count();
    }

    /**
     * Get available beds count
     */
    public function getAvailableBedsAttribute(): int
    {
        return max(0, ($this->no_of_beds ?? 0) - $this->occupancy_count);
    }

    /**
     * Check if room has available beds
     */
    public function hasAvailableBeds(): bool
    {
        return $this->available_beds > 0;
    }
}

