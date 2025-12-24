<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\RegistrationFee;
use App\Models\ClassModel;
use App\Http\Requests\School\StoreRegistrationFeeRequest;
use App\Http\Requests\School\UpdateRegistrationFeeRequest;
use Illuminate\Http\Request;

class RegistrationFeeController extends TenantController
{
    public function index()
    {
        $fees = RegistrationFee::where('school_id', $this->getSchoolId())
            ->with('class')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        $classes = ClassModel::where('school_id', $this->getSchoolId())
            ->whereDoesntHave('registrationFee')
            ->get();

        return view('school.settings.registration-fee.index', compact('fees', 'classes'));
    }

    public function store(StoreRegistrationFeeRequest $request)
    {
        RegistrationFee::create([
            'school_id' => $this->getSchoolId(),
            'class_id' => $request->class_id,
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Registration fee added successfully.');
    }

    public function update(UpdateRegistrationFeeRequest $request, RegistrationFee $registrationFee)
    {
        $this->authorizeTenant($registrationFee);

        $registrationFee->update([
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Registration fee updated successfully.');
    }

    public function destroy(RegistrationFee $registrationFee)
    {
        $this->authorizeTenant($registrationFee);
        $registrationFee->delete();

        return back()->with('success', 'Registration fee deleted successfully.');
    }
}
