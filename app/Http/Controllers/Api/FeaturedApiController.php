<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\FeaturedProduct;
use Illuminate\Support\Facades\Auth;

class FeaturedApiController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // store once

        $featureds = FeaturedProduct::whereHas('product', fn ($q) => $q->active())->with([
            'product' => fn ($q) => $q->forCatalog(),
            'wishlist' => function ($query) use ($user) {
                // Only apply filter if user is logged in
                if ($user) {
                    $query->where('user_id', $user->customer_id);
                }
            },
        ])->get();

        $products = $featureds->pluck('product')->filter()->map(function ($product) use ($user) {
            // If logged in, check wishlist relation
            $product->is_wishlisted = $user ? $product->wishlist->isNotEmpty() : false;
            $product->is_featured = true;

            return $product;
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
        ]);
    }
}
