<?php

namespace App\Traits;

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

            if (app()->runningInConsole()) {
                return;
            }

            // Important: do NOT call auth()->user() or auth()->id() here.
            // When the model being scoped is the User model itself,
            // resolving auth re-enters retrieveById() which re-enters this
            // scope, producing infinite recursion. We log and fall through.
            //
            // Legitimate tenant-less authenticated routes today:
            //  - the post-login /dashboard role dispatcher in routes/web.php
            //  - the super-admin portal in routes/admin.php
            // PRODUCTION_READINESS.md tracks binding currentSchool from
            // the user's school_id earlier in the request so this branch
            // is rarely reached and any remaining hit is genuinely a
            // misconfigured route.
            $route = request()->route();

            Log::info('Tenantable scope skipped: tenant context not bound', [
                'model' => get_class($builder->getModel()),
                'route' => $route ? $route->getName() : null,
                'url' => request()->fullUrl(),
            ]);
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
