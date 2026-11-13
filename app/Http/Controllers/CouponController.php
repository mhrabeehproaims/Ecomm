<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(10);
        return view('Coupons.index', compact('coupons'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:1',
        ]);

        Coupon::create($request->all());

        return response()->json(['message' => 'success', 'response' => 'Coupon created successfully']);
    }

    public function edit(Coupon $coupon)
    {
        return response()->json($coupon);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:1',
        ]);

        $coupon->update($request->all());

        return response()->json(['message' => 'success', 'response' => 'Coupon updated successfully']);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['status' => 'success', 'response' => 'Coupon deleted successfully']);
    }
}
