<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreFeeNameRequest;
use App\Http\Requests\School\UpdateFeeNameRequest;
use App\Models\FeeName;
use App\Services\School\FeeNameService;
use Illuminate\Http\Request;

class FeeNameController extends TenantController
{
    protected FeeNameService $service;

    public function __construct(FeeNameService $service)
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

            $feeNames = $this->service->getPaginatedFeeNames(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.fee-names.index', compact('feeNames'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load fee names.');
        }
    }

    public function store(StoreFeeNameRequest $request)
    {
        try {
            $this->service->createFeeName(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.fee-names.index',
                'Fee name created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create fee name: ' . $e->getMessage());
        }
    }

    public function update(UpdateFeeNameRequest $request, $id)
    {
        try {
            $feeName = FeeName::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateFeeName($feeName, $request->validated());

            return $this->redirectWithSuccess(
                'school.fee-names.index',
                'Fee name updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update fee name: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $feeName = FeeName::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteFeeName($feeName);

            return $this->redirectWithSuccess(
                'school.fee-names.index',
                'Fee name deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.fee-names.index',
                'Failed to delete fee name: ' . $e->getMessage()
            );
        }
    }
}
