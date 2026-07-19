<?php

declare(strict_types=1);

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Role-based authorization middleware.
        //
        // `role:super_admin` accepts an explicit role. The named aliases
        // (super_admin / admin / operator) inject their role automatically so
        // routes can use `->middleware('admin')` without parameters; each
        // alias also grants every higher-ranked role (super_admin > admin >
        // operator).
        $roleMiddleware = new RoleMiddleware;

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'super_admin' => fn ($request, $next) => $roleMiddleware->handle($request, $next, 'super_admin'),
            'admin' => fn ($request, $next) => $roleMiddleware->handle($request, $next, 'admin'),
            'operator' => fn ($request, $next) => $roleMiddleware->handle($request, $next, 'operator'),
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
