<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RequireRole;
use App\Http\Middleware\RequirePlatformAdmin;
use App\Http\Middleware\RequireSubscription;
use App\Http\Middleware\EnforcePlanLimit;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RequireRole::class,
            'platform-admin' => RequirePlatformAdmin::class,
            'subscription' => RequireSubscription::class,
            'plan-limit' => EnforcePlanLimit::class,
        ]);
        $middleware->validateCsrfTokens(except: ['billing/razorpay/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
