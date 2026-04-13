<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreAcademicYearRequest;
use App\Http\Requests\School\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\School\AcademicYearService;
use Illuminate\Http\Request;

class AcademicYearController extends TenantController
{
    protected AcademicYearService $academicYearService;

    public function __construct(AcademicYearService $academicYearService)
    {
        parent::__construct();
        $this->academicYearService = $academicYearService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'start_date'),
                'direction' => $request->input('direction', 'desc'),
            ];

            $academicYears = $this->academicYearService->getPaginatedAcademicYears(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            $stats = $this->academicYearService->getStatistics($this->getSchool());

            return view('school.academic-years.index', compact('academicYears', 'stats'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load academic years.');
        }
    }

    public function create()
    {
        return view('school.academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request)
    {
        try {
            $academicYear = $this->academicYearService->createAcademicYear(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                session()->flash('success', 'Academic year created successfully!');
                return response()->json([
                    'success' => true,
                    'message' => 'Academic year created successfully!',
                    'data' => $academicYear
                ]);
            }

            return $this->redirectWithSuccess(
                'school.academic-years.index',
                'Academic year created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create academic year: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create academic year: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $academicYear = AcademicYear::where('school_id', $this->getSchoolId())
                ->with(['students', 'fees'])
                ->withCount(['students', 'fees'])
                ->findOrFail($id);

            return view('school.academic-years.show', compact('academicYear'));
        } catch (\Exception $e) {
            return $this->redirectWithError('school.academic-years.index', 'Academic year not found.');
        }
    }

    public function edit($id)
    {
        try {
            $academicYear = AcademicYear::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            return view('school.academic-years.edit', compact('academicYear'));
        } catch (\Exception $e) {
            return $this->redirectWithError('school.academic-years.index', 'Academic year not found.');
        }
    }

    public function update(UpdateAcademicYearRequest $request, $id)
    {
        try {
            $academicYear = AcademicYear::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $academicYear = $this->academicYearService->updateAcademicYear(
                $academicYear,
                $request->validated()
            );

            if ($request->wantsJson()) {
                session()->flash('success', 'Academic year updated successfully!');
                return response()->json([
                    'success' => true,
                    'message' => 'Academic year updated successfully!',
                    'data' => $academicYear
                ]);
            }

            return $this->redirectWithSuccess(
                'school.academic-years.index',
                'Academic year updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update academic year: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update academic year: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $academicYear = AcademicYear::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->academicYearService->deleteAcademicYear($academicYear);

            if (request()->wantsJson()) {
                session()->flash('success', 'Academic year deleted successfully!');
                return response()->json([
                    'success' => true,
                    'message' => 'Academic year deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.academic-years.index',
                'Academic year deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete academic year: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.academic-years.index',
                'Failed to delete academic year: ' . $e->getMessage()
            );
        }
    }
}
