<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'visitor_no',
        'name',
        'mobile',
        'email',
        'address',
        'visitor_type',
        'visit_purpose',
        'meeting_purpose',
        'meeting_with',
        'priority',
        'no_of_guests',
        'meeting_type',
        'source',
        'check_in',
        'check_out',
        'meeting_scheduled',
        'status',
        'visitor_photo',
        'id_proof',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'meeting_scheduled' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Generate visitor number
    public static function generateVisitorNo($schoolId)
    {
        $lastVisitor = self::where('school_id', $schoolId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastVisitor) {
            return 'VIS-' . str_pad(1, 6, '0', STR_PAD_LEFT);
        }

        $lastNumber = (int) substr($lastVisitor->visitor_no, 4);
        return 'VIS-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
