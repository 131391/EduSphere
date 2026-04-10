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
        $fee = RegistrationFee::create([
            'school_id' => $this->getSchoolId(),
            'class_id' => $request->class_id,
            'amount' => $request->amount,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration fee added successfully!',
                'data' => $fee
            ]);
        }

        return back()->with('success', 'Registration fee added successfully.');
    }

    public function update(UpdateRegistrationFeeRequest $request, RegistrationFee $registrationFee)
    {
        $this->authorizeTenant($registrationFee);

        $registrationFee->update([
            'amount' => $request->amount,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration fee updated successfully!',
                'data' => $registrationFee
            ]);
        }

        return back()->with('success', 'Registration fee updated successfully.');
    }

    public function destroy(RegistrationFee $registrationFee)
    {
        $this->authorizeTenant($registrationFee);
        $registrationFee->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration fee deleted successfully!'
            ]);
        }

        return back()->with('success', 'Registration fee deleted successfully.');
    }
}
