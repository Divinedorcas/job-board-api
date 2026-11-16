<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EmployerMiddleware;
use App\Http\Middleware\JobSeekerMiddleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'employer' => EmployerMiddleware::class,
            'job_seeker' => JobSeekerMiddleware::class,
            'candidate' => JobSeekerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON for all /api/* routes even if Accept header is missing
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // 401 Unauthenticated
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in to continue.',
                ], 401);
            }
        });

        // 422 Validation Error
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 404 Not Found
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested endpoint or resource was not found.',
                ], 404);
            }
        });

        // 403 Access Denied / Forbidden
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden. You do not have permission to access this resource.',
                ], 403);
            }
        });

        // 400 Database / Query Errors
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $errorCode = $e->errorInfo[1] ?? null;
                $message = 'A database error occurred. Please verify your data and try again.';

                if ($errorCode === 1062) {
                    $message = 'A record with these details already exists (duplicate entry).';
                } elseif ($errorCode === 1451 || $errorCode === 1452) {
                    $message = 'Database relation constraint failed. Please verify referenced records.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 400);
            }
        });

        // 500 General Server Error
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'An unexpected server error occurred.',
                    'error' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], 500);
            }
        });
    })->create();
