<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;


class AuthService
{
   public function userRegister(RegisterUserRequest $request):User
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        return $user;
    }

public function userLogin(LoginUserRequest $request)
{
        $credentials = $request->validated();
    
    $user = User::where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'message' => 'Invalid credentials.'
        ], 401);
    }
            $token = $user->createToken('auth_token')->plainTextToken;
 return [
        'user' => $user,
        'token' => $token,
    ];
    }

public function  userLogOut(Request $request)
{
     $user = $request->user();       
        $user->currentAccessToken()->delete();
       
        return 'message';
            
    }

}