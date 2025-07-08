<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  public function handle(Request $request, Closure $next, $role): Response
{
    $user = Auth::guard('api')->user();

    // Pastikan perbandingan role tidak case-sensitive
    if (!$user || strtolower($user->role) !== strtolower($role)) {
        return response()->json([
            'message' => 'Unauthorized in ' . $role,
            'status_code' => 403,
            'data' => null
        ], 403);
    }

    return $next($request);
}


}
