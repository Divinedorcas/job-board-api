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
        $user = $this->authService->userRegister($request);
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function userLogin(LoginUserRequest $request){
        $result= $this->authService->userLogin($request);
         return response()->json([
                'message' => 'User logged in successfully', 
                'access_token' => $result['token'],
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

