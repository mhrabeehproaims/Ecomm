<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Events\newCustomerCreatedEvent;

class CustomersController extends Controller
{
    public function index() {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'nullable',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|unique:customers,phone',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'zip' => 'nullable',
            'password' => 'required|min:5',
            'status' => 'nullable',
            'profile_pic' => 'nullable',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $validated['status'] ?? 1;
        $user = Customer::create($validated);

        if ($request->filled('email')) {
            event(new newCustomerCreatedEvent($user));
            // OR
            // newCustomerCreatedEvent::dispatch($user);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function show()
    {
        $user = Auth::user();

        $customer = Customer::with('deliveryAddress')->findOrFail($user->customer_id);

        return response()->json([
            'status' => 'success',
            'data' => $customer
        ]);
    }
    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'nullable|string',
            'email'      => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')->ignore($user->customer_id, 'customer_id'),
            ],
            'phone'      => [
                'required',
                Rule::unique('customers', 'phone')->ignore($user->customer_id, 'customer_id'),
            ],
            'address'    => 'nullable|string',
            'city'       => 'nullable|string',
            'state'      => 'nullable|string',
            'country'    => 'nullable|string',
            'zip'        => 'nullable|string',
            'status'     => 'nullable|integer',
            'old_password'      => 'nullable',
            'new_password'      => 'required_with:old_password|min:5|confirmed',
            'new_password_confirmation' => 'nullable',
        ]);


        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['error' => 'Old password is incorrect'], 422);
            }

            $validated['password'] = Hash::make($request->new_password);
        }


        $validated['status'] = $validated['status'] ?? 1;

        // Remove fields not in DB
        unset($validated['old_password'], $validated['new_password'], $validated['new_password_confirmation']);

        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');
            $image_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('CustomerImage', $image_name, 'public');
            $validated['profile_pic'] = $image_name;
        }

        $customer = Customer::findOrFail($user->customer_id);
        $customer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'user updated successfully',
            'user' => $user->customer_id
        ]);
    }


    public function destroy(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete account'
            ], 500);
        }

        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Your account has been deleted successfully'
        ]);
    }
}
