<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductWithCommentResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user?->customer_id;

        $category_name = $request->query('category_name'); // for category wise filter
        $search_keyword = $request->query('keyword'); // for category wise filter

        $query = Product::forCatalog()->orderByDesc('product_id');

        if ($search_keyword) {
            $query->where(function ($q) use ($search_keyword) {
                $q->where('product_name', 'LIKE', "%{$search_keyword}%")
                    // ->orWhere('description', 'LIKE', "%{$search_keyword}%")
                    ->orWhere('health_benefit', 'LIKE', "%{$search_keyword}%");
                // category wise seach
                $q->orWhereHas('category', function ($subQuery) use ($search_keyword) {
                    $subQuery->where('categ_name', 'LIKE', "%{$search_keyword}%");
                });
                // brand wise seach
                // $q->orWhereHas('brand', function ($subQuery) use ($search_keyword) {
                //     $subQuery->where('brand_name', 'LIKE', "%{$search_keyword}%");
                // });
            });
        }

        if ($category_name) {
            $query->whereHas('category', fn ($category) => $category->where('categ_name', $category_name));
        }

        $products = $query->with(['featured', 'wishlist' => function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products)->map(function ($product) {
                $product->is_wishlisted = $product->wishlist->isNotEmpty();
                $product->is_featured = $product->featured ? true : false;

                return $product;
            }),
        ]);
    }

    public function show($id)
    {
        // $product = ProductWithCommentResource::collection(Product::where('product_id', $id)->get());
        // return response()->json($product);
        try {
            $id = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $error) {
            abort(404);
        }
        abort_unless(is_int($id) || (is_string($id) && ctype_digit($id)), 404);

        $product = Product::forCatalog()->with([
            'comments.replies.customer' => function ($query) {
                $query->select('customer_id', 'first_name', 'last_name', 'profile_pic');
            },
            'comments.customer' => function ($query) {
                $query->select('customer_id', 'first_name', 'last_name', 'profile_pic');
            },
        ])->where('product_id', $id)->get();

        if ($product->isEmpty()) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // return response()->json($product);
        return response()->json(ProductWithCommentResource::collection($product));
    }
}
