<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreSchoolBankRequest;
use App\Http\Requests\School\UpdateSchoolBankRequest;
use App\Models\SchoolBank;
use App\Services\School\SchoolBankService;
use Illuminate\Http\Request;

class SchoolBankController extends TenantController
{
    protected SchoolBankService $service;

    public function __construct(SchoolBankService $service)
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

            $banks = $this->service->getPaginatedBanks(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.school-banks.index', compact('banks'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load school banks.');
        }
    }

    public function store(StoreSchoolBankRequest $request)
    {
        try {
            $this->service->createBank(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.school-banks.index',
                'School bank created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create school bank: ' . $e->getMessage());
        }
    }

    public function update(UpdateSchoolBankRequest $request, $id)
    {
        try {
            $bank = SchoolBank::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateBank($bank, $request->validated());

            return $this->redirectWithSuccess(
                'school.school-banks.index',
                'School bank updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update school bank: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bank = SchoolBank::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteBank($bank);

            return $this->redirectWithSuccess(
                'school.school-banks.index',
                'School bank deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.school-banks.index',
                'Failed to delete school bank: ' . $e->getMessage()
            );
        }
    }
}
