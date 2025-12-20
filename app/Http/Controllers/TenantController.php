<?php

namespace App\Http\Controllers;

use App\Models\School;

/**
 * Tenant Controller
 * 
 * Base controller for all tenant-aware controllers
 * Automatically handles school context
 */
abstract class TenantController extends BaseController
{
    /**
     * Current school instance
     */
    protected ?School $school = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->school = app('currentSchool');

            if (!$this->school) {
                abort(404, 'School not found');
            }

            return $next($request);
        });
    }

    /**
     * Get current school
     */
    protected function getSchool(): School
    {
        return $this->school;
    }

    /**
     * Get school ID
     */
    protected function getSchoolId(): int
    {
        return $this->school->id;
    }

    /**
     * Check if school is active
     */
    protected function ensureSchoolActive(): void
    {
        if (!$this->school->isActive()) {
            abort(403, 'School is not active');
        }

        if (!$this->school->isSubscriptionActive()) {
            abort(403, 'School subscription has expired');
        }
    }

    /**
     * Apply school scope to query
     */
    protected function scopeToSchool($query)
    {
        return $query->where('school_id', $this->getSchoolId());
    }
}
