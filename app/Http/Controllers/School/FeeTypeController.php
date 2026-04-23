<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreFeeTypeRequest;
use App\Http\Requests\School\UpdateFeeTypeRequest;
use App\Models\FeeType;
use App\Services\School\FeeTypeService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class FeeTypeController extends TenantController
{
    use HasAjaxDataTable;

    protected FeeTypeService $feeTypeService;

    public function __construct(FeeTypeService $feeTypeService)
    {
        parent::__construct();
        $this->feeTypeService = $feeTypeService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', FeeType::class);

        $schoolId = $this->getSchoolId();

        $transformer = function ($feeType) {
            return [
                'id' => $feeType->id,
                'name' => $feeType->name,
                'created_at' => $feeType->created_at?->format('M d, Y'),
                'created_at_human' => $feeType->created_at?->diffForHumans(),
            ];
        };

        $query = FeeType::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        if (\in_array($sort, ['name', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, [
                'total' => FeeType::where('school_id', $schoolId)->count(),
            ]);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => FeeType::where('school_id', $schoolId)->count(),
            ],
        ]);

        return view('school.fee-types.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreFeeTypeRequest $request)
    {
        $this->authorize('create', FeeType::class);

        try {
            $feeType = $this->feeTypeService->createFeeType(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee type created successfully!',
                    'data' => $feeType
                ]);
            }

            return $this->redirectWithSuccess(
                'school.fee-types.index',
                'Fee type created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create fee type: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create fee type: ' . $e->getMessage());
        }
    }

    public function update(UpdateFeeTypeRequest $request, $id)
    {
        try {
            $feeType = FeeType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->authorize('update', $feeType);

            $feeType = $this->feeTypeService->updateFeeType($feeType, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee type updated successfully!',
                    'data' => $feeType
                ]);
            }

            return $this->redirectWithSuccess(
                'school.fee-types.index',
                'Fee type updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update fee type: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update fee type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $feeType = FeeType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->authorize('delete', $feeType);

            $this->feeTypeService->deleteFeeType($feeType);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee type deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.fee-types.index',
                'Fee type deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete fee type: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.fee-types.index',
                'Failed to delete fee type: ' . $e->getMessage()
            );
        }
    }
}
