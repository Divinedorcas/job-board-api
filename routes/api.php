<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;


Route::apiResource('companies', CompanyController::class);



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//puplic access
Route::post('/register', [AuthController::class, 'userRegister']);
Route::post('/login', [AuthController::class, 'userLogin']);

//protected access
Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
        return response()->json($request->user());
    });
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'userLogOut']);
    Route::post('/createjob', [JobController::class, 'createJob']);
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{id}', [JobController::class, 'showOneJob']);
    Route::put('/jobs/{id}', [JobController::class, 'updateJob']);
    Route::delete('/jobs/{id}', [JobController::class, 'deleteJob']);
    route::post('/createcompany', [CompanyController::class, 'createCompany']);
});


//Route::prefix('auth')->group(function (){})
