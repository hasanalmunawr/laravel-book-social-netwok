<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if ($user) {
            // If the email exists, return an error response
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'The email is already registered.'
            ], 422));
        }

        $uuid = Str::uuid()->toString();

        // Concatenate the first name with the UUID to create the username
        $username = $data['first_name'] . '_' . $uuid;

        User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for a verification link.'
        ]);
    }


    public function login(LoginUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $userLogin = $request->validated();

        $user = User::where('email',$userLogin['email'])->first();
        if(!$user || !Hash::check($userLogin['password'],$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }

        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
        return response()->json([
            'access_token' => $token,
        ]);
    }

    public function logout(){
        $user = auth()->user();
        $user->tokens()->delete();


        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

}
