<?php

namespace App\Models;

use App\Traits\{Searchable, Sortable, Cacheable};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LateFee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable, Sortable, Cacheable;

    protected $fillable = [
        'school_id',
        'fine_date',
        'late_fee_amount',
    ];

    protected $casts = [
        'late_fee_amount' => 'decimal:2',
    ];

    protected $searchable = ['fine_date', 'late_fee_amount'];
    protected $sortable = ['id', 'fine_date', 'late_fee_amount', 'created_at'];
    protected $cacheTTL = 3600;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['fine_date', 'late_fee_amount'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
