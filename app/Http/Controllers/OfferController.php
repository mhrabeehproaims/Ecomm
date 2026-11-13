<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\OfferZone;
use App\Models\Product;
use Illuminate\Http\Request;

class OfferController extends Controller

{
    public function index()
    {
        $offers = OfferZone::orderBy('created_at', 'desc')->paginate(20);
        return view('Offers.index', compact('offers'));
    }


    public function create(Request $request)
    {
        $type = $request->get('type');
        $disc_type = $request->get('disc_type', 'percentage');
        $products = Product::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();

        return view('Offers.create-offer', compact('type', 'products', 'categories', 'brands', 'disc_type'));
    }


    public function store(Request $request)
    {
        $offer = new OfferZone();

        $offer->offer_type = $request->offer_type;
        $offer->title = $request->name;
        $offer->discount_type = $request->discount_type;
        $offer->discount_value = $request->discount_value[0];
        $offer->description = $request->description;
        $offer->discount_from = $request->discount_from;

        // Multiple categories & brands
        $offer->category_ids = $request->category;
        $offer->brand_ids = $request->brand_name;
        $offer->subcategory_ids = $request->subcategory;

        // Build product details table
        $productDetails = [];

        foreach ($request->discount_value as $index => $value) {
            $productDetails[] = [
                'product_id' => $request->product_id[$index],
                'product_mrp' => $request->product_mrp[$index] ?? '',
                'product_srate' => $request->product_srate[$index] ?? '',
                'discount_value' => $value,
                'discount_amount' => $request->discount_amount[$index],
                'final_price' => $request->final_price[$index],
            ];
        }

        $offer->applicable_product_ids = $request->product_id;
        $offer->product_details = $productDetails;
        // $offer->applicable_product_ids =
        //     collect($data['products'] ?? [])
        //     ->pluck('product_id')
        //     ->toArray();
        $offer->start_date = $request->starts_at;
        $offer->end_date = $request->ends_at;
        $offer->status = 1;

        $offer->save();
        return redirect()->route('offers.index');
    }

    public function show($id)
    {
        $offer = OfferZone::findOrFail($id);
        $productIds = is_array($offer->applicable_product_ids)
            ? $offer->applicable_product_ids
            : json_decode($offer->applicable_product_ids, true);

        $offer->products = !empty($productIds)
            ? Product::select('product_id', 'product_name')->whereIn('product_id', $productIds)->get()
            : [];

        return response()->json($offer);
    }
    public function destroy(OfferZone $offer)
    {
        try {
            $offer->delete();
            return back()->with('message', 'Offer Deleted Successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete offer.');
        }
    }
}
