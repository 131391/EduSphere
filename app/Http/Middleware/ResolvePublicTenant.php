<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = $this->identifyBySubdomain($request);

        if ($school) {
            app()->instance('currentSchool', $school);
        }

        return $next($request);
    }

    protected function identifyBySubdomain(Request $request): ?School
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0] ?? null;

        if (!$subdomain || in_array($subdomain, ['www', 'admin', 'api', 'localhost'], true)) {
            return null;
        }

        return Cache::remember("public.school.subdomain.{$subdomain}", 300, function () use ($subdomain) {
            return School::where('subdomain', $subdomain)->first();
        });
    }
}
