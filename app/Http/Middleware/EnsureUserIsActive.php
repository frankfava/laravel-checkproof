<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the route is protected by `auth:api`
        $routeMiddleware = $request->route()?->middleware() ?? [];

        if (! in_array('auth:api', $routeMiddleware)) {
            return $next($request);
        }

        // Check if the user is authenticated and active
        $user = $request->user();

        if (! $user || ! $user->active) {
            throw new AuthorizationException('Your account is inactive. Please contact support.');
        }

        return $next($request);
    }
}
