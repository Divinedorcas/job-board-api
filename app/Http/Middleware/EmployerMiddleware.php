<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\AuthController;


class EmployerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next){

    if (!$request->user() || $request->user()->role !== 'employer') {
        return response()->json([
            'message' => 'Only employers can perform this action.'
        ], 403);
    }
        return $next($request);
    }
}

