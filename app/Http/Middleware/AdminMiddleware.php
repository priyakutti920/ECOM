<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('admin.login');
        }

        if (!Auth::user()->is_admin) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthorized.'], 403)
                : redirect()->route('admin.login');
        }

        return $next($request);
    }
}
