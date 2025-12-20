<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreCorrespondingRelativeRequest;
use App\Http\Requests\School\UpdateCorrespondingRelativeRequest;
use App\Models\CorrespondingRelative;
use App\Services\School\CorrespondingRelativeService;
use Illuminate\Http\Request;

class CorrespondingRelativeController extends TenantController
{
    protected CorrespondingRelativeService $service;

    public function __construct(CorrespondingRelativeService $service)
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

            $relatives = $this->service->getPaginatedRelatives(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.corresponding-relatives.index', compact('relatives'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load corresponding relatives.');
        }
    }

    public function store(StoreCorrespondingRelativeRequest $request)
    {
        try {
            $this->service->createRelative(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.corresponding-relatives.index',
                'Corresponding relative created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create corresponding relative: ' . $e->getMessage());
        }
    }

    public function update(UpdateCorrespondingRelativeRequest $request, $id)
    {
        try {
            $relative = CorrespondingRelative::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateRelative($relative, $request->validated());

            return $this->redirectWithSuccess(
                'school.corresponding-relatives.index',
                'Corresponding relative updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update corresponding relative: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $relative = CorrespondingRelative::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteRelative($relative);

            return $this->redirectWithSuccess(
                'school.corresponding-relatives.index',
                'Corresponding relative deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.corresponding-relatives.index',
                'Failed to delete corresponding relative: ' . $e->getMessage()
            );
        }
    }
}
