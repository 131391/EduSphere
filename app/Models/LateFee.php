<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LateFee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'fine_date',
        'late_fee_amount',
    ];

    protected $casts = [
        'late_fee_amount' => 'decimal:2',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

