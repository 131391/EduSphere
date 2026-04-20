<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\RegistrationFee;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\HasAjaxDataTable;

class RegistrationFeeController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = fn($row) => [
            'id'         => $row->id,
            'class_id'   => $row->class_id,
            'class_name' => $row->class?->name ?? 'N/A',
            'amount'     => (float) $row->amount,
            'created_at' => $row->created_at->format('d M, Y'),
        ];

        $query = RegistrationFee::where('school_id', $schoolId)->with('class');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('class', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $stats = $this->getStats($schoolId);

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData       = $this->getHydrationData($query, $transformer, ['stats' => $stats]);
        $allClasses        = ClassModel::where('school_id', $schoolId)->orderBy('name')->get();
        $assignedClassIds  = RegistrationFee::where('school_id', $schoolId)->pluck('class_id');
        $unassignedClasses = $allClasses->whereNotIn('id', $assignedClassIds)->values();

        return view('school.settings.registration-fee.index', [
            'initialData'       => $initialData,
            'stats'             => $stats,
            'classes'           => $allClasses,
            'unassignedClasses' => $unassignedClasses,
        ]);
    }

    public function store(Request $request)
    {
        $schoolId  = $this->getSchoolId();
        $validator = Validator::make($request->all(), [
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('school_id', $schoolId),
                Rule::unique('registration_fees')->where('school_id', $schoolId),
            ],
            'amount' => 'required|numeric|min:0|max:999999.99',
        ], [
            'class_id.exists'  => 'The selected class is not valid for this school.',
            'class_id.unique'  => 'A registration fee for this class already exists.',
            'amount.required'  => 'Please enter a fee amount.',
            'amount.numeric'   => 'Amount must be a number.',
            'amount.min'       => 'Amount cannot be negative.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fee = RegistrationFee::create([
            'school_id' => $schoolId,
            'class_id'  => $request->class_id,
            'amount'    => $request->amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration fee saved successfully.',
            'data'    => array_merge($fee->toArray(), ['class_name' => $fee->class?->name]),
        ]);
    }

    public function update(Request $request, RegistrationFee $registrationFee)
    {
        $this->authorizeTenant($registrationFee);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0|max:999999.99',
        ], [
            'amount.required' => 'Please enter a fee amount.',
            'amount.numeric'  => 'Amount must be a number.',
            'amount.min'      => 'Amount cannot be negative.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $registrationFee->update(['amount' => $request->amount]);

        return response()->json([
            'success' => true,
            'message' => 'Registration fee updated successfully.',
            'data'    => $registrationFee->fresh()->toArray(),
        ]);
    }

    public function destroy(RegistrationFee $registrationFee)
    {
        $this->authorizeTenant($registrationFee);
        $registrationFee->delete();

        return response()->json(['success' => true, 'message' => 'Registration fee deleted.']);
    }

    private function getStats(int $schoolId): array
    {
        return [
            'total_configurations'     => RegistrationFee::where('school_id', $schoolId)->count(),
            'average_registration_fee' => number_format(RegistrationFee::where('school_id', $schoolId)->avg('amount') ?? 0, 2),
            'unique_classes'           => RegistrationFee::where('school_id', $schoolId)->distinct('class_id')->count('class_id'),
        ];
    }
}
