<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $sessionId = 0; //$request->session()->getId();

        $cartItems = Cart::with(['product.primaryImage'])->when($user->customer_id, function ($q) use ($user, $sessionId) {
            if ($user) {
                $q->where('user_id', $user->customer_id);
            } else {
                $q->where('session_id', $sessionId);
            }
        })->get();


        return response()->json([
            'status' => 'success',
            'data'    => $cartItems,
        ], 200);
    }

    public function store(Request $request)
    {

        $requestData = $request->all();
        if (isset($requestData['product_id'])) {
            try {
                $requestData['product_id'] = decrypt($requestData['product_id']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid product ID'], 400);
            }
        }

        validator($requestData, [
            'product_id' => 'required|exists:products,product_id',
            'quantity'   => 'required|integer|min:1',
            'options'    => 'nullable',
        ])->validate();

        $user = Auth::user();
        $sessionId = 0; //$request->session()->getId();
        $product_id = decrypt($request->product_id);

        $product = Product::findOrFail($product_id);

        $cartItem = Cart::firstOrNew([
            'user_id'    => $user->customer_id,
            'session_id' => $sessionId, //$user ? null : $sessionId,
            'product_id' => $product_id,
        ]);

        $cartItem->quantity = ($cartItem->exists ? $cartItem->quantity : 0) + $request->quantity;
        $cartItem->price    = $product->default_price_value;
        $cartItem->options  = $request->options ?? null;
        $cartItem->save();

        return response()->json([
            'status' => 'success',
            'data'    => $cartItem,
        ], 201);
    }

    public function remove(Request $request)
    {
        $user = Auth::user();
        $product_id = decrypt($request->product_id);

        $request->merge(['product_id' => $product_id]);

        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('carts', 'product_id')->where('user_id', $user->customer_id),
            ],
        ]);

        $deleted = Cart::where(['user_id' => $user->customer_id, 'product_id' => $product_id])->delete();

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product removed from cart successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Product not found in cart'
        ], 404);
    }

    public function change_count(Request $request)
    {
        $user = Auth::user();

        $count = $request->count ?? 1;
        $product_id = decrypt($request->product_id);

        $request->merge(['product_id' => $product_id]);

        // Get the cart item
        $item = Cart::where([
            'product_id' => $product_id,
            'user_id' => $user->customer_id
        ])->first();

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart',
            ], 404);
        }

        // Check product stock
        $product = Product::find($product_id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        // Validate stock availability
        if ($product->stock < $count) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock available',
                'available_stock' => $product->stock,
                'requested_quantity' => $count
            ], 400);
        }

        // Check if product is out of stock
        if ($product->stock <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is out of stock',
            ], 400);
        }

        // Update cart quantity
        $item->update(['quantity' => $count]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated successfully',
            'data' => $item,
            'available_stock' => $product->stock
        ], 200);
    }
}
