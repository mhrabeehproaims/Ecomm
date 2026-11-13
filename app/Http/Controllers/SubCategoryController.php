<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categs = Category::where('status', 1)->get();
        $sub_categs = SubCategory::with('category')->get();
        return view('Sub-category.sub-categs', compact('categs', 'sub_categs'));
        // return $sub_categs;
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
        $valid_data = $request->validate([
            'category' => 'required|integer',
            'subCateg_name' => [
                'required',
                'string',
                Rule::unique('sub_categories', 'subCat_name')
                    ->where('cat_id', $request->category),
            ], // to validates same sub category name under different category
            'SubCateg_desc' => 'nullable|string',
            'return_days' => 'nullable|integer',
            'subCateg_status' => 'required|integer',
            'SubCateg_image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $data = [
            'cat_id' => $valid_data['category'],
            'subCat_name' => $valid_data['subCateg_name'],
            'subcateg_desc' => $valid_data['SubCateg_desc'],
            'return_days' => $valid_data['return_days'],
            'status' => $valid_data['subCateg_status'],
        ];

        if ($request->hasFile('SubCateg_image')) {
            $file = $request->file('SubCateg_image');
            $file_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();

            $file->storeAs('subCategImage', $file_name, 'public');
            $data['image'] = $file_name;
        } else {
            $data['image'] = '';
        }

        SubCategory::create($data);

        return response()->json(['message' => 'success', 'data' => $data]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategory $subCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $valid_data = $request->validate([
            'category' => 'required|integer',
            'subCateg_name' => [
                'required',
                'string',
                Rule::unique('sub_categories', 'subCat_name')
                    ->where('cat_id', $request->category)->ignore($subCategory->subCat_id, 'subCat_id',),
            ], // to validates same sub category name under different category
            'SubCateg_desc' => 'nullable|string',
            'return_days' => 'nullable|integer',
            'subCateg_status' => 'required|integer',
            'SubCateg_image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $data = [
            'cat_id' => $valid_data['category'],
            'subCat_name' => $valid_data['subCateg_name'],
            'subcateg_desc' => $valid_data['SubCateg_desc'],
            'return_days' => $valid_data['return_days'] ?? 0,
            'status' => $valid_data['subCateg_status']
        ];

        if ($request->hasFile('SubCateg_image')) {

            if ($subCategory->image) {
                Storage::disk('public')->delete('subCategImage/' . $subCategory->image);
            }

            $file = $request->file('SubCateg_image');
            $filename = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('subCategImage', $filename, 'public');
            $data['image'] = $filename;
        }

        $subCategory->update($data);

        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sub = SubCategory::findOrFail($id);

        if ($sub->products()->exists()) {
            return response()->json(['status' => 'Failed! This Subcategory is already used']);
        }
        $sub->delete();
        if ($sub->image) {
            Storage::disk('public')->delete('subCategImage/' . $sub->image);
        }
        return response()->json(['status' => 'success']);
    }
}
