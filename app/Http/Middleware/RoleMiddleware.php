<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized - Not authenticated');
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if ($user->hasAccessRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized - Insufficient permissions for role: ' . implode(', ', $roles));
    }
}
