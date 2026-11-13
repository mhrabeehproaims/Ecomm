<?php

// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (is_int($request->input('mobile'))) {
            $request->merge(['mobile' => (string) $request->input('mobile')]);
        }
        $credentials = $request->validate([
            'mobile' => ['required', 'string', 'regex:/^[+]?[0-9]{7,15}$/D'],
            'password' => 'required|string|max:128',
        ]);

        $user = Customer::disableCache()->where('phone', $credentials['mobile'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'response_code' => 401,
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ((int) $user->status !== 1) {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        $user->update(['last_seen_at' => now()]);
        // Create Sanctum token
        $user->tokens()->delete();
        $token = $user->createToken('auth_token', ['*'], now()->addHours(8))->plainTextToken;

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof Customer, 403, 'A customer account is required.');
        $token = $user->currentAccessToken();
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
