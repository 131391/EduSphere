<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Http\Requests\Receptionist\StoreHostelFloorRequest;
use App\Http\Requests\Receptionist\UpdateHostelFloorRequest;
use App\Models\HostelFloor;
use App\Models\Hostel;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HostelFloorController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($floor) {
            return [
                'id'                => $floor->id,
                'floor_name'        => $floor->floor_name,
                'hostel_id'         => $floor->hostel_id,
                'hostel_name'       => $floor->hostel->hostel_name ?? 'N/A',
                'total_room'        => $floor->total_room ?? 0,
                'total_room_label'  => ($floor->total_room ?? 0) . ' Rooms',
                'floor_create_date' => $floor->floor_create_date ? $floor->floor_create_date->format('d M, Y') : 'N/A',
                'raw' => [
                    'hostel_id'         => $floor->hostel_id,
                    'floor_name'        => $floor->floor_name,
                    'total_room'        => $floor->total_room,
                    'floor_create_date' => $floor->floor_create_date ? $floor->floor_create_date->format('Y-m-d') : '',
                ],
            ];
        };

        $query = HostelFloor::where('school_id', $schoolId)->with('hostel');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('floor_name', 'like', "%{$search}%")
                  ->orWhereHas('hostel', function ($hq) use ($search) {
                      $hq->where('hostel_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();

        $stats = [
            'total_floors'  => HostelFloor::where('school_id', $schoolId)->count(),
            'total_rooms'   => (int) HostelFloor::where('school_id', $schoolId)->sum('total_room'),
            'total_hostels' => Hostel::where('school_id', $schoolId)->count(),
        ];

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('receptionist.hostel-floors.index', [
            'initialData' => $initialData,
            'stats'       => $stats,
            'hostels'     => $hostels,
        ]);
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hostel_floors_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Hostel', 'Floor Name', 'Total Rooms', 'Established On']);
            $query->orderBy('created_at', 'desc')->cursor()->each(function ($floor) use ($file) {
                fputcsv($file, [
                    $floor->hostel->hostel_name ?? 'N/A',
                    $floor->floor_name,
                    $floor->total_room ?? 0,
                    $floor->floor_create_date ? $floor->floor_create_date->format('Y-m-d') : 'N/A',
                ]);
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(StoreHostelFloorRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['school_id'] = $this->getSchoolId();

            $floor = DB::transaction(function () use ($validated) {
                return HostelFloor::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Floor added successfully.',
                'data' => $floor->load('hostel'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateHostelFloorRequest $request, HostelFloor $hostelFloor)
    {
        $this->authorizeTenant($hostelFloor);

        try {
            $validated = $request->validated();

            DB::transaction(function () use ($hostelFloor, $validated) {
                $hostelFloor->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Floor updated successfully.',
                'data' => $hostelFloor->load('hostel'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HostelFloor $hostelFloor)
    {
        $this->authorizeTenant($hostelFloor);

        try {
            if (method_exists($hostelFloor, 'rooms') && $hostelFloor->rooms()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this floor.',
                    'errors' => ['floor' => ['This floor has rooms assigned. Please remove them first.']],
                ], 422);
            }
            $hostelFloor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Floor deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export()
    {
        return redirect()->route('receptionist.hostel-floors.index', ['export' => 'csv']);
    }
}
