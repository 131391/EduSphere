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
