<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Receptionist routes are moved to web.php for consistency
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Don't apply tenant middleware globally - only on specific routes
        // $middleware->web(append: [
        //     \App\Http\Middleware\TenantMiddleware::class,
        // ]);
        
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'school.access' => \App\Http\Middleware\SchoolAccessMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

