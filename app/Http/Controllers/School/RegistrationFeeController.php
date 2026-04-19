<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\RegistrationFee;
use App\Models\ClassModel;
use App\Http\Requests\School\StoreRegistrationFeeRequest;
use App\Http\Requests\School\UpdateRegistrationFeeRequest;
use Illuminate\Http\Request;

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

        $transformer = function($row) {
            return [
                'id' => $row->id,
                'class_id' => $row->class_id,
                'class_name' => $row->class?->name ?? 'N/A',
                'amount' => number_format($row->amount, 2),
                'created_at' => $row->created_at->format('d M, Y'),
            ];
        };

        $query = RegistrationFee::where('school_id', $schoolId)
            ->with('class');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('class', function($q) use ($search) {
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

        return view('school.settings.registration-fee.index', array_merge($initialData, [
            'initialData' => $initialData,
            'stats' => $stats,
            'classes' => ClassModel::where('school_id', $schoolId)
                ->whereDoesntHave('registrationFee')
                ->get()
        ]));
    }

    protected function getTableStats()
    {
        return [
            'total_configurations' => RegistrationFee::where('school_id', $this->getSchoolId())->count(),
            'average_registration_fee' => number_format(RegistrationFee::where('school_id', $this->getSchoolId())->avg('amount') ?? 0, 2),
            'unique_classes' => RegistrationFee::where('school_id', $this->getSchoolId())->distinct('class_id')->count('class_id'),
        ];
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
