<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\GeneralStatus;

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
    ];

    protected $casts = [
        'rent' => 'decimal:2',
        'hostel_assign_date' => 'date',
        'status' => GeneralStatus::class,
    ];

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
}

