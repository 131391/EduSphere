<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\Hostel;
use App\Models\StudentTransportAssignment;
use App\Models\HostelBedAssignment;
use App\Enums\GeneralStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentFacilityController extends TenantController
{
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $students = Student::where('school_id', $schoolId)
            ->with(['transportAssignment.route', 'hostelAssignment.hostel'])
            ->paginate(15);

        return view('school.facilities.index', compact('students'));
    }

    public function assignTransport(Request $request, Student $student)
    {
        $this->authorizeTenant($student);

        $validated = $request->validate([
            'transport_route_id' => 'required|exists:transport_routes,id',
            'pickup_point' => 'nullable|string',
            'start_date' => 'required|date',
        ]);

        DB::transaction(function () use ($student, $validated) {
            // Deactivate old assignment if exists
            StudentTransportAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update(['status' => GeneralStatus::Inactive, 'end_date' => now()]);

            StudentTransportAssignment::create(array_merge($validated, [
                'school_id' => $this->getSchoolId(),
                'student_id' => $student->id,
                'status' => GeneralStatus::Active,
            ]));
        });

        return back()->with('success', 'Transport assigned successfully.');
    }

    public function assignHostel(Request $request, Student $student)
    {
        $this->authorizeTenant($student);

        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'hostel_room_id' => 'nullable|exists:hostel_rooms,id',
            'start_date' => 'required|date',
        ]);

        DB::transaction(function () use ($student, $validated) {
            // Deactivate old assignment if exists
            HostelBedAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update(['status' => GeneralStatus::Inactive, 'end_date' => now()]);

            HostelBedAssignment::create(array_merge($validated, [
                'school_id' => $this->getSchoolId(),
                'student_id' => $student->id,
                'status' => GeneralStatus::Active,
            ]));
        });

        return back()->with('success', 'Hostel assigned successfully.');
    }
}
