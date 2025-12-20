<?php

namespace App\Traits;

use App\Models\School;

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
                    $builder->where('school_id', $school->id);
                }
            }
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

