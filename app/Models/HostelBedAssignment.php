<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\GeneralStatus;

/**
 * @property int $id
 * @property int $school_id
 * @property int $student_id
 * @property int $hostel_id
 * @property int $hostel_floor_id
 * @property int $hostel_room_id
 * @property \Carbon\Carbon|null $end_date
 * @property \App\Enums\GeneralStatus $status
 */
class HostelBedAssignment extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'student_id',
        'hostel_id',
        'hostel_floor_id',
        'hostel_room_id',
        'bed_no',
        'rent',
        'hostel_assign_date',
        'starting_month',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'rent' => 'decimal:2',
        'hostel_assign_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => GeneralStatus::class,
    ];

    /**
     * Scope to filter only active assignments
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', GeneralStatus::Active);
    }

    /**
     * Scope to filter assignments by hostel
     */
    public function scopeForHostel(Builder $query, int $hostelId): Builder
    {
        return $query->where('hostel_id', $hostelId);
    }

    /**
     * Scope to filter assignments by room
     */
    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where('hostel_room_id', $roomId);
    }

    /**
     * Get the school that owns the assignment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student assigned to the bed.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the hostel.
     */
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    /**
     * Get the floor.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(HostelFloor::class, 'hostel_floor_id');
    }

    /**
     * Get the room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    /**
     * Check if assignment is currently active
     */
    public function isActive(): bool
    {
        return $this->status === GeneralStatus::Active;
    }

    /**
     * Check if assignment has ended
     */
    public function hasEnded(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Get the occupancy status of the room
     */
    public function getOccupancyAttribute(): int
    {
        return self::where('hostel_room_id', $this->hostel_room_id)
            ->active()
            ->count();
    }
}

