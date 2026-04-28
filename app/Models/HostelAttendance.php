<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Hostel Attendance Model
 * 
 * @property int $id
 * @property int $school_id
 * @property int $student_id
 * @property int $hostel_id
 * @property int|null $academic_year_id
 * @property int|null $hostel_floor_id
 * @property int|null $hostel_room_id
 * @property \Carbon\Carbon $attendance_date
 * @property bool $is_present
 * @property \Carbon\Carbon|null $time
 * @property string|null $remarks
 * @property int|null $marked_by
 */
class HostelAttendance extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'school_id',
        'student_id',
        'hostel_id',
        'academic_year_id',
        'hostel_floor_id',
        'hostel_room_id',
        'attendance_date',
        'is_present',
        'time',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time' => 'datetime',
        'is_present' => 'boolean',
    ];

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter present students
     */
    public function scopePresent(Builder $query): Builder
    {
        return $query->where('is_present', true);
    }

    /**
     * Scope to filter absent students
     */
    public function scopeAbsent(Builder $query): Builder
    {
        return $query->where('is_present', false);
    }

    /**
     * Get the academic year for the record.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the floor for the record.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(HostelFloor::class, 'hostel_floor_id');
    }

    /**
     * Get the room for the record.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

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
     * Get the hostel for the attendance record.
     */
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    /**
     * Get the user who marked the attendance.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
