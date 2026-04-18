<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\GeneralStatus;

class StudentTransportAssignment extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'student_id',
        'route_id',
        'bus_stop_id',
        'vehicle_id',
        'fee_per_month',
        'academic_year_id',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'fee_per_month' => 'decimal:2',
        'status' => GeneralStatus::class,
    ];

    /**
     * Get the school that owns the assignment.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student that owns the assignment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the route for the assignment.
     */
    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    /**
     * Get the bus stop for the assignment.
     */
    public function busStop()
    {
        return $this->belongsTo(BusStop::class);
    }

    /**
     * Get the vehicle for the assignment.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the academic year for the assignment.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
