<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCopy extends Model
{
    use HasFactory, Tenantable, SoftDeletes;

    // school_id intentionally omitted — set automatically by Tenantable.
    protected $fillable = [
        'book_id',
        'accession_number',
        'status',
        'condition',
        'shelf_location',
        'acquired_on',
        'notes',
    ];

    protected $casts = [
        'acquired_on' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
