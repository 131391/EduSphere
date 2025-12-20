<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreReligionRequest;
use App\Http\Requests\School\UpdateReligionRequest;
use App\Models\Religion;
use App\Services\School\ReligionService;
use Illuminate\Http\Request;

class ReligionController extends TenantController
{
    protected ReligionService $service;

    public function __construct(ReligionService $service)
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

            $religions = $this->service->getPaginatedReligions(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.religions.index', compact('religions'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load religions.');
        }
    }

    public function store(StoreReligionRequest $request)
    {
        try {
            $this->service->createReligion(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.religions.index',
                'Religion created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create religion: ' . $e->getMessage());
        }
    }

    public function update(UpdateReligionRequest $request, $id)
    {
        try {
            $religion = Religion::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateReligion($religion, $request->validated());

            return $this->redirectWithSuccess(
                'school.religions.index',
                'Religion updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update religion: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $religion = Religion::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteReligion($religion);

            return $this->redirectWithSuccess(
                'school.religions.index',
                'Religion deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.religions.index',
                'Failed to delete religion: ' . $e->getMessage()
            );
        }
    }
}
