<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantService
{
    /**
     * Get current school
     */
    public function getCurrentSchool(): ?School
    {
        return app('currentSchool');
    }

    /**
     * Set current school
     */
    public function setCurrentSchool(School $school): void
    {
        app()->instance('currentSchool', $school);
    }

    /**
     * Switch database connection for school (if using separate databases)
     */
    public function switchDatabase(School $school): void
    {
        $databaseName = config('tenant.database_prefix', 'edusphere_') . $school->code;
        
        config(['database.connections.tenant.database' => $databaseName]);
        DB::purge('tenant');
    }

    /**
     * Clear school cache
     */
    public function clearSchoolCache(School $school): void
    {
        Cache::forget("school.subdomain.{$school->subdomain}");
        Cache::forget("school.domain.{$school->domain}");
        Cache::forget("school.path.{$school->code}");
        Cache::forget("school.id.{$school->id}");
    }

    /**
     * Get school by identifier
     */
    public function getSchoolByIdentifier(string $identifier, string $type = 'subdomain'): ?School
    {
        return Cache::remember("school.{$type}.{$identifier}", 3600, function () use ($identifier, $type) {
            return match ($type) {
                'subdomain' => School::where('subdomain', $identifier)->first(),
                'domain' => School::where('domain', $identifier)->first(),
                'code' => School::where('code', $identifier)->first(),
                'id' => School::find($identifier),
                default => null,
            };
        });
    }
}

