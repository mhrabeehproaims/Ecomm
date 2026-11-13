<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSizeRequest;
use App\Http\Requests\UpdateSizeRequest;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::latest()->get();
        return view('Size.sizes', ['sizes' => $sizes]);
    }

    public function store(StoreSizeRequest $request)
    {

        $data = [
            'size_name' => $request->validated()['size_name'],
            'status'    => $request->validated()['size_status']
        ];


        Size::create($data);

        return response()->json(['message' => 'success']);
    }

    public function update(UpdateSizeRequest $request, string $id)
    {
        $data = [
            'size_name' => $request->validated()['size_name'],
            'status'    => $request->validated()['size_status'],
        ];

        $size = Size::findOrFail($id);

        $size->update($data);

        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $size = Size::findOrFail($id);
        if ($size->products()->exists()) {
            return response()->json(['status' => 'Failed! This unit is already used']);
        }
        $size->delete();
        return response()->json(['status' => 'success']);
    }
}
