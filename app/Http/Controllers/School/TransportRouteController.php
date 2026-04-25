<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreTransportRouteRequest;
use App\Http\Requests\School\UpdateTransportRouteRequest;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Services\School\TransportRouteService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class TransportRouteController extends TenantController
{
    use HasAjaxDataTable;

    protected TransportRouteService $routeService;

    public function __construct(TransportRouteService $routeService)
    {
        parent::__construct();
        $this->routeService = $routeService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($route) {
            return [
                'id' => $route->id,
                'route_name' => $route->route_name,
                'vehicle_no' => $route->vehicle?->vehicle_no,
                'route_create_date' => $route->route_create_date?->format('Y-m-d'),
                'status_label' => $route->getStatusLabel(),
                'status' => $route->status->value,
                'created_at' => $route->created_at?->format('M d, Y'),
            ];
        };

        $query = TransportRoute::with('vehicle')->where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('route_name', 'like', '%' . $search . '%')
                  ->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_no', 'like', '%' . $search . '%');
                  });
            });
        }

        $sort = $request->input('sort', 'route_name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        
        $allowedSorts = ['id', 'route_name', 'route_create_date', 'created_at'];
        if (\in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('route_name', 'asc');
        }

        if ($request->expectsJson() || $request->ajax() || $request->has('page') || $request->filled('filters')) {
            return $this->handleAjaxTable($query, $transformer, []);
        }

        $vehicles = Vehicle::where('school_id', $schoolId)->where('is_active', true)->get();

        $initialData = $this->getHydrationData($query, $transformer, [
            'vehicles' => $vehicles,
            'statusLabels' => TransportRoute::getStatusLabels(),
        ]);

        return view('school.transport.routes', [
            'initialData' => $initialData,
            'vehicles' => $vehicles,
            'title' => 'Transport Routes Management'
        ]);
    }

    public function store(StoreTransportRouteRequest $request)
    {
        try {
            $route = $this->routeService->createRoute(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Route "' . $route->route_name . '" created successfully!',
                    'data' => $route
                ]);
            }

            return $this->redirectWithSuccess(
                'school.transport_routes.index',
                'Route "' . $route->route_name . '" created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create route: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create route: ' . $e->getMessage());
        }
    }

    public function update(UpdateTransportRouteRequest $request, $id)
    {
        try {
            $route = TransportRoute::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $route = $this->routeService->updateRoute($route, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Route "' . $route->route_name . '" updated successfully!',
                    'data' => $route
                ]);
            }

            return $this->redirectWithSuccess(
                'school.transport_routes.index',
                'Route "' . $route->route_name . '" updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update route: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update route: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $route = TransportRoute::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $routeName = $route->route_name;
            $this->routeService->deleteRoute($route);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Route "' . $routeName . '" deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.transport_routes.index',
                'Route "' . $routeName . '" deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete route: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.transport_routes.index',
                'Failed to delete route: ' . $e->getMessage()
            );
        }
    }
}
