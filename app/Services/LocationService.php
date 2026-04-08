<?php

namespace App\Services;

use Nnjeim\World\World;
use Exception;

class LocationService
{
    /**
     * Get all countries.
     *
     * @return array
     */
    public function getCountries()
    {
        try {
            $action = World::countries();
            return $action->success ? ($action->data ?? []) : [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get states by country ID.
     *
     * @param int $countryId
     * @return array
     */
    public function getStates($countryId)
    {
        try {
            $action = World::states([
                'filters' => [
                    'country_id' => $countryId,
                ],
            ]);
            return $action->success ? ($action->data ?? []) : [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get cities by state ID.
     *
     * @param int $stateId
     * @return array
     */
    public function getCities($stateId)
    {
        try {
            $action = World::cities([
                'filters' => [
                    'state_id' => $stateId,
                ],
            ]);
            return $action->success ? ($action->data ?? []) : [];
        } catch (Exception $e) {
            return [];
        }
    }
}
