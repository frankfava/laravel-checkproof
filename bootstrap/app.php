<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;
use League\OAuth2\Server\Exception\OAuthServerException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // Web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Api Guest
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // API - User
            Route::middleware(['api', 'auth:api'])
                ->prefix('api')
                ->group(base_path('routes/api-auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'scopes' => CheckScopes::class,
            'scope' => CheckForAnyScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (OAuthServerException $e) {
            return response()->json([
                'error' => $e->getErrorType(),
                'message' => $e->getMessage(),
                'hint' => $e->getHint(),
            ], $e->getHttpStatusCode());
        });
    })->create();
