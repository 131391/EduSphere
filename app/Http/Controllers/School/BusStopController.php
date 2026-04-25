<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreBusStopRequest;
use App\Http\Requests\School\UpdateBusStopRequest;
use App\Models\BusStop;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Services\School\BusStopService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class BusStopController extends TenantController
{
    use HasAjaxDataTable;

    protected BusStopService $busStopService;

    public function __construct(BusStopService $busStopService)
    {
        parent::__construct();
        $this->busStopService = $busStopService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($busStop) {
            return [
                'id' => $busStop->id,
                'bus_stop_no' => $busStop->bus_stop_no,
                'bus_stop_name' => $busStop->bus_stop_name,
                'route_name' => $busStop->route?->route_name,
                'vehicle_no' => $busStop->vehicle?->vehicle_no,
                'distance_from_institute' => $busStop->distance_from_institute,
                'charge_per_month' => $busStop->charge_per_month,
                'created_at' => $busStop->created_at?->format('M d, Y'),
            ];
        };

        $query = BusStop::with(['route', 'vehicle'])->where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('bus_stop_no', 'like', '%' . $search . '%')
                  ->orWhere('bus_stop_name', 'like', '%' . $search . '%')
                  ->orWhereHas('route', function($rq) use ($search) {
                      $rq->where('route_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_no', 'like', '%' . $search . '%');
                  });
            });
        }

        $sort = $request->input('sort', 'bus_stop_name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        
        $allowedSorts = ['id', 'bus_stop_no', 'bus_stop_name', 'distance_from_institute', 'charge_per_month', 'created_at'];
        if (\in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('bus_stop_name', 'asc');
        }

        if ($request->expectsJson() || $request->ajax() || $request->has('page') || $request->filled('filters')) {
            return $this->handleAjaxTable($query, $transformer, []);
        }

        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->where('is_active', true)->get();

        $initialData = $this->getHydrationData($query, $transformer, [
            'routes' => $routes,
            'vehicles' => $vehicles,
        ]);

        return view('school.transport.bus_stops', [
            'initialData' => $initialData,
            'routes' => $routes,
            'vehicles' => $vehicles,
            'title' => 'Bus Stops Management'
        ]);
    }

    public function store(StoreBusStopRequest $request)
    {
        try {
            $busStop = $this->busStopService->createBusStop(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus Stop "' . $busStop->bus_stop_name . '" created successfully!',
                    'data' => $busStop
                ]);
            }

            return $this->redirectWithSuccess(
                'school.bus_stops.index',
                'Bus Stop "' . $busStop->bus_stop_name . '" created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create bus stop: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create bus stop: ' . $e->getMessage());
        }
    }

    public function update(UpdateBusStopRequest $request, $id)
    {
        try {
            $busStop = BusStop::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $busStop = $this->busStopService->updateBusStop($busStop, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus Stop "' . $busStop->bus_stop_name . '" updated successfully!',
                    'data' => $busStop
                ]);
            }

            return $this->redirectWithSuccess(
                'school.bus_stops.index',
                'Bus Stop "' . $busStop->bus_stop_name . '" updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update bus stop: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update bus stop: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $busStop = BusStop::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $busStopName = $busStop->bus_stop_name;
            $this->busStopService->deleteBusStop($busStop);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus Stop "' . $busStopName . '" deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.bus_stops.index',
                'Bus Stop "' . $busStopName . '" deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete bus stop: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.bus_stops.index',
                'Failed to delete bus stop: ' . $e->getMessage()
            );
        }
    }
}
