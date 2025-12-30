<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nnjeim\World\World;

class LocationController extends Controller
{
    /**
     * Get all countries
     */
    public function getCountries()
    {
        try {
            $action = World::countries();
            
            if ($action->success) {
                return response()->json([
                    'success' => true,
                    'data' => $action->data
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch countries'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching countries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get states by country ID
     */
    public function getStates($countryId)
    {
        try {
            $action = World::states([
                'filters' => [
                    'country_id' => $countryId
                ]
            ]);
            
            if ($action->success) {
                return response()->json([
                    'success' => true,
                    'data' => $action->data ?? []
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch states'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching states: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cities by state ID
     */
    public function getCities($stateId)
    {
        try {
            $action = World::cities([
                'filters' => [
                    'state_id' => $stateId
                ]
            ]);
            
            if ($action->success) {
                return response()->json([
                    'success' => true,
                    'data' => $action->data ?? []
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cities'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cities: ' . $e->getMessage()
            ], 500);
        }
    }
}
