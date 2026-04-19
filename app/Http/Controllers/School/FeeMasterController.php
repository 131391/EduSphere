<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\FeeMaster;
use App\Models\ClassModel;
use App\Models\FeeName;
use App\Models\FeeType;
use App\Http\Requests\School\StoreFeeMasterRequest;
use App\Http\Requests\School\UpdateFeeMasterRequest;
use Illuminate\Http\Request;

use App\Traits\HasAjaxDataTable;

class FeeMasterController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function($row) {
            return [
                'id' => $row->id,
                'class_name' => $row->class?->name ?? 'N/A',
                'fee_name' => $row->feeName?->name ?? 'N/A',
                'fee_type' => $row->feeType?->name ?? 'N/A',
                'amount' => number_format($row->amount, 2),
            ];
        };

        $query = FeeMaster::where('school_id', $schoolId)
            ->with(['class', 'feeName', 'feeType']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('fee_type_id')) {
            $query->where('fee_type_id', $request->fee_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('feeName', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $stats = $this->getTableStats();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.fee-master.index', [
            'initialData' => $initialData,
            'stats' => $stats,
            'classes' => ClassModel::where('school_id', $schoolId)->get(),
            'feeNames' => FeeName::where('school_id', $schoolId)->active()->get(),
            'feeTypes' => FeeType::where('school_id', $schoolId)->active()->get(),
        ]);
    }

    protected function getTableStats()
    {
        return [
            'total_configurations' => FeeMaster::where('school_id', $this->getSchoolId())->count(),
            'fee_types_count' => FeeMaster::where('school_id', $this->getSchoolId())->distinct('fee_type_id')->count('fee_type_id'),
            'classes_mapped' => FeeMaster::where('school_id', $this->getSchoolId())->distinct('class_id')->count('class_id'),
        ];
    }

    public function store(StoreFeeMasterRequest $request)
    {
        foreach ($request->amounts as $feeNameId => $amount) {
            if ($amount !== null && $amount !== '') {
                FeeMaster::updateOrCreate(
                    [
                        'school_id' => $this->getSchoolId(),
                        'class_id' => $request->class_id,
                        'fee_name_id' => $feeNameId,
                        'fee_type_id' => $request->fee_type_id,
                    ],
                    [
                        'amount' => $amount,
                    ]
                );
            }
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee configurations saved successfully!'
            ]);
        }

        return back()->with('success', 'Fee configurations saved successfully.');
    }

    public function update(UpdateFeeMasterRequest $request, FeeMaster $feeMaster)
    {
        $this->authorizeTenant($feeMaster);

        $feeMaster->update([
            'amount' => $request->amount,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee configuration updated successfully!',
                'data' => $feeMaster
            ]);
        }

        return back()->with('success', 'Fee configuration updated successfully.');
    }

    public function destroy(FeeMaster $feeMaster)
    {
        $this->authorizeTenant($feeMaster);
        $feeMaster->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee configuration deleted successfully!'
            ]);
        }

        return back()->with('success', 'Fee configuration deleted successfully.');
    }
}
