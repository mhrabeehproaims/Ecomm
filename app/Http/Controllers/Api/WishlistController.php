<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Nette\Utils\Strings;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user(); // or Auth::user()

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        // Get wishlist items for this user
        $wishlist_items = Wishlist::where('user_id', $user->customer_id)->pluck('product_id');

        $products = Product::whereIn('product_id', $wishlist_items)->get();

        return response()->json([
            'status' => 'success',
            'data'   => ProductResource::collection($products),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $productId = decrypt($request->product_id);
        $request->merge(['product_id' => $productId]);

        $request->validate([
            'product_id' => 'required|exists:products,product_id',
        ]);

        $user = $request->user();

        $exists = Wishlist::where('user_id', $user->customer_id)
            ->where('product_id', $productId)
            ->first();

        if ($exists) {
            $exists->delete();
            return response()->json([
                'status' => true,
                'message' => 'Removed'
            ], 200);
        }

        Wishlist::create([
            'user_id' => $user->customer_id,
            'product_id' => $productId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product added to wishlist'
        ]);
    }

    /**
     * Display the specified resource.
     */





    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
