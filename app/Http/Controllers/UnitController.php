<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\updateUnitRequest;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->get();
        return view('Unit.units', ['units' => $units]);
    }

    public function store(StoreUnitRequest $request)
    {

        $data = [
            'unit_name' => $request->validated()['unit_name'],
            'status'    => $request->validated()['unit_status'],
        ];


        Unit::create($data);

        return response()->json(['message' => 'success']);
    }

    public function update(updateUnitRequest $request, string $id)
    {
        $data = [
            'unit_name' => $request->validated()['unit_name'],
            'status'    => $request->validated()['unit_status'],
        ];

        // $upData = [
        //     'unit_name' => $data['unit_name'],
        //     'status' => $data['status']
        // ];

        $unit = Unit::findOrFail($id);

        $unit->update($data);

        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cat = Unit::findOrFail($id);
     
        if ($cat->products()->exists()) {
            return response()->json(['status' => 'Failed! This unit is already used']);
        }
        $cat->delete();
        return response()->json(['status' => 'success']);
    }
}
