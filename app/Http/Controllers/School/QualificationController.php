<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreQualificationRequest;
use App\Http\Requests\School\UpdateQualificationRequest;
use App\Models\Qualification;
use App\Services\School\QualificationService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class QualificationController extends TenantController
{
    use HasAjaxDataTable;

    protected QualificationService $service;

    public function __construct(QualificationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = Qualification::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'name', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $stats = [
            'total' => Qualification::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.qualifications.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
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
