<?php

namespace App\Http\Controllers;

use App\Models\weight;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WeightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $weights = weight::orderBy('created_at', 'desc')->get();

        return view('Weight.weights', ['weights' => $weights]);
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
        $validated = $request->validate([
            'weight_name' => 'required|string|unique:weights,weight_name',
            'weight_status' => 'integer'
        ]);

        $data = [
            'weight_name' => $validated['weight_name'],
            'status' => $validated['weight_status']
        ];

        $weight = weight::create($data);

        return response()->json(['message' => 'success', 'data' => $weight]);
    }

    /**
     * Display the specified resource.
     */
    public function show(weight $weight)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(weight $weight)
    {
        $weight->status = !$weight->status;
        $weight->update();

        return response()->json(['message' => 'success']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, weight $weight)
    {
        $validated = $request->validate([
            'weight_name' => [
                'required',
                Rule::unique('weights', 'weight_name')->ignore($weight->weight_id, 'weight_id')
            ],
            'weight_status' => 'nullable|boolean'
        ]);

        $data = [
            'weight_name' => $validated['weight_name'],
            'status' => $validated['weight_status'] ?? 1
        ];

        $weight->update($data);

        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(weight $weight)
    {
        if ($weight->products()->exists()) {
            return response()->json(['status' => 'Failed! This unit is already used']);
        }
        $weight->delete();
        return response()->json(['status' => 'success']);
    }
}
