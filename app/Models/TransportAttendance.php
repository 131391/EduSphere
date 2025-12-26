<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TransportAttendanceType;

class TransportAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'vehicle_id',
        'route_id',
        'academic_year_id',
        'attendance_date',
        'attendance_type',
        'is_present',
        'time',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'attendance_type' => TransportAttendanceType::class,
        'is_present' => 'boolean',
        'time' => 'datetime',
    ];

    /**
     * Get the school that owns the attendance record.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student for the attendance record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the vehicle for the attendance record.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the route for the attendance record.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    /**
     * Get the academic year for the attendance record.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the user who marked the attendance.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
