<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreBloodGroupRequest;
use App\Http\Requests\School\UpdateBloodGroupRequest;
use App\Models\BloodGroup;
use App\Services\School\BloodGroupService;
use Illuminate\Http\Request;

class BloodGroupController extends TenantController
{
    protected BloodGroupService $service;

    public function __construct(BloodGroupService $service)
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

            $groups = $this->service->getPaginatedGroups(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.blood-groups.index', compact('groups'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load blood groups.');
        }
    }

    public function store(StoreBloodGroupRequest $request)
    {
        try {
            $this->service->createGroup(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.blood-groups.index',
                'Blood group created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create blood group: ' . $e->getMessage());
        }
    }

    public function update(UpdateBloodGroupRequest $request, $id)
    {
        try {
            $group = BloodGroup::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateGroup($group, $request->validated());

            return $this->redirectWithSuccess(
                'school.blood-groups.index',
                'Blood group updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update blood group: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $group = BloodGroup::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteGroup($group);

            return $this->redirectWithSuccess(
                'school.blood-groups.index',
                'Blood group deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.blood-groups.index',
                'Failed to delete blood group: ' . $e->getMessage()
            );
        }
    }
}
