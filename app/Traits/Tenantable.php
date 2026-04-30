<?php

namespace App\Traits;

use App\Exceptions\TenantResolutionException;
use App\Models\School;
use Illuminate\Support\Facades\Log;

trait Tenantable
{
    /**
     * Boot the trait
     */
    public static function bootTenantable()
    {
        static::addGlobalScope('school', function ($builder) {
            if (app()->bound('currentSchool')) {
                $school = app('currentSchool');
                if ($school instanceof School) {
                    $builder->where($builder->getModel()->getTable() . '.school_id', $school->id);
                    return;
                }
            }

            // No tenant context. Allow well-known cross-tenant callers; fail
            // closed for everything else so a missing `tenant` middleware
            // can't quietly leak data across schools.
            if (app()->runningInConsole()) {
                return;
            }

            $user = auth()->user();

            if ($user === null) {
                // Pre-auth requests (login, password reset). Controllers in
                // this path are responsible for any tenant scoping they need.
                return;
            }

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return;
            }

            $modelClass = get_class($builder->getModel());
            $route = request()->route();

            Log::critical('Tenantable scope bypass blocked', [
                'model' => $modelClass,
                'user_id' => $user->getAuthIdentifier(),
                'route' => $route ? $route->getName() : null,
                'url' => request()->fullUrl(),
            ]);

            if (app()->environment('local', 'testing')) {
                throw new TenantResolutionException(
                    "No school context bound for query on {$modelClass}. "
                    . 'Ensure the route is wrapped in the "tenant" middleware.'
                );
            }

            // Production: fail closed. Returning zero rows is safer than
            // throwing for a public-facing request that may already be
            // rendering a partial response.
            $builder->whereRaw('1 = 0');
        });

        static::creating(function ($model) {
            if (app()->bound('currentSchool')) {
                $school = app('currentSchool');
                if ($school instanceof School && !$model->school_id) {
                    $model->school_id = $school->id;
                }
            }
        });
    }
}
