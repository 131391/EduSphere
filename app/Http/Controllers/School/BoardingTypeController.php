<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreBoardingTypeRequest;
use App\Http\Requests\School\UpdateBoardingTypeRequest;
use App\Models\BoardingType;
use App\Services\School\BoardingTypeService;
use Illuminate\Http\Request;

class BoardingTypeController extends TenantController
{
    protected BoardingTypeService $service;

    public function __construct(BoardingTypeService $service)
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

            $types = $this->service->getPaginatedTypes(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.boarding-types.index', compact('types'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load boarding types.');
        }
    }

    public function store(StoreBoardingTypeRequest $request)
    {
        try {
            $this->service->createType(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.boarding-types.index',
                'Boarding type created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create boarding type: ' . $e->getMessage());
        }
    }

    public function update(UpdateBoardingTypeRequest $request, $id)
    {
        try {
            $type = BoardingType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateType($type, $request->validated());

            return $this->redirectWithSuccess(
                'school.boarding-types.index',
                'Boarding type updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update boarding type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $type = BoardingType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteType($type);

            return $this->redirectWithSuccess(
                'school.boarding-types.index',
                'Boarding type deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.boarding-types.index',
                'Failed to delete boarding type: ' . $e->getMessage()
            );
        }
    }
}
