<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AdmissionFee;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class AdmissionFeeController extends TenantController
{
    public function index()
    {
        $fees = AdmissionFee::with('class')
            ->where('school_id', $this->getSchoolId())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        
        return view('school.settings.admission-fee.index', compact('fees', 'classes'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $fee = AdmissionFee::create([
            'school_id' => $this->getSchoolId(),
            'class_id' => $validated['class_id'],
            'amount' => $validated['amount'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission fee added successfully!',
                'data' => $fee
            ]);
        }

        return redirect()->route('school.settings.admission-fee.index')
            ->with('success', 'Admission fee added successfully.');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $admissionFee = AdmissionFee::findOrFail($id);
        $this->authorizeTenant($admissionFee);
        
        $admissionFee->update([
            'class_id' => $validated['class_id'],
            'amount' => $validated['amount'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission fee updated successfully!',
                'data' => $admissionFee
            ]);
        }

        return redirect()->route('school.settings.admission-fee.index')
            ->with('success', 'Admission fee updated successfully.');
    }

    public function destroy($id)
    {
        $admissionFee = AdmissionFee::findOrFail($id);
        $this->authorizeTenant($admissionFee);
        $admissionFee->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission fee deleted successfully!'
            ]);
        }

        return redirect()->route('school.settings.admission-fee.index')
            ->with('success', 'Admission fee deleted successfully.');
    }
}
