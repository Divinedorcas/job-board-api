<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JobSeekerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || ($user->role !== 'job_seeker' && $user->role !== 'candidate' && $user->role !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Only registered candidates can apply for jobs or access the candidate dashboard.'
            ], 403);
        }

        return $next($request);
    }
}
