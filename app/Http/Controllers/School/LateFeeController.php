<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\LateFee;
use App\Http\Requests\School\StoreLateFeeRequest;
use App\Http\Requests\School\UpdateLateFeeRequest;
use Illuminate\Http\Request;

class LateFeeController extends TenantController
{
    public function index()
    {
        $lateFees = LateFee::where('school_id', $this->getSchoolId())
            ->paginate(15);

        return view('school.late-fee.index', compact('lateFees'));
    }

    public function store(StoreLateFeeRequest $request)
    {
        LateFee::create([
            'school_id' => $this->getSchoolId(),
            'fine_date' => $request->fine_date,
            'late_fee_amount' => $request->late_fee_amount,
        ]);

        return back()->with('success', 'Late fee configuration created successfully.');
    }

    public function update(UpdateLateFeeRequest $request, LateFee $lateFee)
    {
        $this->authorizeTenant($lateFee);

        $lateFee->update([
            'fine_date' => $request->fine_date,
            'late_fee_amount' => $request->late_fee_amount,
        ]);

        return back()->with('success', 'Late fee configuration updated successfully.');
    }

    public function destroy(LateFee $lateFee)
    {
        $this->authorizeTenant($lateFee);
        $lateFee->delete();

        return back()->with('success', 'Late fee configuration deleted successfully.');
    }
}
