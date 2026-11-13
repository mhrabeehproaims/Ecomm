<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::latest()->get();
        return view('Brand.brands', compact('brands'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_name' => 'required|unique:brands,brand_name',
            'brand_image' => 'image|nullable|mimes:jpg,jpeg,png,webp',
            'brand_status' => 'required|in:0,1'
        ]);

        if ($request->hasFile('brand_image')) {
            $file = $request->file('brand_image');
            // $file_name = time() . '_' . $file->getClientOriginalName();
            $file_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('BrandImage', $file_name, 'public');

            $data['image'] = $file_name;
        } else {
            $data['image'] = "";
        }

        Brand::create(['brand_name' => $data['brand_name'], 'image' => $data['image'], 'status' => $data['brand_status']]);
        return response()->json(['message' => 'success']);
    }


    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'brand_name' => 'required|unique:brands,brand_name,' . $brand->brand_id . ',brand_id',
            'brand_image' => 'image|nullable|mimes:jpg,jpeg,png,webp',
            'brand_status' => 'required|in:0,1'
        ]);

        $upData = [
            'brand_name' => $data['brand_name'],
            'status' => $data['brand_status']
        ];

        if ($request->hasFile('brand_image')) {
            if ($brand->image) {
                Storage::disk('public')->delete('BrandImage/' . $brand->image);
            }
            $file = $request->file('brand_image');
            $file_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('BrandImage', $file_name, 'public');

            $upData['image'] = $file_name;
        }

        $brand = Brand::findOrFail($request->hidden_id);
        $brand->update($upData);
        return response()->json(['message' => 'success']);
    }



    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return response()->json(['status' => 'Failed! This brand is already used']);
        } elseif ($brand->delete()) {
            if ($brand->image) {
                Storage::disk('public')->delete('BrandImage/' . $brand->image);
            }
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed']);
        }
    }
}
