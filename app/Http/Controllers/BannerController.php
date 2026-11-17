<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Traits\BannerNavigationData;

class BannerController extends Controller
{
    use BannerNavigationData;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::latest()->get();
        return view('Banner.banners', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'banner_image' => 'required',
    //         'select-reference' => 'required_if:reference_type:category,subcategory,brand,product',
    //         'banner_order' => 'required|unique:banners,sort_order'
    //     ]);

    //     $data = [
    //         'title' => $request->input('banner_title') ?? "",
    //         'description' => "",
    //         'reference_type' => $request->input('reference_type'),
    //         'reference_id' => $request->input('select-reference'),
    //         'status' => $request->input('banner_status') ?? 1,
    //         'sort_order' => $request->input('banner_order'),
    //     ];

    //     if ($request->hasFile('banner_image')) {
    //         $file = $request->file('banner_image');
    //         $file_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
    //         $file->storeAs('BannerImage', $file_name, 'public');

    //         $data['image_path'] = $file_name;
    //     } else {
    //         $data['image_path'] = "";
    //     }


    //     Banner::create($data);
    //     return response()->json(['message' => 'success']);
    // }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'banner_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
    //         'banner_title' => 'nullable|string|max:255',
    //         'subtitle' => 'nullable|string',
    //         'button_text' => 'nullable|string|max:100',
    //         'reference_type' => 'nullable|in:category,subcategory,brand,product',
    //         'select-reference' => 'nullable|integer',
    //         'button_link' => 'nullable|string|max:255',
    //         'button_position' => 'required|in:preset,custom',
    //         'preset_position' => 'nullable|string',
    //         'button_x_position' => 'nullable|integer|min:0|max:100',
    //         'button_y_position' => 'nullable|integer|min:0|max:100',
    //         'button_color' => 'nullable|string|max:20',
    //         'button_text_color' => 'nullable|string|max:20',
    //         'button_font_size' => 'nullable|integer|min:10|max:48',
    //         'button_shape' => 'nullable|in:rounded,pill,square',
    //         'f_date' => 'required|date',
    //         't_date' => 'required|date|after_or_equal:f_date',
    //         'banner_status' => 'required|boolean',
    //         'banner_order' => 'required|integer|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         // Handle image upload
    //         $imagePath = null;
    //         if ($request->hasFile('banner_image')) {
    //             $image = $request->file('banner_image');
    //             $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //             $imagePath = $image->storeAs('BannerImage', $imageName, 'public');
    //         }

    //         // Determine navigation type and IDs
    //         $navigationData = $this->getNavigationData($request);

    //         // Determine button position
    //         $buttonPositionData = $this->getButtonPositionData($request);

    //         // Create banner
    //         $banner = Banner::create([
    //             'banner_name' => $request->banner_title ?? 'Banner ' . time(),
    //             'banner_image' => $imagePath,
    //             'banner_title' => $request->banner_title,
    //             'banner_subtitle' => $request->subtitle,
    //             'button_text' => $request->button_text ?? 'Shop Now',
    //             'button_link' => $request->button_link ?? '#new-arrivals',
    //             'category_id' => $navigationData['category_id'],
    //             'product_id' => $navigationData['product_id'],
    //             'navigation_type' => $navigationData['navigation_type'],
    //             'button_position' => $buttonPositionData['position'],
    //             'button_x_position' => $buttonPositionData['x_position'],
    //             'button_y_position' => $buttonPositionData['y_position'],
    //             'button_color' => $request->button_color ?? '#ff6b00',
    //             'button_text_color' => $request->button_text_color ?? '#ffffff',
    //             'button_font_size' => $request->button_font_size ? $request->button_font_size . 'px' : '16px',
    //             'button_shape' => $request->button_shape ?? 'rounded',
    //             'from_date' => $request->f_date,
    //             'to_date' => $request->t_date,
    //             'banner_status' => $request->banner_status,
    //             'display_order' => $request->banner_order,
    //         ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Banner created successfully',
    //             'data' => $banner
    //         ], 201);
    //     } catch (\Exception $e) {
    //         // Delete uploaded image if banner creation fails
    //         if (isset($imagePath)) {
    //             Storage::disk('public')->delete($imagePath);
    //         }

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to create banner',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banner_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'banner_title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'reference_type' => 'nullable',
            'select-reference' => 'nullable|integer',
            'button_link' => 'nullable|string',
            'button_position_type' => 'required|in:preset,custom',
            'preset_position' => 'nullable|string',
            'button_x_position' => 'nullable|integer|min:0|max:100',
            'button_y_position' => 'nullable|integer|min:0|max:100',
            'button_color' => 'nullable|string|max:20',
            'button_text_color' => 'nullable|string|max:20',
            'button_font_size' => 'nullable|integer|min:10|max:48',
            'button_shape' => 'nullable|in:rounded,pill,square',
            'f_date' => 'required|date',
            't_date' => 'required|date|after_or_equal:f_date',
            'banner_status' => 'required|boolean',
            'banner_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('banners', 'sort_order')
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('banner_image')) {
                $image = $request->file('banner_image');
                $imageName = time() . '_' . Str::random(5) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('BannerImage', $imageName, 'public');
            }

            // Get button position data from trait
            $buttonPositionData = $this->getButtonPositionData($request);

            // Create banner
            $banner = Banner::create([
                'banner_title' => $request->banner_title ?? '',
                'banner_subtitle' => $request->subtitle ?? '',
                'banner_image' => $imageName,
                'button_text' => $request->button_text ?? 'Shop Now',
                'button_link' => $request->button_link ?? '#new-arrivals',
                'reference_type' => $request->reference_type,
                'reference_id' => $request->input('select-reference'),
                'button_position_type' => $request->button_position_type,
                'preset_position' => $buttonPositionData['position'],
                'button_x_position' => $buttonPositionData['x_position'],
                'button_y_position' => $buttonPositionData['y_position'],
                'button_color' => $request->button_color ?? '#ff6b00',
                'button_text_color' => $request->button_text_color ?? '#ffffff',
                'button_font_size' => $request->button_font_size ? $request->button_font_size . 'px' : '16px',
                'button_shape' => $request->button_shape ?? 'rounded',
                'from_date' => $request->f_date,
                'to_date' => $request->t_date,
                'status' => $request->banner_status,
                'sort_order' => $request->banner_order,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Banner created successfully',
                // 'data' => $banner
            ], 201);
        } catch (\Exception $e) {
            // Delete uploaded image if banner creation fails
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'banner_title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'reference_type' => 'nullable|in:category,subcategory,brand,product,link',
            'select-reference' => 'nullable|integer',
            'button_link' => 'nullable|string|max:255',
            'button_position_type' => 'required|in:preset,custom',
            'preset_position' => 'nullable|string',
            'button_x_position' => 'nullable|integer|min:0|max:100',
            'button_y_position' => 'nullable|integer|min:0|max:100',
            'button_color' => 'nullable|string|max:20',
            'button_text_color' => 'nullable|string|max:20',
            'button_font_size' => 'nullable|integer|min:10|max:48',
            'button_shape' => 'nullable|in:rounded,pill,square',
            'f_date' => 'required|date',
            't_date' => 'required|date|after_or_equal:f_date',
            'banner_status' => 'required|boolean',
            'banner_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('banners', 'sort_order')
                    ->ignore($id, 'banner_id') // ✅ correct
                    ->where(fn($q) => $q->where('banner_id', $request->banner_id)),

            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }



        try {
            // Handle image upload
            $imagePath = $banner->banner_image;

            $buttonPositionData = $this->getButtonPositionData($request);
            $update_data = [
                'banner_title' => $request->banner_title ?? '',
                'banner_subtitle' => $request->subtitle ?? '',
                'button_text' => $request->button_text ?? 'Shop Now',
                'button_link' => $request->button_link ?? '#new-arrivals',
                'reference_type' => $request->reference_type,
                'reference_id' => $request->input('select-reference'),
                'button_position_type' => $request->button_position_type,
                'preset_position' => $buttonPositionData['position'],
                'button_x_position' => $buttonPositionData['x_position'],
                'button_y_position' => $buttonPositionData['y_position'],
                'button_color' => $request->button_color ?? '#ff6b00',
                'button_text_color' => $request->button_text_color ?? '#ffffff',
                'button_font_size' => $request->button_font_size ? $request->button_font_size . 'px' : '16px',
                'button_shape' => $request->button_shape ?? 'rounded',
                'from_date' => $request->f_date,
                'to_date' => $request->t_date,
                'banner_status' => $request->banner_status,
                'sort_order' => $request->banner_order,
            ];

            if ($request->hasFile('banner_image')) {
                // Delete old image if exists
                if ($banner->banner_image) {
                    Storage::disk('public')->delete('BannerImage/' . $banner->banner_image);
                }

                $image = $request->file('banner_image');
                $imageName = time() . '_' . Str::random(5) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('BannerImage', $imageName, 'public');
                $update_data['banner_image'] = $imageName;
            }

            $banner->update($update_data);

            return response()->json([
                'status' => 'success',
                'message' => 'Banner updated successfully',
                'data' => $banner
            ], 200);
        } catch (\Exception $e) {
            // Rollback image if something goes wrong
            if ($request->hasFile('banner_image') && isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function destroy(Banner $banner)
    {
        if ($banner->delete()) {
            if ($banner->banner_image) {
                Storage::disk('public')->delete('BannerImage/' . $banner->banner_image);
            }
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed']);
        }
    }

    public function getBannerTypes($type)
    {
        switch ($type) {
            case 'category':
                $result = Category::active()->get();
                break;

            case 'subcategory':
                $result = SubCategory::active()->get();
                break;

            case 'brand':
                $result = Brand::active()->get();
                break;

            case 'product':
                $result = Product::active()->get();
                break;

            default:
                $result = "";
                break;
        }

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
