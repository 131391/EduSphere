<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\Hostel;
use App\Models\StudentTransportAssignment;
use App\Models\HostelBedAssignment;
use App\Enums\GeneralStatus;
use App\Services\School\StudentTransportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentFacilityController extends TenantController
{
    protected StudentTransportService $transportService;

    public function __construct(StudentTransportService $transportService)
    {
        parent::__construct();
        $this->transportService = $transportService;
    }
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
            'action'             => 'required|in:assign,remove',
            'route_id'           => ['required_if:action,assign', \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $this->getSchoolId())],
            'bus_stop_id'        => ['required_if:action,assign', \Illuminate\Validation\Rule::exists('bus_stops', 'id')->where('school_id', $this->getSchoolId())],
            'academic_year_id'   => ['required_if:action,assign', \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())],
            'start_date'         => 'required_if:action,assign|date',
        ]);

        if ($validated['action'] === 'remove') {
            $this->transportService->removeTransport($student);
        } else {
            $this->transportService->assignTransport($this->getSchool(), $student, $validated);
        }

        return back()->with('success', 'Transport assigned successfully.');
    }

    public function assignHostel(Request $request, Student $student)
    {
        $this->authorizeTenant($student);

        $validated = $request->validate([
            'hostel_id'      => ['required', \Illuminate\Validation\Rule::exists('hostels', 'id')->where('school_id', $this->getSchoolId())],
            'hostel_room_id' => ['nullable', \Illuminate\Validation\Rule::exists('hostel_rooms', 'id')->where('school_id', $this->getSchoolId())],
            'start_date'     => 'required|date',
        ]);

        DB::transaction(function () use ($student, $validated) {
            HostelBedAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update(['status' => GeneralStatus::Inactive, 'end_date' => now()]);

            HostelBedAssignment::create(array_merge($validated, [
                'school_id'  => $this->getSchoolId(),
                'student_id' => $student->id,
                'status'     => GeneralStatus::Active,
            ]));
        });

        return back()->with('success', 'Hostel assigned successfully.');
    }
}
