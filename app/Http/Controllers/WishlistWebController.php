<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistWebController extends Controller
{
    public function index()
    {
        // $wishlists = Customer::with(['wishlist' => fn($qry) => $qry->orderBy('created_at', 'desc')])->get();
        $customers = Customer::all();
        $products = Product::where('status', 1)->get();
        $wishlists = Wishlist::with('customer', 'product')->orderBy('created_at', 'desc')->get();
        // return $wishlists;
        return view('wishlist.wishlists', compact('wishlists', 'customers', 'products'));
    }
    public function get_wishist_byCustomer(Request $request)
    {
        $customer_id = $request->customer;
        $product_id = $request->product;
        // return [$customer_id, $product_id];
        $customers = Customer::all();
        $products = Product::where('status', 1)->get();

        if ($customer_id && !$product_id) {
            $wishlists = Customer::with(['wishlist' => fn($qry) => $qry->orderBy('created_at', 'asc')])->where('customer_id', $customer_id)->first();
            return view('wishlist.wishlistFilter', compact('wishlists', 'customers', 'products', 'customer_id', 'product_id'));
        }

        if (!$customer_id && $product_id) {
            $wishlists = Product::with('wishlist.customer')->find($product_id);
            // return $wishlists->product_name;
            return view('wishlist.customersByProduct', compact('wishlists', 'customers', 'products', 'product_id'));
        }

        if ($customer_id && $product_id) {
            $wishlists = Product::where('product_id', $product_id)
                ->whereHas('wishlist', function ($q) use ($customer_id) {
                    $q->where('user_id', $customer_id);
                })
                ->with([
                    'wishlist' => function ($q) use ($customer_id) {
                        $q->where('user_id', $customer_id)
                            ->orderBy('created_at', 'asc');
                    }
                ])
                ->first();
            return view('wishlist.customersByProduct', compact('wishlists', 'customer_id', 'customers', 'products', 'product_id'));
        }
    }
}
