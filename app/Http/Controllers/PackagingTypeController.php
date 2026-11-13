<?php

namespace App\Http\Controllers;

use App\Models\PackagingType;
use Illuminate\Http\Request;
use App\Http\Requests\packageTypeRqst;
use App\Http\Requests\updatePackageType;

class PackagingTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $package_types = PackagingType::latest()->get();

        return view('Package.types', compact('package_types'));
    }


    public function store(packageTypeRqst $request)
    {
        $data = [
            'package_type' => $request->validated()['package_name'],
            'status' => $request->validated()['package_status']
        ];

        PackagingType::create($data);

        return response()->json(['message' => 'success']);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(updatePackageType $request, String $id)
    {
        $data = [
            'package_type' => $request->validated()['package_name'],
            'status' => $request->validated()['package_status']
        ];

        $type = PackagingType::findOrFail($id);
        $type->update($data);

        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PackagingType $package)
    {
        if ($package->products()->exists()) {
            return response()->json(['status' => 'Failed! This package type is already used']);
        }
        $package->delete();
        return response()->json(['status' => 'success']);
    }
}
