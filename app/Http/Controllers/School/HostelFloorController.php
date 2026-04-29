<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $query = HostelFloor::with('hostel')->where('school_id', $schoolId);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('floor_name', 'like', "%{$search}%")
                    ->orWhereHas('hostel', function ($hq) use ($search) {
                        $hq->where('hostel_name', 'like', "%{$search}%");
                    });
            });
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

        return view('school.hostel.floors', [
            'initialData' => $initialData,
            'stats' => $stats,
            'hostels' => $hostels,
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

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'floor_name' => 'required|string|max:255',
                'total_room' => 'nullable|integer|min:0',
                'floor_create_date' => 'nullable|date',
            ]);
            $validated['school_id'] = $this->getSchoolId();

            $floor = HostelFloor::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Floor added successfully.',
                'data' => $floor,
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
                'message' => 'Failed to add floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, HostelFloor $floor)
    {
        $this->authorizeTenant($floor);

        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'floor_name' => 'required|string|max:255',
                'total_room' => 'nullable|integer|min:0',
                'floor_create_date' => 'nullable|date',
            ]);

            $floor->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Floor updated successfully.',
                'data' => $floor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HostelFloor $floor)
    {
        $this->authorizeTenant($floor);

        try {
            if ($floor->rooms()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this floor.',
                    'errors' => ['floor' => ['This floor has rooms assigned. Please remove them first.']],
                ], 422);
            }

            $floor->delete();

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

    public function getByHostel($hostelId)
    {
        $floors = HostelFloor::where('school_id', $this->getSchoolId())
            ->where('hostel_id', $hostelId)
            ->orderBy('floor_name')
            ->get();

        return response()->json($floors);
    }

    public function export()
    {
        return redirect()->route('school.hostel.floors.index', ['export' => 'csv']);
    }
}
