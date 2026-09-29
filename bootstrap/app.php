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
 $middleware->alias([
 'admin' => \App\Http\Middleware\AdminMiddleware::class,
 'customer' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
 ]);

 // UPI gateway posts the webhook server-to-server — no CSRF token possible.
 $middleware->validateCsrfTokens(except: [
 'api/upi/webhook',
 ]);
 })
 ->withExceptions(function (Exceptions $exceptions): void {
     $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
         if ($request->expectsJson() || $request->ajax()) {
             return response()->json([
                 'success' => false,
                 'message' => 'Your session expired or security token mismatch. Please refresh the page.',
             ], 419);
         }

         return redirect()->back()
             ->withInput($request->except('_token', 'password', 'password_confirmation'))
             ->with('error', 'Your session expired or security token timed out. Please try again.');
     });
 })->create();