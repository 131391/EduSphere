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
 * Hostel Model
 * 
 * @property int $id
 * @property int $school_id
 * @property string $hostel_name
 * @property string|null $hostel_incharge
 * @property int|null $capability
 * @property \Carbon\Carbon|null $hostel_create_date
 */
class Hostel extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'hostel_name',
        'hostel_incharge',
        'capability',
        'hostel_create_date',
    ];

    protected $casts = [
        'capability' => 'integer',
        'hostel_create_date' => 'date',
    ];

    /**
     * Scope to filter hostels with available capacity
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereRaw('capability > (SELECT COUNT(*) FROM hostel_bed_assignments WHERE hostel_bed_assignments.hostel_id = hostels.id AND hostel_bed_assignments.status = 1 AND hostel_bed_assignments.deleted_at IS NULL)');
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('hostel_name', 'like', "%{$search}%")
              ->orWhere('hostel_incharge', 'like', "%{$search}%");
        });
    }

    /**
     * Get the school that owns the hostel.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the floors for the hostel.
     */
    public function floors(): HasMany
    {
        return $this->hasMany(HostelFloor::class);
    }

    /**
     * Get the rooms for the hostel.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class);
    }

    /**
     * Get active bed assignments for this hostel
     */
    public function bedAssignments(): HasMany
    {
        return $this->hasMany(HostelBedAssignment::class)->active();
    }

    /**
     * Get current occupancy count
     */
    public function getOccupancyCountAttribute(): int
    {
        return $this->bedAssignments()->count();
    }

    /**
     * Get available beds count
     */
    public function getAvailableBedsAttribute(): int
    {
        return max(0, ($this->capability ?? 0) - $this->occupancy_count);
    }

    /**
     * Check if hostel has available capacity
     */
    public function hasCapacity(): bool
    {
        return $this->available_beds > 0;
    }

    /**
     * Get occupancy percentage
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if (!$this->capability || $this->capability === 0) {
            return 0;
        }
        return round(($this->occupancy_count / $this->capability) * 100, 2);
    }
}

