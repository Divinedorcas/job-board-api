<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Test connectivity
Route::get('/test', function () {
    return response()->json([
        'message' => 'Laravel is connected!'
    ]);
});

// 1. PUBLIC ROUTES (No authentication required)
Route::post('/register', [AuthController::class, 'userRegister']);
Route::post('/login', [AuthController::class, 'userLogin']);
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{id}', [JobController::class, 'showOneJob']);
Route::get('/company', [CompanyController::class, 'index']);
Route::apiResource('companies', CompanyController::class)->only(['index', 'show']);

// 2. PROTECTED COMMON ROUTES (Any authenticated user)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->load('company'));
    });
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'userLogOut']);
});

// 3. EMPLOYER ONLY ROUTES (Must be registered user with role === 'employer')
Route::middleware(['auth:sanctum', 'employer'])->group(function () {
    // Employer Dashboard
    Route::get('/employer/dashboard', [ApplicationController::class, 'employerDashboard']);

    // Job Management (Adding, Editing, Deleting jobs)
    Route::post('/createjob', [JobController::class, 'createJob']);
    Route::put('/jobs/{id}', [JobController::class, 'updateJob']);
    Route::delete('/jobs/{id}', [JobController::class, 'deleteJob']);

    // Company Management
    Route::post('/createcompany', [CompanyController::class, 'createCompany']);

    // Review / Update Candidate Applications
    Route::patch('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
});

// 4. CANDIDATE ONLY ROUTES (Must be registered user with role === 'job_seeker' or 'candidate')
Route::middleware(['auth:sanctum', 'candidate'])->group(function () {
    // Candidate Dashboard
    Route::get('/candidate/dashboard', [ApplicationController::class, 'candidateDashboard']);
    Route::get('/candidate/applications', [ApplicationController::class, 'candidateDashboard']);

    // Apply for a Job
    Route::post('/jobs/{id}/apply', [ApplicationController::class, 'apply']);
});
