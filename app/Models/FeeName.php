<?php

namespace App\Models;

use App\Traits\Tenantable;

use App\Traits\{Searchable, Sortable, Cacheable};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\YesNo;

class FeeName extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable, Sortable, Cacheable;

    protected $fillable = [
        'school_id',
        'fee_type_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => YesNo::class,
    ];

    protected $searchable = ['name'];
    protected $sortable = ['id', 'name', 'created_at'];
    protected $cacheTTL = 3600;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', \App\Enums\YesNo::Yes);
    }
}
