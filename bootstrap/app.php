<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all upstream reverse proxies for SSL termination, correct IP and secure cookies
        $middleware->trustProxies(at: '*');

        // Prepend installer redirect so that missing .env files automatically redirect to /install
        $middleware->web(prepend: [
            \App\Http\Middleware\RedirectIfNotInstalled::class,
        ], append: [
            \App\Http\Middleware\CompressResponse::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'customer' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
            'installed' => \App\Http\Middleware\RedirectIfInstalled::class,
            'not_installed' => \App\Http\Middleware\RedirectIfNotInstalled::class,
        ]);

        // Payment and Courier gateways post webhooks server-to-server — no CSRF token possible.
        $middleware->validateCsrfTokens(except: [
            'api/upi/webhook',
            'payment/webhook',
            'api/payment/webhook',
            'api/payment/webhook/*',
            'api/courier/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $render419 = function (\Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session expired or security token mismatch. Please refresh the page.',
                ], 419);
            }

            if ($request->is('admin*')) {
                $target = ($request->is('admin/login') || !\Illuminate\Support\Facades\Auth::check())
                    ? route('admin.login')
                    : (url()->previous() ?: route('admin.login'));

                return redirect()->to($target)
                    ->withInput($request->except('_token', 'password', 'password_confirmation'))
                    ->with('error', 'Your session expired or security token timed out. Please try again.');
            }

            if ($request->is('login*') || $request->is('register*')) {
                return redirect()->route('shop.login.email')
                    ->withInput($request->except('_token', 'password', 'password_confirmation'))
                    ->with('error', 'Your session expired or security token timed out. Please try again.');
            }

            $prev = url()->previous();
            $target = ($prev && $prev !== $request->fullUrl() && parse_url($prev, PHP_URL_HOST) === $request->getHost())
                ? $prev
                : route('shop.home');

            return redirect()->to($target)
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Your session expired or security token timed out. Please try again.');
        };

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) use ($render419) {
            if ($e->getStatusCode() === 419) {
                return $render419($request);
            }
            return null;
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) use ($render419) {
            return $render419($request);
        });
    })->create();