<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Services\Install\EnvironmentManager $envManager */
        $envManager = app(\App\Services\Install\EnvironmentManager::class);

        if ($envManager->isInstalled()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The application has already been installed. To re-install, remove install.lock.',
                ], 403);
            }

            return redirect()->route('shop.home')->with('info', 'Application is already installed.');
        }

        return $next($request);
    }
}
