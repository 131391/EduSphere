<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreReligionRequest;
use App\Http\Requests\School\UpdateReligionRequest;
use App\Models\Religion;
use App\Services\School\ReligionService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class ReligionController extends TenantController
{
    use HasAjaxDataTable;

    protected ReligionService $service;

    public function __construct(ReligionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($religion) {
            return [
                'id' => $religion->id,
                'name' => $religion->name,
                'created_at' => $religion->created_at?->format('M d, Y'),
                'created_at_human' => $religion->created_at?->diffForHumans(),
            ];
        };

        $query = Religion::where('school_id', $schoolId);

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
                'total' => Religion::where('school_id', $schoolId)->count(),
            ]);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => Religion::where('school_id', $schoolId)->count(),
            ],
        ]);

        return view('school.religions.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreReligionRequest $request)
    {
        try {
            $religion = $this->service->createReligion(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Religion created successfully!',
                    'data' => $religion
                ]);
            }

            return $this->redirectWithSuccess(
                'school.religions.index',
                'Religion created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create religion: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create religion: ' . $e->getMessage());
        }
    }

    public function update(UpdateReligionRequest $request, $id)
    {
        try {
            $religion = Religion::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $religion = $this->service->updateReligion($religion, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Religion updated successfully!',
                    'data' => $religion
                ]);
            }

            return $this->redirectWithSuccess(
                'school.religions.index',
                'Religion updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update religion: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update religion: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $religion = Religion::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteReligion($religion);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Religion deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.religions.index',
                'Religion deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete religion: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.religions.index',
                'Failed to delete religion: ' . $e->getMessage()
            );
        }
    }
}
