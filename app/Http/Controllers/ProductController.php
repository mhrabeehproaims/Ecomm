<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\StockManagement;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\weight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('product_id', 'desc')->get();
        return view('Product.products', ['products' => $products]);
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $packageTypes = PackagingType::active()->get();
        $units = Unit::active()->get();
        $sizes = Size::active()->get();
        $tax_schedules = Tax::active()->get();
        $weights = weight::active()->get();

        return view('Product.create', [
            'categories' => $categories,
            'brands' => $brands,
            'package_types' => $packageTypes,
            'sizes' => $sizes,
            'units' => $units,
            'taxSchedules' => $tax_schedules,
            'weights' => $weights
        ]);
    }

    public function getSubcategories($categoryId)
    {
        $subcategories = SubCategory::where('cat_id', $categoryId)->where('status', '1')->get();

        if ($subcategories->isNotEmpty()) {
            $data = [
                'status' => 'subCategory',
                'data' => $subcategories
            ];
        } else {
            $products = Product::select('product_id', 'product_name')
                ->where('category_id', $categoryId)
                ->get();

            $data = [
                'status' => 'product',
                'data' => $products
            ];
        }

        return response()->json($data);
    }

    public function getOfferProducts(Request $request)
    {
        $categoryIds = array_filter($request->categories ?? []);
        $subcategoryIds = array_filter($request->subcategories ?? []);
        $brandIds = array_filter($request->brands ?? []);

        $products = Product::select('product_id', 'product_name', 'mrp', 'special_price')
            ->when(count($categoryIds), fn($q) => $q->whereIn('category_id', $categoryIds))
            ->when(count($subcategoryIds), fn($q) => $q->whereIn('sub_category_id', $subcategoryIds))
            ->when(count($brandIds), fn($q) => $q->whereIn('brand_id', $brandIds))
            ->where('status', 1)
            ->get();

        return response()->json([
            'status' => 'success',
            'products' => $products
        ]);
    }

    public function getproductsBySubcategories($sub_categoryId)
    {
        $products = Product::select('product_id', 'product_name', 'mrp', 'special_price')->where('sub_category_id', $sub_categoryId)->get();

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = [
                'product_name' => $request->validated()['product_name'],
                'description' => $request->validated()['product_desc'],
                'health_benefit' => $request->validated()['health_benefit'],
                'category_id' => $request->validated()['category'] ?? null,
                'sub_category_id' => $request->validated()['subcategory'] ?? null,
                'stock' => $request->validated()['product_stock'] ?? 0,
                'minimum_stock' => $request->validated()['minimum_stock'] ?? 0,
                'brand_id' => $request->validated()['product_brand'] ?? null,
                'size_id' => $request->validated()['product_size'] ?? null,
                'weight_id' => $request->validated()['product_weight'] ?? null,
                'unit_id' => $request->validated()['product_unit'] ?? null,
                'package_type_id' => $request->validated()['package_type'] ?? null,
                'mrp' => $request->validated()['product_mrp'],
                'retail' => $request->validated()['retail_price'] ?? 0,
                'wholesale' => $request->validated()['wholesale_price'] ?? 0,
                'special_price' => $request->validated()['special_price'] ?? 0,
                'tax_schedule_id' => $request->validated()['tax_schedule'] ?? null,
                'hsn_code' => $request->validated()['hsn_code'] ?? null,
                'discount' => $request->validated()['product_discount'] ?? 0,
                'discount_type' => $request->validated()['discount_type'] ?? null,
                'package_weight' => $request->validated()['package_weight'] ?? null,
                'primary_price' => $request->validated()['primary_price'] ?? 'mrp',
            ];

            $product = Product::create($data);

            // Upload product images
            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $index => $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('Products', $filename, 'public');

                    if ($path === false) {
                        Log::warning('Failed to store product image', [
                            'product_id' => $product->product_id,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                        continue;
                    }

                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $index === 0 ? 1 : 0,
                    ]);
                }
            }

            // Upload description images
            if ($request->hasFile('desc_images')) {
                $files = $request->file('desc_images');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('DescriptionImages', $filename, 'public');

                    if ($path === false) {
                        Log::warning('Failed to store description image', [
                            'product_id' => $product->product_id,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                        continue;
                    }

                    $product->descriptionImages()->create([
                        'image_name' => $path,
                    ]);
                }
            }

            // Create Stock
            StockManagement::create([
                'product_id' => $product->product_id,
                'quantity' => $data['stock'],
                'stock_status' => 'in_stock',
                'notes' => 'Stock Created',
            ]);
        });

        return redirect()->route('products.create')->with('message', 'success');
    }

    public function updateStatus(Product $product)
    {
        $product->status = !$product->status;
        Product::withoutEvents(function () use ($product) {
            $product->save();
        });

        return response()->json([
            'message' => 'success',
            'status' => $product->status
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['images', 'descriptionImages']);

        $productData = [
            'name' => $product->product_name,
            'description' => $product->description,
            'health_benefit' => $product->health_benefit,
            'category' => $product->category->categ_name ?? '',
            'sub_category' => $product->sub_category_name ?? '',
            'stock' => $product->stock ?? '',
            'minimum_stock' => $product->minimum_stock ?? '',
            'brand_name' => $product->brand->brand_name ?? '',
            'size' => $product->size->size_name ?? '',
            'unit' => $product->unit->unit_name ?? '',
            'product_weight' => $product->weight->weight_name ?? '',
            'package_type' => $product->packageType->package_type ?? '',
            'mrp' => $product->mrp,
            'retail' => $product->retail ?? 0.0,
            'wholesale' => $product->wholesale ?? 0.0,
            'special_price' => $product->special_price ?? 0.0,
            'primary_price' => ucwords($product->primary_price) ?? '',
            'tax_schedule' => $product->taxSchedule->tax_name ?? '',
            'tax_percentage' => $product->taxSchedule?->tax_percentage ? $product->taxSchedule->tax_percentage . ' (%)' : 0,
            'tax_igst' => $product->taxSchedule?->igst ? $product->taxSchedule->igst . ' (%)' : 0,
            'tax_cgst' => $product->taxSchedule?->cgst ? $product->taxSchedule->cgst . ' (%)' : 0,
            'tax_sgst' => $product->taxSchedule?->sgst ? $product->taxSchedule->sgst . ' (%)' : 0,
            'package_weight' => $product->package_weight ?? null,
            'hsn_code' => $product->hsn_code,
            'created_at' => $product->created_at ? $product->created_at->format('d-m-Y') : '',
            'status' => $product->status == 1 ? 'Active' : 'Inactive',
            'images' => $product->images,
            'desc_images' => $product->descriptionImages,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $productData
        ]);
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'descriptionImages']);
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $packageTypes = PackagingType::active()->get();
        $units = Unit::active()->get();
        $sizes = Size::active()->get();
        $tax_schedules = Tax::active()->get();
        $weights = weight::active()->get();
        $gsts = '';

        $priceOptions = [
            'mrp' => $product->mrp,
            'retail' => $product->retail,
            'wholesale' => $product->wholesale,
            'special_price' => $product->special_price,
        ];

        if (isset($priceOptions[$product->primary_price]) && $product->taxSchedule) {
            $gsts = $this->calculateGST($priceOptions[$product->primary_price], (float) $product->taxSchedule->tax_percentage);
        } else {
            $gsts = [
                'cgst_amount' => null,
                'sgst_amount' => null,
                'igst_amount' => null,
                'primary_price' => $priceOptions[$product->primary_price] ?? 0
            ];
        }

        return view('Product.edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'package_types' => $packageTypes,
            'sizes' => $sizes,
            'units' => $units,
            'taxSchedules' => $tax_schedules,
            'weights' => $weights,
            'gst' => $gsts,
            'images' => $product->images,
            'desc_images' => $product->descriptionImages
        ]);
    }

    public function calculateGST($product_netPrice, $taxPercent)
    {
        $result = [];
        $cgst = $taxPercent / 2;
        $sgst = $taxPercent / 2;
        if ($product_netPrice > 0 && $taxPercent) {
            $cgst_amount = ($cgst / 100) * $product_netPrice;
            $sgst_amount = ($sgst / 100) * $product_netPrice;
            $igst_amount = ($taxPercent / 100) * $product_netPrice;

            $result = [
                'cgst_amount' => number_format($cgst_amount, 2),
                'sgst_amount' => number_format($sgst_amount, 2),
                'igst_amount' => number_format($igst_amount, 2),
                'primary_price' => $product_netPrice
            ];
        }

        return $result;
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            // Update product data
            $data = [
                'product_name' => $request->validated()['product_name'],
                'description' => $request->validated()['product_desc'],
                'health_benefit' => $request->validated()['health_benefit'],
                'category_id' => $request->validated()['category'] ?? null,
                'sub_category_id' => $request->validated()['subcategory'] ?? null,
                'stock' => $request->validated()['product_stock'] ?? 0,
                'minimum_stock' => $request->validated()['minimum_stock'] ?? 0,
                'brand_id' => $request->validated()['product_brand'] ?? null,
                'size_id' => $request->validated()['product_size'] ?? null,
                'weight_id' => $request->validated()['product_weight'] ?? null,
                'unit_id' => $request->validated()['product_unit'] ?? null,
                'package_type_id' => $request->validated()['package_type'] ?? null,
                'mrp' => $request->validated()['product_mrp'],
                'retail' => $request->validated()['retail_price'] ?? 0,
                'wholesale' => $request->validated()['wholesale_price'] ?? 0,
                'special_price' => $request->validated()['special_price'] ?? 0,
                'primary_price' => $request->validated()['primary_price'] ?? 'mrp',
                'tax_schedule_id' => $request->validated()['tax_schedule'] ?? null,
                'hsn_code' => $request->validated()['hsn_code'] ?? null,
                'discount' => $request->validated()['product_discount'] ?? 0,
                'discount_type' => $request->validated()['discount_type'] ?? null,
                'package_weight' => $request->validated()['package_weight'] ?? null,
            ];

            Product::withoutEvents(function () use ($product, $data) {
                $product->update($data);
            });

            // Handle product images
            $existingImageIds = $request->input('existing_images', []);

            // Delete removed images
            $imagesToDelete = $product
                ->images()
                ->whereNotIn('image_id', $existingImageIds)
                ->get();

            foreach ($imagesToDelete as $img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }

            // Upload new images
            if ($request->hasFile('product_images')) {
                $files = is_array($request->file('product_images'))
                    ? $request->file('product_images')
                    : [$request->file('product_images')];

                foreach ($files as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('Products', $filename, 'public');

                    if ($path === false) {
                        Log::warning('Failed to store product image during update', [
                            'product_id' => $product->product_id,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                        continue;
                    }

                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => 0,
                    ]);
                }
            }

            // Ensure at least one primary image
            if ($product->images()->where('is_primary', 1)->count() === 0 && $product->images()->exists()) {
                $firstImage = $product->images()->oldest()->first();
                $firstImage->update(['is_primary' => 1]);
            }

            // Handle description images
            $existingDescImageIds = $request->input('existing_images_desc', []);

            $descImagesToDelete = $product
                ->descriptionImages()
                ->whereNotIn('id', $existingDescImageIds)
                ->get();

            foreach ($descImagesToDelete as $img) {
                Storage::disk('public')->delete($img->image_name);
                $img->delete();
            }

            if ($request->hasFile('desc_images')) {
                $files = is_array($request->file('desc_images'))
                    ? $request->file('desc_images')
                    : [$request->file('desc_images')];

                foreach ($files as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('DescriptionImages', $filename, 'public');

                    if ($path === false) {
                        Log::warning('Failed to store description image during update', [
                            'product_id' => $product->product_id,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                        continue;
                    }

                    $product->descriptionImages()->create([
                        'image_name' => $path,
                    ]);
                }
            }
        });

        return redirect()->route('products.index')->with('message', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->load(['images', 'descriptionImages']);

        if ($product->images->count()) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->image_path);  // adjust column name if needed
                $img->delete();
            }
        }

        if ($product->descriptionImages->count()) {
            foreach ($product->descriptionImages as $descImg) {
                Storage::disk('public')->delete($descImg->image_name);  // adjust column name if needed
                $descImg->delete();
            }
        }

        if ($product->delete()) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failed'], 500);
    }
}
