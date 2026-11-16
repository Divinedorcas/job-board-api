<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || ($user->role !== 'employer' && $user->role !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Only registered employers can perform this action or access the employer dashboard.'
            ], 403);
        }

        return $next($request);
    }
}
