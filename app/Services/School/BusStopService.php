<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\BusStop;
use Illuminate\Support\Facades\DB;

class BusStopService
{
    /**
     * Create a new bus stop.
     *
     * @param School $school
     * @param array $data
     * @return BusStop
     */
    public function createBusStop(School $school, array $data): BusStop
    {
        return DB::transaction(function () use ($school, $data) {
            $busStop = new BusStop($data);
            $busStop->school_id = $school->id;
            $busStop->save();

            return $busStop;
        });
    }

    /**
     * Update an existing bus stop.
     *
     * @param BusStop $busStop
     * @param array $data
     * @return BusStop
     */
    public function updateBusStop(BusStop $busStop, array $data): BusStop
    {
        return DB::transaction(function () use ($busStop, $data) {
            $busStop->update($data);
            return $busStop->fresh();
        });
    }

    /**
     * Delete a bus stop.
     *
     * @param BusStop $busStop
     * @return bool|null
     * @throws \Exception
     */
    public function deleteBusStop(BusStop $busStop): ?bool
    {
        if (DB::table('student_transport_assignments')->where('bus_stop_id', $busStop->id)->where('status', \App\Enums\GeneralStatus::Active)->exists()) {
            throw new \Exception('Cannot delete bus stop. It has active student assignments.');
        }

        return $busStop->delete();
    }
}
