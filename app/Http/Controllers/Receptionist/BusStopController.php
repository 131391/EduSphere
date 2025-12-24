<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\BusStop;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusStopController extends TenantController
{
    public function index()
    {
        $schoolId = $this->getSchoolId();
        $busStops = BusStop::where('school_id', $schoolId)
            ->with(['route', 'vehicle'])
            ->latest()
            ->paginate(15);
        
        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();

        return view('receptionist.bus-stops.index', compact('busStops', 'routes', 'vehicles'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $validated = $request->validate([
            'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'bus_stop_no' => ['required', 'string', 'max:255'],
            'bus_stop_name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_from_institute' => ['nullable', 'numeric', 'min:0'],
            'charge_per_month' => ['nullable', 'numeric', 'min:0'],
            'area_pin_code' => ['nullable', 'string', 'max:10'],
        ]);

        $validated['school_id'] = $schoolId;

        BusStop::create($validated);

        return back()->with('success', 'Bus stop created successfully.');
    }

    public function update(Request $request, BusStop $busStop)
    {
        $schoolId = $this->getSchoolId();
        
        $validated = $request->validate([
            'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'bus_stop_no' => ['required', 'string', 'max:255'],
            'bus_stop_name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_from_institute' => ['nullable', 'numeric', 'min:0'],
            'charge_per_month' => ['nullable', 'numeric', 'min:0'],
            'area_pin_code' => ['nullable', 'string', 'max:10'],
        ]);

        $validated['school_id'] = $schoolId;

        $busStop->update($validated);

        return back()->with('success', 'Bus stop updated successfully.');
    }

    public function destroy(BusStop $busStop)
    {
        $busStop->delete();

        return back()->with('success', 'Bus stop deleted successfully.');
    }
}
