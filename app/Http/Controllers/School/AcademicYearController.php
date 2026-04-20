<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreAcademicYearRequest;
use App\Http\Requests\School\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\School\AcademicYearService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class AcademicYearController extends TenantController
{
    use HasAjaxDataTable;

    protected AcademicYearService $academicYearService;

    public function __construct(AcademicYearService $academicYearService)
    {
        parent::__construct();
        $this->academicYearService = $academicYearService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = fn($row) => [
            'id'         => $row->id,
            'name'       => $row->name,
            'start_date' => $row->start_date?->format('Y-m-d'),
            'end_date'   => $row->end_date?->format('Y-m-d'),
            'duration'   => $row->start_date?->format('M d, Y') . ' — ' . $row->end_date?->format('M d, Y'),
            'is_current' => $row->is_current === \App\Enums\YesNo::Yes,
            'created_at' => $row->created_at?->format('M d, Y'),
        ];

        $query = AcademicYear::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = in_array($request->input('sort'), ['name', 'start_date', 'end_date', 'created_at'])
            ? $request->input('sort') : 'start_date';
        $query->orderBy($sort, $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc');

        $stats = ['total' => AcademicYear::where('school_id', $schoolId)->count(),
                  'current' => AcademicYear::where('school_id', $schoolId)->where('is_current', \App\Enums\YesNo::Yes)->count()];

        if ($request->expectsJson() || $request->ajax() || $request->has('page') || $request->filled('filters')) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, ['stats' => $stats]);

        return view('school.academic-years.index', ['initialData' => $initialData, 'stats' => $stats]);
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
