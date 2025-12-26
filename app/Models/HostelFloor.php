<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelFloor extends Model
{
    use HasFactory, SoftDeletes;

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
}
