<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocationService;
use Exception;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Get all countries
     */
    public function getCountries()
    {
        try {
            $countries = $this->locationService->getCountries();
            
            return response()->json([
                'success' => true,
                'data' => $countries
            ]);
        } catch (Exception $e) {
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
            $states = $this->locationService->getStates($countryId);
            
            return response()->json([
                'success' => true,
                'data' => $states
            ]);
        } catch (Exception $e) {
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
            $cities = $this->locationService->getCities($stateId);
            
            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cities: ' . $e->getMessage()
            ], 500);
        }
    }
}
