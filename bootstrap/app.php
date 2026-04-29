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
        
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'public.tenant' => \App\Http\Middleware\ResolvePublicTenant::class,
            'school.access' => \App\Http\Middleware\SchoolAccessMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle duplicate entry DB errors globally for JSON requests
        $exceptions->render(function (
            \Illuminate\Database\UniqueConstraintViolationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This record already exists. Please check for duplicates.',
                ], 422);
            }
        });

        // Handle generic query exceptions (FK violations, etc.) for JSON requests
        $exceptions->render(function (
            \Illuminate\Database\QueryException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                $code = $e->errorInfo[1] ?? 0;
                $message = match ((int) $code) {
                    1062 => 'This record already exists. Please check for duplicates.',
                    1451, 1452 => 'This record is linked to other data and cannot be modified.',
                    default => 'A database error occurred. Please try again.',
                };
                return response()->json(['success' => false, 'message' => $message], 422);
            }
        });
    })->create();
