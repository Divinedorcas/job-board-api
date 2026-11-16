<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function userRegister(RegisterUserRequest $request)
    {
        $result = $this->authService->userRegister($request);
        return response()->json([
            'message' => 'User registered successfully',
            'token' => $result['token'],
            'user' => $result['user'],
            'token_type' => 'Bearer',
        ], 201);
    }

    public function userLogin(LoginUserRequest $request)
    {
        $result = $this->authService->userLogin($request);

        if (!$result || ($result instanceof JsonResponse)) {
            return $result instanceof JsonResponse
                ? $result
                : response()->json([
                    'message' => 'Invalid email or password. Please check your credentials and try again.'
                ], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'token' => $result['token'],
            'user' => $result['user'],
            'token_type' => 'Bearer',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('company')
        ]);
    }

    public function userLogOut(Request $request)
    {
        $this->authService->userLogOut($request);
        return response()->json([
            'message' => 'User logged out successfully'
        ]);
    }
}
