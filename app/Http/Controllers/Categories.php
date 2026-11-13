<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Categories extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Categories = Category::orderBy('categ_id', 'desc')->get();
        return view('Category.categs', compact('Categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $name = $request->validate([
            'categ_name' => 'required | unique:categories,categ_name',
            'categ_desc' => 'nullable|string',
            'return_days' => 'nullable|integer',
            'categ_status' => 'required',
            'categ_image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $name['return_days'] = $name['return_days'] ?? 0;

        if ($request->hasFile('categ_image')) {
            $file = $request->file('categ_image');
            $image_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('categoryImage', $image_name, 'public');
            $name['categ_image'] = $image_name;
        } else {
            $name['categ_image'] = '';
        }

        Category::create(['image' => $name['categ_image'], 'categ_name' => $name['categ_name'], 'categ_desc' => $name['categ_desc'], 'return_days' => $name['return_days'], 'status' => $name['categ_status']]);
        return response()->json(['message' => 'success']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

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
        $data = $request->validate([
            'categ_name' => 'required | unique:categories,categ_name,' . $id . ',categ_id',
            'categ_desc' => 'nullable|string',
            'return_days' => 'nullable|integer',
            'categ_status' => 'required',
            'categ_image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $upData = [
            'categ_name' => $data['categ_name'],
            'status' => $data['categ_status'],
            'categ_desc' => $data['categ_desc'],
            'return_days' => $data['return_days']
        ];

        $category = Category::findOrFail($id);

        if ($request->hasFile('categ_image')) {
            if ($category->image) {
                Storage::disk('public')->delete('categoryImage/' . $category->image);
            }

            $file = $request->file('categ_image');
            $fileName = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('categoryImage', $fileName, 'public');
            $upData['image'] = $fileName;
        }


        $category->update($upData);

        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cat = Category::findOrFail($id);
        if ($cat->products()->exists() || $cat->subCategories()->exists()) {
            return response()->json(['status' => 'failed', 'message' => 'Failed! This category is already used']);
        } else {
            $cat->delete();
            if ($cat->image) {
                Storage::disk('public')->delete('categoryImage/' . $cat->image);
            }
            return response()->json(['status' => 'success']);
        }
    }
}
