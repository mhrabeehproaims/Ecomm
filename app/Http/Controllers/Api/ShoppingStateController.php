<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ShoppingStateController extends Controller
{
    public function __invoke(Request $request, CartController $cart, WishlistController $wishlist)
    {
        // Reuse the existing read contracts; do not recalculate or write cart prices.
        return response()->json([
            'status' => 'success',
            'customer' => $request->user()->loadMissing('deliveryAddress'),
            'cart' => $cart->index($request)->getData(true)['data'],
            'wishlist' => $wishlist->index($request)->getData(true)['data'],
        ])->header('Cache-Control', 'private, no-store')->header('Vary', 'Authorization');
    }
}
