<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AdmissionFee;
use App\Models\ClassModel;
use App\Http\Requests\School\StoreAdmissionFeeRequest;
use App\Http\Requests\School\UpdateAdmissionFeeRequest;
use Illuminate\Http\Request;

use App\Traits\HasAjaxDataTable;

class AdmissionFeeController extends TenantController
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

        $query = AdmissionFee::where('school_id', $schoolId)
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

        return view('school.settings.admission-fee.index', array_merge($initialData, [
            'initialData' => $initialData,
            'stats' => $stats,
            'classes' => ClassModel::where('school_id', $schoolId)->get()
        ]));
    }

    protected function getTableStats()
    {
        return [
            'total_configurations' => AdmissionFee::where('school_id', $this->getSchoolId())->count(),
            'average_admission_fee' => number_format(AdmissionFee::where('school_id', $this->getSchoolId())->avg('amount') ?? 0, 2),
            'unique_classes' => AdmissionFee::where('school_id', $this->getSchoolId())->distinct('class_id')->count('class_id'),
        ];
    }

    public function create()
    {
        //
    }

    public function store(StoreAdmissionFeeRequest $request)
    {
        $validated = $request->validated();

        $fee = AdmissionFee::create([
            'school_id' => $this->getSchoolId(),
            'class_id' => $validated['class_id'],
            'amount' => $validated['amount'],
        ]);

        if (request()->wantsJson()) {
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

    public function update(UpdateAdmissionFeeRequest $request, AdmissionFee $admissionFee)
    {
        $this->authorizeTenant($admissionFee);
        $validated = $request->validated();
        
        $admissionFee->update([
            'class_id' => $validated['class_id'],
            'amount' => $validated['amount'],
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission fee updated successfully!',
                'data' => $admissionFee
            ]);
        }

        return redirect()->route('school.settings.admission-fee.index')
            ->with('success', 'Admission fee updated successfully.');
    }

    public function destroy(AdmissionFee $admissionFee)
    {
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
