<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxes = Tax::latest()->get();
        return view('Tax.taxes', ['taxes' => $taxes]);
    }

    public function store(StoreTaxRequest $request)
    {
        $validated = $request->validated();

        $data = [
            'tax_name' => $validated['tax_name'],
            'tax_percentage' => $validated['tax_percent'],
            'cgst' => $validated['tax_cgst'],
            'sgst' => $validated['tax_sgst'],
            'igst' => $validated['tax_igst'],
            'status' => $validated['tax_status'],
        ];

        Tax::create($data);

        return response()->json(['message' => 'success']);
    }

    //update status only
    public function edit(Tax $tax_master)
    {
        $tax_master->status = !$tax_master->status;
        $tax_master->save();

        return response()->json([
            'message' => 'success',
            'status' => $tax_master->status
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxRequest $request, Tax $tax_master)
    {
        $validated = $request->validated();
        $data = [
            'tax_name' => $validated['tax_name'],
            'tax_percentage' => $validated['tax_percent'],
            'cgst' => $validated['tax_cgst'],
            'sgst' => $validated['tax_sgst'],
            'igst' => $validated['tax_igst'],
            'status' => $validated['tax_status'],
        ];

        $tax_master->update($data);
        return response()->json(['message' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax_master)
    {
        if ($tax_master->products()->exists()) {
            return response()->json(['status' => 'Failed! This unit is already used']);
        }
        $tax_master->delete();
        return response()->json(['status' => 'success']);
    }
}
