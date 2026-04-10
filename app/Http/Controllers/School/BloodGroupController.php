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
            $group = $this->service->createGroup(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Blood group created successfully!',
                    'data' => $group
                ]);
            }

            return $this->redirectWithSuccess(
                'school.blood-groups.index',
                'Blood group created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create blood group: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create blood group: ' . $e->getMessage());
        }
    }

    public function update(UpdateBloodGroupRequest $request, $id)
    {
        try {
            $group = BloodGroup::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $group = $this->service->updateGroup($group, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Blood group updated successfully!',
                    'data' => $group
                ]);
            }

            return $this->redirectWithSuccess(
                'school.blood-groups.index',
                'Blood group updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update blood group: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update blood group: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $group = BloodGroup::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteGroup($group);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Blood group deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.blood-groups.index',
                'Blood group deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete blood group: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.blood-groups.index',
                'Failed to delete blood group: ' . $e->getMessage()
            );
        }
    }
}
