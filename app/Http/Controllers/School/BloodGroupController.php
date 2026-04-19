<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreBloodGroupRequest;
use App\Http\Requests\School\UpdateBloodGroupRequest;
use App\Models\BloodGroup;
use App\Services\School\BloodGroupService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class BloodGroupController extends TenantController
{
    use HasAjaxDataTable;

    protected BloodGroupService $service;

    public function __construct(BloodGroupService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'created_at' => $group->created_at?->format('M d, Y'),
                'created_at_human' => $group->created_at?->diffForHumans(),
            ];
        };

        $query = BloodGroup::where('school_id', $schoolId);

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
                'total' => BloodGroup::where('school_id', $schoolId)->count(),
            ]);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => BloodGroup::where('school_id', $schoolId)->count(),
            ],
        ]);

        return view('school.blood-groups.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
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
