<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
public function __construct(
    protected AuthService $authService
){}

    public function userRegister(RegisterUserRequest $request){
        $result = $this->authService->userRegister($request);
        return response()->json([
            'message' => 'User registered successfully',
            'token' => $result['token'],
            'user' => $result['user'],
        ], 201);
    }

    public function userLogin(LoginUserRequest $request){
        $result= $this->authService->userLogin($request);
         return response()->json([
                'message' => 'User logged in successfully', 
                'token' => $result['token'],
                'user' => $result['user'],
                'token_type' => 'Bearer',
            ]);
    }

     public function  userLogOut(Request $request){
        $user = $this->authService->userLogOut($request);
        return response()->json([
            'message' => 'User logged out successfully'
        ]);
    }


}

