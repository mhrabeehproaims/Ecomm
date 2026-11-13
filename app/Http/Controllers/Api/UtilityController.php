<?php

namespace App\Http\Controllers\Api;

use App\Events\NewEnquiryEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UtilityController extends Controller
{
    public function getProductsByCategory($categ_id)
    {
        $categ_id = decrypt($categ_id);
        $prods = Product::where('category_id', $categ_id)->get();
        $products = ProductResource::collection($prods);
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
    public function getProductsBySubCategory($sub_categ_id)
    {
        $prods = Product::where('sub_category_id', $sub_categ_id)->get();
        $products = ProductResource::collection($prods);
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
    public function getProductsByBrand($brand_id)
    {
        $prods = Product::where('brand_id', $brand_id)->get();
        $products = ProductResource::collection($prods);
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
    public function get_random_products()
    {
        $user = Auth::user();

        $query = Product::inRandomOrder()
            ->with(['wishlist' => function ($q) use ($user) {
                if ($user) {
                    $q->where('user_id', $user->customer_id);
                }
            }])
            ->limit(10);

        $randomProducts = $query->get();

        // Map and append `is_wishlisted`
        $products = $randomProducts->map(function ($product) use ($user) {
            $product->is_wishlisted = $user && $product->wishlist && $product->wishlist->isNotEmpty();
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products)
        ]);
    }

    public function get_all_brands()
    {
        $brands = Brand::where('status', 1)->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $brands,
            'image_path' => 'https://vantara.datacubeglobal.com/storage/BrandImage/',
        ]);
    }

    public function top_selling_products()
    {
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(10)
            ->with(['product' => function ($query) {
                $query->with('images'); // eager load product images
            }])
            ->get();

        $topProducts->each(function ($item) {
            if ($item->product) {
                $item->product;
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $topProducts,
        ], 200);
    }


    public function getAllPolicies()
    {
        $Policies = ReturnPolicy::active()->get();

        return response()->json([
            'status' => 'success',
            'data' => $Policies,
        ], 200);
    }

    public function validate_coupon(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'coupon_code' => 'required',
            'total_amount' => 'required'
        ]);
        $couponCode = $request->input('coupon_code');
        $total_amount = $request->input('total_amount');
        $today = now();
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if (!$coupon) {
                return response()->json(['status' => 'error', 'message' => 'Invalid coupon code'], 400);
            }

            if ($coupon->is_active !== 1) {
                return response()->json(['status' => 'error', 'message' => 'This coupon is inactive'], 400);
            }

            if ($today->lt($coupon->start_date) || $today->gt($coupon->end_date)) {
                return response()->json(['status' => 'error', 'message' => 'This coupon has expired or not yet started'], 400);
            }

            $totalUsed = CouponUsage::where('coupon_id', $coupon->id)->count();

            if ($coupon->max_uses && $totalUsed >= $coupon->max_uses) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This coupon usage limit has been reached'
                ], 400);
            }

            // Check per-user limit
            $totalUsedByUser = CouponUsage::where('customer_id', $user->customer_id)
                ->where('coupon_id', $coupon->id)
                ->count();

            if ($coupon->max_uses_per_user && $totalUsedByUser >= $coupon->max_uses_per_user) {
                return response()->json(['status' => 'error', 'message' => 'You have already used this coupon'], 400);
            }

            if ($coupon->min_order_amount && $total_amount < $coupon->min_order_amount) {
                return response()->json(['status' => 'error', 'message' => "The minimum order amount is {$coupon->min_order_amount} for this coupon"], 400);
            }

            return response()->json(['status' => 'success', 'message' => "The coupon id valid"], 200);
        }
    }

    // Customer Enquiry (Get in touch form)
    public function sendEnquiryMail(Request $request)
    {
        $message_data = $request->validate(
            [
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:150',
                'message' => 'required|string|max:1000',
                'phone' => ['required', 'regex:/^\+?[0-9]{7,15}$/'],
            ],
            [
                'phone.regex' => 'The phone number must be valid and contain 7-15 digits, optionally starting with +.',
            ]
        );

        event(new NewEnquiryEvent($message_data));
        return response()->json([
            'status' => 'success',
            'message' => 'Email sent succesfully',
        ]);
    }
}
