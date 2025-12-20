<?php

namespace App\Models;

use App\Traits\{Searchable, Sortable, Cacheable};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FeeType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable, Sortable, Cacheable;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $searchable = ['name'];
    protected $sortable = ['id', 'name', 'created_at'];
    protected $cacheTTL = 3600;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

