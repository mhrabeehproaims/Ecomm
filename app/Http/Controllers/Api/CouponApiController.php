<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    public function getAllCoupons()
    {
        $coupons = Coupon::where('is_active', 1)->get();

        return response()->json([
            'status' => 'success',
            'coupons' => $coupons
        ]);
    }
}
