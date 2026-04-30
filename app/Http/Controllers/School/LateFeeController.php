<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreLateFeeRequest;
use App\Http\Requests\School\UpdateLateFeeRequest;
use App\Models\LateFee;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class LateFeeController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $this->authorize('viewAny', LateFee::class);

        $schoolId = $this->getSchoolId();

        $transformer = function ($lateFee) {
            return [
                'id' => $lateFee->id,
                'fine_date' => $lateFee->fine_date,
                'late_fee_amount' => (float) $lateFee->late_fee_amount,
                'late_fee_amount_formatted' => number_format($lateFee->late_fee_amount, 2),
                'created_at' => $lateFee->created_at?->format('M d, Y'),
                'created_at_human' => $lateFee->created_at?->diffForHumans(),
            ];
        };

        $query = LateFee::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('fine_date', 'like', '%' . $search . '%')
                  ->orWhere('late_fee_amount', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        if (\in_array($sort, ['fine_date', 'late_fee_amount', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, [
                'total' => LateFee::where('school_id', $schoolId)->count(),
            ]);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => LateFee::where('school_id', $schoolId)->count(),
            ],
        ]);

        return view('school.late-fee.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreLateFeeRequest $request)
    {
        $this->ensureSchoolActive();
        $this->authorize('create', LateFee::class);

        $lateFee = LateFee::create([
            'school_id' => $this->getSchoolId(),
            'fine_date' => $request->fine_date,
            'late_fee_amount' => $request->late_fee_amount,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Late fee configuration created successfully!',
                'data' => $lateFee
            ]);
        }

        return back()->with('success', 'Late fee configuration created successfully.');
    }

    public function update(UpdateLateFeeRequest $request, LateFee $lateFee)
    {
        $this->authorizeTenant($lateFee);
        $this->authorize('update', $lateFee);

        $lateFee->update([
            'fine_date' => $request->fine_date,
            'late_fee_amount' => $request->late_fee_amount,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Late fee configuration updated successfully!',
                'data' => $lateFee
            ]);
        }

        return back()->with('success', 'Late fee configuration updated successfully.');
    }

    public function destroy(LateFee $lateFee)
    {
        $this->authorizeTenant($lateFee);
        $this->authorize('delete', $lateFee);
        $lateFee->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Late fee configuration deleted successfully!'
            ]);
        }

        return back()->with('success', 'Late fee configuration deleted successfully.');
    }
}
