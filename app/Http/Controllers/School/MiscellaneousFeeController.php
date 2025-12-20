<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreMiscellaneousFeeRequest;
use App\Http\Requests\School\UpdateMiscellaneousFeeRequest;
use App\Models\MiscellaneousFee;
use App\Services\School\MiscellaneousFeeService;
use Illuminate\Http\Request;

class MiscellaneousFeeController extends TenantController
{
    protected MiscellaneousFeeService $service;

    public function __construct(MiscellaneousFeeService $service)
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

            $fees = $this->service->getPaginatedFees(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.miscellaneous-fees.index', compact('fees'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load fees.');
        }
    }

    public function store(StoreMiscellaneousFeeRequest $request)
    {
        try {
            $this->service->createFee(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.miscellaneous-fees.index',
                'Fee created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create fee: ' . $e->getMessage());
        }
    }

    public function update(UpdateMiscellaneousFeeRequest $request, $id)
    {
        try {
            $fee = MiscellaneousFee::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateFee($fee, $request->validated());

            return $this->redirectWithSuccess(
                'school.miscellaneous-fees.index',
                'Fee updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update fee: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $fee = MiscellaneousFee::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteFee($fee);

            return $this->redirectWithSuccess(
                'school.miscellaneous-fees.index',
                'Fee deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.miscellaneous-fees.index',
                'Failed to delete fee: ' . $e->getMessage()
            );
        }
    }
}
