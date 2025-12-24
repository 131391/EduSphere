<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreRegistrationCodeRequest;
use App\Http\Requests\School\UpdateRegistrationCodeRequest;
use App\Models\RegistrationCode;
use App\Services\School\RegistrationCodeService;
use Illuminate\Http\Request;

class RegistrationCodeController extends TenantController
{
    protected RegistrationCodeService $service;

    public function __construct(RegistrationCodeService $service)
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

            return view('school.registration-codes.index', compact('codes'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load registration codes.');
        }
    }

    public function store(StoreRegistrationCodeRequest $request)
    {
        try {
            $this->service->createCode(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.registration-codes.index',
                'Registration code created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create registration code: ' . $e->getMessage());
        }
    }

    public function update(UpdateRegistrationCodeRequest $request, $id)
    {
        try {
            $code = RegistrationCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateCode($code, $request->validated());

            return $this->redirectWithSuccess(
                'school.registration-codes.index',
                'Registration code updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update registration code: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $code = RegistrationCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteCode($code);

            return $this->redirectWithSuccess(
                'school.registration-codes.index',
                'Registration code deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.registration-codes.index',
                'Failed to delete registration code: ' . $e->getMessage()
            );
        }
    }
}
