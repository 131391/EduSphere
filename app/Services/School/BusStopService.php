<?php

namespace App\Services\School;

use App\Models\BusStop;
use App\Models\School;
use App\Models\StudentTransportAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusStopService
{
    public function __construct(
        protected TransportIntegrityService $transportIntegrityService
    ) {
    }

    public function createBusStop(School $school, array $data): BusStop
    {
        return DB::transaction(function () use ($school, $data) {
            $normalized = $this->normalizeBusStopData($school, $data);

            $busStop = new BusStop($normalized);
            $busStop->school_id = $school->id;
            $busStop->save();

            return $busStop;
        });
    }

    public function updateBusStop(BusStop $busStop, array $data): BusStop
    {
        return DB::transaction(function () use ($busStop, $data) {
            $normalized = $this->normalizeBusStopData($busStop->school, $data, $busStop);

            if (
                (int) $busStop->route_id !== (int) $normalized['route_id']
                && StudentTransportAssignment::withTrashed()->where('bus_stop_id', $busStop->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'route_id' => ['This bus stop already has transport assignment history. Create a new stop instead of moving it to a different route.'],
                ]);
            }

            $busStop->update($normalized);

            return $busStop->fresh();
        });
    }

    public function deleteBusStop(BusStop $busStop): ?bool
    {
        if (StudentTransportAssignment::withTrashed()->where('bus_stop_id', $busStop->id)->exists()) {
            throw ValidationException::withMessages([
                'bus_stop' => ['Cannot delete this bus stop because transport assignment history exists for it.'],
            ]);
        }

        return $busStop->delete();
    }

    private function normalizeBusStopData(School $school, array $data, ?BusStop $busStop = null): array
    {
        $route = $this->transportIntegrityService->getRouteForSchool($school, (int) $data['route_id'], true);

        if (!$route->vehicle_id) {
            throw ValidationException::withMessages([
                'route_id' => ['Assign a vehicle to this route before creating a bus stop for it.'],
            ]);
        }

        $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, (int) $route->vehicle_id, true);

        if (
            array_key_exists('vehicle_id', $data)
            && filled($data['vehicle_id'])
            && (int) $data['vehicle_id'] !== (int) $vehicle->id
        ) {
            throw ValidationException::withMessages([
                'vehicle_id' => ['The selected vehicle does not match the vehicle assigned to the selected route.'],
            ]);
        }

        $query = BusStop::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('bus_stop_no', $data['bus_stop_no']);

        if ($busStop) {
            $query->where('id', '!=', $busStop->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'bus_stop_no' => ['A bus stop with this stop number already exists in this school.'],
            ]);
        }

        $data['vehicle_id'] = $vehicle->id;

        return $data;
    }
}
