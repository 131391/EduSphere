<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreQualificationRequest;
use App\Http\Requests\School\UpdateQualificationRequest;
use App\Models\Qualification;
use App\Services\School\QualificationService;
use Illuminate\Http\Request;

class QualificationController extends TenantController
{
    protected QualificationService $service;

    public function __construct(QualificationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'id'),
                'direction' => $request->input('direction', 'asc'),
            ];

            $qualifications = $this->service->getPaginatedQualifications(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.qualifications.index', compact('qualifications'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load qualifications.');
        }
    }

    public function store(StoreQualificationRequest $request)
    {
        try {
            $qualification = $this->service->createQualification(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Qualification created successfully!',
                    'data' => $qualification
                ]);
            }

            return $this->redirectWithSuccess(
                'school.qualifications.index',
                'Qualification created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create qualification: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create qualification: ' . $e->getMessage());
        }
    }

    public function update(UpdateQualificationRequest $request, $id)
    {
        try {
            $qualification = Qualification::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $qualification = $this->service->updateQualification($qualification, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Qualification updated successfully!',
                    'data' => $qualification
                ]);
            }

            return $this->redirectWithSuccess(
                'school.qualifications.index',
                'Qualification updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update qualification: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update qualification: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $qualification = Qualification::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteQualification($qualification);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Qualification deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.qualifications.index',
                'Qualification deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete qualification: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.qualifications.index',
                'Failed to delete qualification: ' . $e->getMessage()
            );
        }
    }
}
