<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CheckoutEvent;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class CheckoutEventController extends Controller
{
    public function index()
    {
        // $carts = Customer::with(['wishlist' => fn($qry) => $qry->orderBy('created_at', 'desc')])->get();
        $customers = Customer::all();
        $products = Product::where('status', 1)->get();
        $cart_products = Cart::with('customer', 'product')->orderBy('created_at', 'desc')->get();
        return view('Cart.carts', compact('cart_products', 'customers', 'products'));
    }

    public function checkoutClick(Request $request)
    {
        $sessionId = $request->input('session_id');

        $user = $request->user();

        CheckoutEvent::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'customer_id' => $user ? $user->customer_id : null,
                'cart_id' => $request->input('cart_id'),
                'clicked_at' => now(),
            ]
        );

        return response()->json(['status' => true]);
    }

    public function get_carts_byCustomer(Request $request)
    {
        $customer_id = $request->customer;
        $product_id = $request->product;
        // return [$customer_id, $product_id];
        $customers = Customer::all();
        $products = Product::where('status', 1)->get();

        if ($customer_id && !$product_id) {
            $carts = Customer::with(['carts' => fn($qry) => $qry->orderBy('created_at', 'asc')])->where('customer_id', $customer_id)->first();
            // return $carts;
            return view('Cart.cartFilter', compact('carts', 'customers', 'products', 'customer_id', 'product_id'));
        }

        if (!$customer_id && $product_id) {
            $carts = Product::with('carts.customer')->find($product_id);
            // return $carts->product_name;
            return view('Cart.customersByProduct', compact('carts', 'customers', 'products', 'product_id'));
        }

        if ($customer_id && $product_id) {
            $carts = Product::where('product_id', $product_id)
                ->whereHas('carts', function ($q) use ($customer_id) {
                    $q->where('user_id', $customer_id);
                })
                ->with([
                    'carts' => function ($q) use ($customer_id) {
                        $q->where('user_id', $customer_id)
                            ->orderBy('created_at', 'asc');
                    }
                ])
                ->first();
            return view('Cart.customersByProduct', compact('carts', 'customer_id', 'customers', 'products', 'product_id'));
        }
    }
    public function show($id)
    {
        $checkoutEvent = CheckoutEvent::with([
            'customer',
            'customer.carts.product',
            'cart.customer'
        ])->find($id);

        if (!$checkoutEvent) {
            return response()->json(['message' => 'Checkout event not found'], 404);
        }

        // Build product list with quantity
        $products = $checkoutEvent->customer->carts->map(function ($cart) {
            return [
                'name' => $cart->product->product_name ?? null,
                'qty'  => $cart->quantity ?? 1,
                'unit'  => $cart->product->unit->unit_name ?? null,
            ];
        })->filter(fn($p) => $p['name'])->values()->toArray();

        $responseData = [
            'id'                => $checkoutEvent->id,
            'session_id'        => $checkoutEvent->session_id,
            'clicked_at'        => $checkoutEvent->clicked_at->format('d-m-Y H:i A'),
            'customer_name'     => $checkoutEvent->customer->full_name ?? 'N/A',
            'cart_customer_name' => $checkoutEvent->cart->customer->full_name ?? 'N/A',
            'products'          => $products,
        ];

        return response()->json($responseData);
    }
}
