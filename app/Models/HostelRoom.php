<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\YesNo;

class HostelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'hostel_id',
        'hostel_floor_id',
        'room_name',
        'ac',
        'cooler',
        'fan',
        'room_create_date',
    ];

    protected $casts = [
        'ac' => YesNo::class,
        'cooler' => YesNo::class,
        'fan' => YesNo::class,
        'room_create_date' => 'date',
    ];

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
}

