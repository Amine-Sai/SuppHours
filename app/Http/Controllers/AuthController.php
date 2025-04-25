<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller

{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response([
                'message' => 'Credentials are incorrect',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function createUser(Request $request)
    {
        $data = $request->validate([
            'fullName' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        //$token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'user' => $user,
            //'token' => $token,
        ], 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete(); // Delete all tokens

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
            'data' => []
        ], 200);
    }
}
