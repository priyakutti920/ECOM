<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (file_exists(storage_path('installed'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The application has already been installed. To re-install, remove storage/installed.',
                ], 403);
            }

            return redirect()->route('shop.home')->with('info', 'Application is already installed.');
        }

        return $next($request);
    }
}
