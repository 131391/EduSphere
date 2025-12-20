<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AdmissionFee;
use App\Models\ClassModel;
use App\Http\Requests\School\StoreAdmissionFeeRequest;
use App\Http\Requests\School\UpdateAdmissionFeeRequest;
use Illuminate\Http\Request;

class AdmissionFeeController extends TenantController
{
    public function index()
    {
        $fees = AdmissionFee::where('school_id', $this->getSchoolId())
            ->with('class')
            ->paginate(10);
            
        $classes = ClassModel::where('school_id', $this->getSchoolId())
            ->whereDoesntHave('admissionFee')
            ->get();

        return view('school.settings.admission-fee.index', compact('fees', 'classes'));
    }

    public function store(StoreAdmissionFeeRequest $request)
    {
        AdmissionFee::create([
            'school_id' => $this->getSchoolId(),
            'class_id' => $request->class_id,
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Admission fee added successfully.');
    }

    public function update(UpdateAdmissionFeeRequest $request, AdmissionFee $admissionFee)
    {
        $this->authorizeTenant($admissionFee);

        $admissionFee->update([
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Admission fee updated successfully.');
    }

    public function destroy(AdmissionFee $admissionFee)
    {
        $this->authorizeTenant($admissionFee);
        $admissionFee->delete();

        return back()->with('success', 'Admission fee deleted successfully.');
    }
}
