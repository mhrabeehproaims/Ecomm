<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['status' => 'success', 'data' => Cart::with('product.primaryImage')->where('user_id', $request->user()->customer_id)->get()]);
    }

    private function product(Request $request): int
    {
        $request->validate(['product_id' => 'required|string|max:4096']);
        try {
            $id = decrypt($request->product_id);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['product_id' => 'Invalid product ID.']);
        }
        if (! is_numeric($id) || (int) $id < 1) {
            throw ValidationException::withMessages(['product_id' => 'Invalid product ID.']);
        }

        return (int) $id;
    }

    public function store(Request $request)
    {
        $id = $this->product($request);
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:99', 'options' => 'nullable|array|max:20']);
        $cart = DB::transaction(function () use ($request, $id, $data) {
            $customer = $request->user()->customer_id;
            app(CheckoutService::class)->lockCustomer($customer);
            $product = Product::where('product_id', $id)->lockForUpdate()->first();
            $existing = Cart::where('user_id', $customer)->where('product_id', $id)->orderBy('cart_id')->lockForUpdate()->get();
            if (! $product || (int) $product->status !== 1 || $existing->sum('quantity') + $data['quantity'] > min(99, $product->stock)) {
                throw ValidationException::withMessages(['quantity' => 'Insufficient available stock.']);
            }
            $cart = $existing->first() ?? new Cart(['user_id' => $customer, 'session_id' => 0, 'product_id' => $id, 'quantity' => 0]);
            $cart->quantity += $data['quantity'];
            $cart->price = Money::decimal(Money::minor($product->default_price_value));
            $cart->options = isset($data['options']) ? json_encode($data['options']) : null;
            $cart->save();

            return $cart;
        }, 5);

        return response()->json(['status' => 'success', 'data' => $cart], 201);
    }

    public function change_count(Request $request)
    {
        $id = $this->product($request);
        $data = $request->validate(['count' => 'required|integer|min:1|max:99']);
        $cart = DB::transaction(function () use ($request, $id, $data) {
            $customer = $request->user()->customer_id;
            app(CheckoutService::class)->lockCustomer($customer);
            $product = Product::where('product_id', $id)->lockForUpdate()->first();
            $entries = Cart::where('user_id', $customer)->where('product_id', $id)->orderBy('cart_id')->lockForUpdate()->get();
            $cart = $entries->first();
            abort_unless($cart, 404, 'Item not found in cart.');
            if (! $product || (int) $product->status !== 1 || min(99, $product->stock) < $data['count'] + $entries->sum('quantity') - $cart->quantity) {
                throw ValidationException::withMessages(['count' => 'Insufficient available stock.']);
            }
            $cart->quantity = $data['count'];
            $cart->save();

            return $cart;
        }, 5);

        return response()->json(['status' => 'success', 'message' => 'Cart updated successfully', 'data' => $cart]);
    }

    public function remove(Request $request)
    {
        $id = $this->product($request);
        DB::transaction(function () use ($request, $id) {
            app(CheckoutService::class)->lockCustomer($request->user()->customer_id);
            $deleted = Cart::where('user_id', $request->user()->customer_id)->where('product_id', $id)->delete();
            abort_unless($deleted, 404, 'Product not found in cart.');
        }, 5);

        return response()->json(['status' => 'success', 'message' => 'Product removed from cart successfully']);
    }
}
