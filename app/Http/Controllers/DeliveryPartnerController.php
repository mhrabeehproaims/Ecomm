<?php

namespace App\Http\Controllers;

use App\Models\DeliveryPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class DeliveryPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $partners = DeliveryPartner::latest()->get();
        // return $partners;
        return view('DeliveryParnter.partners', ['delivery_partners' => $partners]);
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
        $validated = Validator::make($request->all(), [
            'partner_name' => 'required|string',
            'contact_person' => 'required|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'required',
            'address_line' => 'required|string|max:255',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pincode' => 'required|string|max:10',
            'tracking_url'   => 'nullable|url|max:255',
            'per_km_rate'    => 'nullable|numeric|min:0',
            'base_charge'    => 'nullable|numeric|min:0',
            'parnter_status' => 'required',
            'website'        => 'nullable|url|max:255',
        ]);


        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validated->errors()
            ], 422);
        }
        $validated_data = $validated->validated();
        $validated_data['status'] = $validated_data['parnter_status'] ?? null;
        unset($validated_data['parnter_status']);
        DeliveryPartner::create($validated_data);

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryPartner $deliveryPartner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function switchStatus(DeliveryPartner $deliveryPartner, Request $request)
    {
        $request->validate([
            'partner_status' => 'required|in:active,inactive'
        ]);

        $deliveryPartner->update(['status' => $request->partner_status]);

        return response()->json(
            [
                'status' => 'success',
                'message' => $request->partner_status,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryPartner $deliveryPartner)
    {
        $validated = Validator::make($request->all(), [
            'partner_name' => 'required|string',
            'contact_person' => 'required|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'required',
            'address_line' => 'required|string|max:255',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pincode' => 'required|string|max:10',
            'tracking_url'   => 'nullable|url|max:255',
            'per_km_rate'    => 'nullable|numeric|min:0',
            'base_charge'    => 'nullable|numeric|min:0',
            'parnter_status' => 'required',
            'website'        => 'nullable|url|max:255',
        ]);


        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validated->errors()
            ], 422);
        }
        $validated_data = $validated->validated();
        $validated_data['status'] = $validated_data['parnter_status'] ?? null;
        unset($validated_data['parnter_status']);
        $deliveryPartner->update($validated_data);

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryPartner $deliveryPartner)
    {
        // if ($deliveryPartner->orders()->exists()) {
        // return response()->json(['status' => 'Failed! This brand is already used']);
        // } elseif ($deliveryPartner->delete()) {
        $deliveryPartner->delete();
        return response()->json(['status' => 'success']);
        // } else {
        //     return response()->json(['status' => 'failed']);
        // }
    }
}
