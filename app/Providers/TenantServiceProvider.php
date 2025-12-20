<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TenantService;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService();
        });

        $this->app->alias(TenantService::class, 'tenant');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

