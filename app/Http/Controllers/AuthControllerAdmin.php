<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthControllerAdmin extends Controller
{

    public function index()
    {
        return view('Auth.login');
    }

    public function validate(Request $request)
    {
        $request->validate([
            'username' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        $remember_me = $request->input('remember-check') === "on";

        $login_cred = [
            'email' => $request->input('username'),
            'password' => $request->input('password')
        ];

        if (Auth::attempt($login_cred, $remember_me)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        } else {
            return redirect()->route('login.index')->with(['title' => 'Login Failed', 'message' => 'Invalid Credentials']);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->update(['remember_token' => null]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index')->with('success', 'Logged out successfully.');
    }
}
