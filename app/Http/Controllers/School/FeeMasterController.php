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

class FeeMasterController extends TenantController
{
    public function index()
    {
        $fees = FeeMaster::where('school_id', $this->getSchoolId())
            ->with(['class', 'feeName', 'feeType'])
            ->paginate(15);
            
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $feeNames = FeeName::where('school_id', $this->getSchoolId())->active()->get();
        $feeTypes = FeeType::where('school_id', $this->getSchoolId())->active()->get();

        return view('school.fee-master.index', compact('fees', 'classes', 'feeNames', 'feeTypes'));
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

        return back()->with('success', 'Fee configurations saved successfully.');
    }

    public function update(UpdateFeeMasterRequest $request, FeeMaster $feeMaster)
    {
        $this->authorizeTenant($feeMaster);

        $feeMaster->update([
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Fee configuration updated successfully.');
    }

    public function destroy(FeeMaster $feeMaster)
    {
        $this->authorizeTenant($feeMaster);
        $feeMaster->delete();

        return back()->with('success', 'Fee configuration deleted successfully.');
    }
}
