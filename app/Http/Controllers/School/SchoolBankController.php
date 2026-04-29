<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreSchoolBankRequest;
use App\Http\Requests\School\UpdateSchoolBankRequest;
use App\Models\SchoolBank;
use App\Services\School\SchoolBankService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class SchoolBankController extends TenantController
{
    use HasAjaxDataTable;

    protected SchoolBankService $service;

    public function __construct(SchoolBankService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id'             => $item->id,
                'bank_name'      => $item->bank_name,
                'account_number' => $item->account_number,
                'branch_name'    => $item->branch_name,
                'ifsc_code'      => $item->ifsc_code,
                'is_active'      => $item->is_active,
                'created_at'     => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = SchoolBank::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('bank_name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('account_number', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('branch_name', 'like', '%' . $request->input('search') . '%');
            });
        }

        $sort = $request->input('sort', 'bank_name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'bank_name', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('bank_name', 'asc');
        }

        $stats = [
            'total' => SchoolBank::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.school-banks.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreSchoolBankRequest $request)
    {
        try {
            $bank = $this->service->createBank(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'School bank created successfully!',
                    'data' => $bank
                ]);
            }

            return $this->redirectWithSuccess(
                'school.school-banks.index',
                'School bank created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create school bank: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create school bank: ' . $e->getMessage());
        }
    }

    public function update(UpdateSchoolBankRequest $request, $id)
    {
        try {
            $bank = SchoolBank::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $bank = $this->service->updateBank($bank, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'School bank updated successfully!',
                    'data' => $bank
                ]);
            }

            return $this->redirectWithSuccess(
                'school.school-banks.index',
                'School bank updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update school bank: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update school bank: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bank = SchoolBank::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteBank($bank);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'School bank deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.school-banks.index',
                'School bank deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete school bank: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.school-banks.index',
                'Failed to delete school bank: ' . $e->getMessage()
            );
        }
    }
}
