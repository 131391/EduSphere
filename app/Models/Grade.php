<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Grade extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'school_id',
        'range_start',
        'range_end',
        'grade',
    ];

    protected static function booted(): void
    {
        $invalidate = function (Grade $grade) {
            Cache::forget('examination:grades:' . $grade->school_id);
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
