<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('auth.login');
        }

        if (!in_array($request->user()->role, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
