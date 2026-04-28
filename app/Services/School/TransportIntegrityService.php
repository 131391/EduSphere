<?php

namespace App\Services\School;

use App\Enums\RouteStatus;
use App\Models\AcademicYear;
use App\Models\BusStop;
use App\Models\School;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

class TransportIntegrityService
{
    public function getVehicleForSchool(School $school, int $vehicleId, bool $requireActive = false): Vehicle
    {
        $vehicle = Vehicle::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->find($vehicleId);

        if (!$vehicle) {
            $this->fail([
                'vehicle_id' => ['The selected vehicle does not belong to this school.'],
            ]);
        }

        if ($requireActive && !$vehicle->is_active) {
            $this->fail([
                'vehicle_id' => ['The selected vehicle is inactive. Activate it before using it in transport operations.'],
            ]);
        }

        return $vehicle;
    }

    public function getRouteForSchool(School $school, int $routeId, bool $requireActive = false): TransportRoute
    {
        $route = TransportRoute::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->find($routeId);

        if (!$route) {
            $this->fail([
                'route_id' => ['The selected route does not belong to this school.'],
            ]);
        }

        if ($requireActive && $route->status !== RouteStatus::Active) {
            $this->fail([
                'route_id' => ['The selected route is inactive. Activate it before using it in transport operations.'],
            ]);
        }

        return $route;
    }

    public function getBusStopForSchool(School $school, int $busStopId): BusStop
    {
        $busStop = BusStop::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->find($busStopId);

        if (!$busStop) {
            $this->fail([
                'bus_stop_id' => ['The selected bus stop does not belong to this school.'],
            ]);
        }

        return $busStop;
    }

    public function getAcademicYearForSchool(
        School $school,
        ?int $academicYearId = null,
        bool $allowCurrentFallback = true
    ): AcademicYear {
        if ($academicYearId !== null) {
            $academicYear = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->find($academicYearId);

            if (!$academicYear) {
                $this->fail([
                    'academic_year_id' => ['The selected academic year does not belong to this school.'],
                ]);
            }

            return $academicYear;
        }

        if ($allowCurrentFallback) {
            $academicYear = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('is_current', true)
                ->first()
                ?: AcademicYear::withoutGlobalScopes()
                    ->where('school_id', $school->id)
                    ->latest('start_date')
                    ->first();

            if ($academicYear) {
                return $academicYear;
            }
        }

        $this->fail([
            'academic_year_id' => ['No academic year is available for this school.'],
        ]);
    }

    public function resolveRouteBusStopVehicle(
        School $school,
        int $routeId,
        int $busStopId,
        ?int $requestedVehicleId = null,
        bool $requireActiveRoute = true,
        bool $requireActiveVehicle = true
    ): array {
        $route = $this->getRouteForSchool($school, $routeId, $requireActiveRoute);
        $busStop = $this->getBusStopForSchool($school, $busStopId);

        if ((int) $busStop->route_id !== (int) $route->id) {
            $this->fail([
                'bus_stop_id' => ['The selected bus stop does not belong to the selected route.'],
            ]);
        }

        if (!$route->vehicle_id) {
            $this->fail([
                'route_id' => ['Assign a vehicle to this route before using it in transport operations.'],
            ]);
        }

        $vehicle = $this->getVehicleForSchool($school, (int) $route->vehicle_id, $requireActiveVehicle);

        if ($requestedVehicleId !== null && $requestedVehicleId !== (int) $vehicle->id) {
            $this->fail([
                'vehicle_id' => ['The selected vehicle does not match the vehicle assigned to the selected route.'],
            ]);
        }

        if ($busStop->vehicle_id !== null && (int) $busStop->vehicle_id !== (int) $vehicle->id) {
            $this->fail([
                'bus_stop_id' => ['The selected bus stop is out of sync with its route vehicle. Update the bus stop before continuing.'],
            ]);
        }

        return [
            'route' => $route,
            'busStop' => $busStop,
            'vehicle' => $vehicle,
        ];
    }

    private function fail(array $messages): void
    {
        throw ValidationException::withMessages($messages);
    }
}
