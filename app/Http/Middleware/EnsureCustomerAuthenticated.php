<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAuthenticated
{
 /**
 * Handle an incoming request.
 */
 public function handle(Request $request, Closure $next): Response
 {
 if (!Auth::guard('customer')->check()) {
 return redirect()
 ->route('shop.login.email')
 ->with('info', 'Please login to continue.');
 }

 return $next($request);
 }
}