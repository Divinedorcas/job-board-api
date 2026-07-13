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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

    if ($request->user()->role !== 'job_seeker') {
    return response()->json([
        'message' => 'Only job seekers can apply.'
    ], 403);
}
        return $next($request);
    }
}
