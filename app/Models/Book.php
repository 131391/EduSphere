<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, Tenantable, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title',
        'author',
        'isbn',
        'category_id',
        'quantity',
        'available_quantity',
        'price',
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;
        $query = $this->where($this->getRouteKeyName(), $value);
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        return $query->firstOrFail();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }
}
