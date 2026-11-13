<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        return view('Admin.profile');
    }
    public function dashboard()
    {
        $total_orders = Order::count();
        $total_return = SalesReturn::count();
        $last_five_orders = Order::with('customer')->latest()->take(5)->get();
        return view('index', compact('total_orders', 'total_return', 'last_five_orders'));
        // return $last_five_orders;
    }
    public function profile_update(Request $request)
    {

        $user = Auth::user();

        $validated = $request->validate([
            'user_name' => 'required|min:3|string',
            'user_email' => 'required|email',
            'oldPassword' => 'nullable|string',
            'newPassword' => 'nullable|string',
        ]);

        $data = [
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
        ];

        if (!empty($validated['newPassword']) && !empty($validated['oldPassword'])) {
            if (Hash::check($validated['oldPassword'], $user->password)) {
                $data['password'] = Hash::make($validated['newPassword']);
            } else {
                return back()->withErrors(['oldPassword' => 'Old password is incorrect']);
            }
        }
        $user->update($data);

        return redirect()->route('admin.profile')->with('message', 'Profile Updated!');
    }
}
