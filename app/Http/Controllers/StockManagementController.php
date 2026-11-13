<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockManagement;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class StockManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory', 'brand']);

        // Optional Filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('subcategory')) {
            $query->where('sub_category_id', $request->subcategory);
        }

        if ($request->filled('brand_name')) {
            $query->where('brand_id', $request->brand_name);
        }

        if ($request->filled('product_name')) {
            $query->where('product_id', $request->product_name);
            // OR: if your product_name filter is based on text input, use LIKE
            // $query->where('product_name', 'LIKE', '%' . $request->product_name . '%');
        }

        // Sorting
        $products = $query->orderBy('stock', 'asc')->get();

        // Dropdown Data
        $categories = Category::active()->get();
        $brands = Brand::active()->get();

        // Optional: load subcategories if category is selected (for dependent dropdown)
        $subcategories = collect();
        if ($request->filled('category')) {
            $subcategories = SubCategory::where('cat_id', $request->category)->get();
        }

        return view('Stock.stocks', compact('products', 'categories', 'brands', 'subcategories'));
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
        // Validate request
        $request->validate([
            'product_id'   => 'required|exists:products,product_id',
            'current_stock'     => 'required|integer',
            'new_stock' => 'nullable|integer',
            'stock_status' => 'required|string'
        ]);

        $quontinty = $request->stock_status == "in_stock" ? (float)$request->current_stock + (float)$request->new_stock : (float)$request->current_stock - (float)$request->new_stock;

        if ($request->stock_status == "out_stock" && (float)$request->current_stock < (float)$request->new_stock) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Stock can not be lessthan 0',
                'cur_stock' => $quontinty,
            ]);
        }

        $stock = StockManagement::create(
            [
                'product_id'   => $request->product_id,
                'quantity'     => $request->new_stock,
                'stock_status' => $request->stock_status,
                'notes'        => 'Stock Updated',
            ],
        );

        if ($stock) {
            Product::where(['product_id' => $request->product_id])->update(['stock' => $quontinty]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Stock added successfully',
            'cur_stock' => $quontinty,
            'unit' => $request->hidden_unit
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($product_id)
    {
        $product = Product::select(['product_id', 'product_name'])
            ->findOrFail($product_id);

        // Paginate stock entries for this product
        $history = $product->stock()->orderBy('created_at', 'asc')->paginate(10);

        return view('Stock.stock-history', compact('product', 'history'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockManagement $stockManagement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockManagement $stockManagement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockManagement $stockManagement)
    {
        //
    }
}
