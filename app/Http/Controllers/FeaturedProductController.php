<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FeaturedProduct;
use Illuminate\Http\Request;

class FeaturedProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::where('status', 1)->get();
        $featureds = FeaturedProduct::with('product')->get();

        return view('Featured_prodcuts.featured_prods', compact('categories', 'featureds'));
        // return $products;
    }

    public function store(Request $request)
    {
        $valid_data = $request->validate([
            'selected_products' => 'required|array',
            'selected_products.*' => 'distinct|exists:products,product_id|unique:featured_products,product_id',
            'status' => 'nullable|boolean',
        ]);

        $data = [];
        foreach ($valid_data['selected_products'] as $pid) {
            $data[] = FeaturedProduct::create([
                'product_id' => $pid,
                'status' => $valid_data['status'] ?? 1,
            ]);
        }

        $featured = FeaturedProduct::with('product.category', 'product.subCategory')
            ->whereIn('product_id', $valid_data['selected_products'])
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => $featured,
        ]);
    }

    /**
     * Display the specified resource.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $ids = $request->ids;

        FeaturedProduct::whereIn('featured_id', $ids)->delete();
        app(\App\Services\Storefront\CatalogInvalidation::class)->changed();

        return response()->json(['status' => 'success']);
    }
}
