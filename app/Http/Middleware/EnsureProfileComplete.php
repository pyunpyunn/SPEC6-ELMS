<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status === 'active' && ! $user->employee) {
            abort(403, 'Please ask HR Admin to complete your employee profile.');
        }

        return $next($request);
    }
}
