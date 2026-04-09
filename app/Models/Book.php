<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory, Tenantable;

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
