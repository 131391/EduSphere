<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Student;
use App\Services\School\StudentTransportService;
use App\Services\School\StudentHostelService;
use App\Http\Requests\School\AssignHostelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentFacilityController extends TenantController
{
    protected StudentTransportService $transportService;
    protected StudentHostelService $hostelService;

    public function __construct(
        StudentTransportService $transportService,
        StudentHostelService $hostelService
    ) {
        parent::__construct();
        $this->transportService = $transportService;
        $this->hostelService = $hostelService;
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
            'action' => 'required|in:assign,remove',
            'route_id' => ['required_if:action,assign', \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $this->getSchoolId())],
            'bus_stop_id' => ['required_if:action,assign', \Illuminate\Validation\Rule::exists('bus_stops', 'id')->where('school_id', $this->getSchoolId())],
            'academic_year_id' => ['required_if:action,assign', \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())],
            'start_date' => 'required_if:action,assign|date',
        ]);

        if ($validated['action'] === 'remove') {
            $this->transportService->removeTransport($student);
        } else {
            $this->transportService->assignTransport($this->getSchool(), $student, $validated);
        }

        return back()->with('success', 'Transport assigned successfully.');
    }

    public function assignHostel(AssignHostelRequest $request, Student $student)
    {
        $this->authorizeTenant($student);

        $validated = $request->validated();

        if ($validated['action'] === 'remove') {
            $this->hostelService->removeHostel($student);
        } else {
            $this->hostelService->assignHostel($this->getSchool(), $student, $validated);
        }

        return back()->with('success', 'Hostel assignment updated successfully.');
    }
}
