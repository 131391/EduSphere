<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreMiscellaneousFeeRequest;
use App\Http\Requests\School\UpdateMiscellaneousFeeRequest;
use App\Models\MiscellaneousFee;
use App\Services\School\MiscellaneousFeeService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class MiscellaneousFeeController extends TenantController
{
    use HasAjaxDataTable;

    protected MiscellaneousFeeService $service;

    public function __construct(MiscellaneousFeeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'amount' => $item->amount,
                'amount_formatted' => $this->formatCurrency($item->amount),
                'description' => $item->description,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = MiscellaneousFee::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'name', 'amount', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $stats = [
            'total_fees' => MiscellaneousFee::where('school_id', $schoolId)->count(),
            'total_amount' => MiscellaneousFee::where('school_id', $schoolId)->sum('amount'),
        ];
        $stats['total_amount_formatted'] = $this->formatCurrency($stats['total_amount']);

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.miscellaneous-fees.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreMiscellaneousFeeRequest $request)
    {
        try {
            $fee = $this->service->createFee(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee created successfully!',
                    'data' => $fee
                ]);
            }

            return $this->redirectWithSuccess(
                'school.miscellaneous-fees.index',
                'Fee created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create fee: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create fee: ' . $e->getMessage());
        }
    }

    public function update(UpdateMiscellaneousFeeRequest $request, $id)
    {
        try {
            $fee = MiscellaneousFee::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $fee = $this->service->updateFee($fee, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee updated successfully!',
                    'data' => $fee
                ]);
            }

            return $this->redirectWithSuccess(
                'school.miscellaneous-fees.index',
                'Fee updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update fee: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update fee: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $fee = MiscellaneousFee::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteFee($fee);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.miscellaneous-fees.index',
                'Fee deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete fee: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.miscellaneous-fees.index',
                'Failed to delete fee: ' . $e->getMessage()
            );
        }
    }

    private function formatCurrency($amount)
    {
        return number_format($amount, 2);
    }
}
