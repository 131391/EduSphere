<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreBoardingTypeRequest;
use App\Http\Requests\School\UpdateBoardingTypeRequest;
use App\Models\BoardingType;
use App\Services\School\BoardingTypeService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class BoardingTypeController extends TenantController
{
    use HasAjaxDataTable;

    protected BoardingTypeService $service;

    public function __construct(BoardingTypeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'created_at' => $type->created_at?->format('M d, Y'),
            ];
        };

        $query = BoardingType::where('school_id', $schoolId);

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
            'total' => BoardingType::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.boarding-types.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreBoardingTypeRequest $request)
    {
        try {
            $type = $this->service->createType(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Boarding type created successfully!',
                    'data' => $type
                ]);
            }

            return $this->redirectWithSuccess(
                'school.boarding-types.index',
                'Boarding type created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create boarding type: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create boarding type: ' . $e->getMessage());
        }
    }

    public function update(UpdateBoardingTypeRequest $request, $id)
    {
        try {
            $type = BoardingType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $type = $this->service->updateType($type, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Boarding type updated successfully!',
                    'data' => $type
                ]);
            }

            return $this->redirectWithSuccess(
                'school.boarding-types.index',
                'Boarding type updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update boarding type: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update boarding type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $type = BoardingType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteType($type);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Boarding type deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.boarding-types.index',
                'Boarding type deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete boarding type: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.boarding-types.index',
                'Failed to delete boarding type: ' . $e->getMessage()
            );
        }
    }
}
