<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mobile'    => 'required|numeric',
            'password' => 'required|string',
        ]);

        $user = Customer::where('phone', $credentials['mobile'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'response_code' => 401,
                'status'        => 'error',
                'message'       => 'Invalid credentials',
            ], 401);
        }

        $user->update(['last_seen_at' => now()]);
        // Create Sanctum token
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Login successful',
            'token'         => $token,
            'user'          => $user,
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
}
