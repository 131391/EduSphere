<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreFeeTypeRequest;
use App\Http\Requests\School\UpdateFeeTypeRequest;
use App\Models\FeeType;
use App\Services\School\FeeTypeService;
use Illuminate\Http\Request;

class FeeTypeController extends TenantController
{
    protected FeeTypeService $feeTypeService;

    public function __construct(FeeTypeService $feeTypeService)
    {
        parent::__construct();
        $this->feeTypeService = $feeTypeService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'id'),
                'direction' => $request->input('direction', 'asc'),
            ];

            $feeTypes = $this->feeTypeService->getPaginatedFeeTypes(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.fee-types.index', compact('feeTypes'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load fee types.');
        }
    }

    public function store(StoreFeeTypeRequest $request)
    {
        try {
            $this->feeTypeService->createFeeType(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.fee-types.index',
                'Fee type created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create fee type: ' . $e->getMessage());
        }
    }

    public function update(UpdateFeeTypeRequest $request, $id)
    {
        try {
            $feeType = FeeType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->feeTypeService->updateFeeType($feeType, $request->validated());

            return $this->redirectWithSuccess(
                'school.fee-types.index',
                'Fee type updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update fee type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $feeType = FeeType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->feeTypeService->deleteFeeType($feeType);

            return $this->redirectWithSuccess(
                'school.fee-types.index',
                'Fee type deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.fee-types.index',
                'Failed to delete fee type: ' . $e->getMessage()
            );
        }
    }
}
