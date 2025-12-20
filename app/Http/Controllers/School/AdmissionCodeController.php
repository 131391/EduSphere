<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreAdmissionCodeRequest;
use App\Http\Requests\School\UpdateAdmissionCodeRequest;
use App\Models\AdmissionCode;
use App\Services\School\AdmissionCodeService;
use Illuminate\Http\Request;

class AdmissionCodeController extends TenantController
{
    protected AdmissionCodeService $service;

    public function __construct(AdmissionCodeService $service)
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

            $codes = $this->service->getPaginatedCodes(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.admission-codes.index', compact('codes'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load admission codes.');
        }
    }

    public function store(StoreAdmissionCodeRequest $request)
    {
        try {
            $this->service->createCode(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.admission-codes.index',
                'Admission code created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create admission code: ' . $e->getMessage());
        }
    }

    public function update(UpdateAdmissionCodeRequest $request, $id)
    {
        try {
            $code = AdmissionCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateCode($code, $request->validated());

            return $this->redirectWithSuccess(
                'school.admission-codes.index',
                'Admission code updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update admission code: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $code = AdmissionCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteCode($code);

            return $this->redirectWithSuccess(
                'school.admission-codes.index',
                'Admission code deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.admission-codes.index',
                'Failed to delete admission code: ' . $e->getMessage()
            );
        }
    }
}
