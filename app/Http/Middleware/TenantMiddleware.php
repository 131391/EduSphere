<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;
use Illuminate\Support\Facades\Cache;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school = $this->identifySchool($request);

        if (!$school) {
            abort(404, 'School not found');
        }


        if (!$school->isActive() || !$school->isSubscriptionActive()) {
            return response()->view('errors.subscription-inactive', ['school' => $school], 403);
        }

        // Bind school to service container
        app()->instance('currentSchool', $school);

        return $next($request);
    }

    /**
     * Identify school from request
     */
    protected function identifySchool(Request $request): ?School
    {
        $method = config('tenant.identification_method', 'domain');
        
        return match ($method) {
            'subdomain' => $this->identifyBySubdomain($request),
            'domain' => $this->identifyByDomain($request),
            'path' => $this->identifyByPath($request),
            'header' => $this->identifyByHeader($request),
            default => $this->identifyBySubdomain($request),
        };
    }

    /**
     * Identify school by subdomain
     */
    protected function identifyBySubdomain(Request $request): ?School
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        if ($subdomain === 'www' || $subdomain === 'admin' || $subdomain === 'api') {
            return null;
        }

        return Cache::remember("school.subdomain.{$subdomain}", 3600, function () use ($subdomain) {
            return School::where('subdomain', $subdomain)
                ->first();
        });
    }

    /**
     * Identify school by domain
     */
    protected function identifyByDomain(Request $request): ?School
    {
        $domain = $request->getHost();

        return Cache::remember("school.domain.{$domain}", 3600, function () use ($domain) {
            return School::where('domain', $domain)
                ->first();
        });
    }

    /**
     * Identify school by path
     */
    protected function identifyByPath(Request $request): ?School
    {
        $path = $request->segment(1);
        
        if (!$path) {
            return null;
        }

        return Cache::remember("school.path.{$path}", 3600, function () use ($path) {
            return School::where('code', $path)
                ->first();
        });
    }

    /**
     * Identify school by header
     */
    protected function identifyByHeader(Request $request): ?School
    {
        $schoolId = $request->header('X-School-ID');
        
        if (!$schoolId) {
            return null;
        }

        return Cache::remember("school.id.{$schoolId}", 3600, function () use ($schoolId) {
            return School::where('id', $schoolId)
                ->first();
        });
    }

}

