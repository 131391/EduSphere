<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamType extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'name',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
