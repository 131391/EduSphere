<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\HostelBedAssignment;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class HostelController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($hostel) {
            return [
                'id'                => $hostel->id,
                'hostel_name'       => $hostel->hostel_name,
                'hostel_incharge'   => $hostel->hostel_incharge,
                'capability'        => $hostel->capability ?? 0,
                'capability_label'  => ($hostel->capability ?? 0) . ' Beds',
                'hostel_create_date' => $hostel->hostel_create_date ? $hostel->hostel_create_date->format('d M, Y') : 'N/A',
                'raw' => [
                    'hostel_name' => $hostel->hostel_name,
                    'hostel_incharge' => $hostel->hostel_incharge,
                    'capability' => $hostel->capability,
                    'hostel_create_date' => $hostel->hostel_create_date ? $hostel->hostel_create_date->format('Y-m-d') : '',
                ],
            ];
        };

        $query = Hostel::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('hostel_name', 'like', "%{$search}%")
                  ->orWhere('hostel_incharge', 'like', "%{$search}%");
            });
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        $stats = [
            'total_hostels' => Hostel::where('school_id', $schoolId)->count(),
            'total_floors' => HostelFloor::where('school_id', $schoolId)->count(),
            'total_rooms' => HostelRoom::where('school_id', $schoolId)->count(),
            'total_beds' => (int) Hostel::where('school_id', $schoolId)->sum('capability'),
            'total_residents' => HostelBedAssignment::where('school_id', $schoolId)->whereNull('deleted_at')->count(),
        ];

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.hostel.hostels', [
            'initialData' => $initialData,
            'stats' => $stats,
        ]);
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hostels_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Hostel Name', 'Warden', 'Capacity (Beds)', 'Established On']);
            $query->orderBy('created_at', 'desc')->cursor()->each(function (Hostel $hostel) use ($file) {
                fputcsv($file, [
                    $hostel->hostel_name,
                    $hostel->hostel_incharge ?? 'Not Assigned',
                    $hostel->capability ?? 0,
                    $hostel->hostel_create_date ? $hostel->hostel_create_date->format('Y-m-d') : 'N/A',
                ]);
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        return redirect()->route('school.hostel.hostels.index', ['export' => 'csv']);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hostel_name' => 'required|string|max:255',
                'hostel_incharge' => 'nullable|string|max:255',
                'capability' => 'nullable|integer|min:1',
                'hostel_create_date' => 'nullable|date',
            ]);
            $validated['school_id'] = $this->getSchoolId();

            $hostel = Hostel::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Hostel added successfully.',
                'data' => $hostel,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Hostel $hostel)
    {
        $this->authorizeTenant($hostel);

        try {
            $validated = $request->validate([
                'hostel_name' => 'required|string|max:255',
                'hostel_incharge' => 'nullable|string|max:255',
                'capability' => 'nullable|integer|min:1',
                'hostel_create_date' => 'nullable|date',
            ]);

            $hostel->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Hostel updated successfully.',
                'data' => $hostel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Hostel $hostel)
    {
        $this->authorizeTenant($hostel);

        try {
            if ($hostel->floors()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this hostel.',
                    'errors' => ['hostel' => ['This hostel has floors assigned. Please remove them first.']],
                ], 422);
            }

            $hostel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hostel deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hostel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
