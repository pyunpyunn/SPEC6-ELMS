<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'active') {
            if ($request->routeIs('employee.profile')) {
                return $next($request);
            }

            return redirect()
                ->route('employee.profile')
                ->with('warning', 'Your account is not approved yet. HR must activate your account before you can use ELMS modules.');
        }

        return $next($request);
    }
}
