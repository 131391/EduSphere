<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\AssignTransportRequest;
use App\Models\Student;
use App\Services\School\StudentTransportService;

class StudentTransportController extends TenantController
{
    protected StudentTransportService $transportService;

    public function __construct(StudentTransportService $transportService)
    {
        parent::__construct();
        $this->transportService = $transportService;
    }

    /**
     * Handle the assignment or removal of transport for a student.
     *
     * @param AssignTransportRequest $request
     * @param Student $student
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function assignTransport(AssignTransportRequest $request, Student $student)
    {
        $this->authorizeTenant($student);

        try {
            $validated = $request->validated();

            if ($validated['action'] === 'remove') {
                $this->transportService->removeTransport($student);
                $message = 'Transport removed successfully.';
            } else {
                $this->transportService->assignTransport($this->getSchool(), $student, $validated);
                $message = 'Transport assigned successfully.';
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process transport assignment: ' . $e->getMessage()
                ], 500);
            }
            return back()->withError('Failed to process transport assignment: ' . $e->getMessage());
        }
    }
}
