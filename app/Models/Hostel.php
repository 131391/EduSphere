<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hostel extends Model
{
    use HasFactory, SoftDeletes;

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
}

