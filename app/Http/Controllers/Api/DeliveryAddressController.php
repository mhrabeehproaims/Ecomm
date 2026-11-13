<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliverAddressRequest;
use App\Models\DeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(DeliverAddressRequest $request)
    {
        $data = [
            'customer_id' => Auth::user()->customer_id,
            'phone' => $request->validated()['phone'],
            'email' => $request->validated()['email'],
            'address_line1' => $request->validated()['address_line1'],
            'address_line2' => $request->validated()['address_line2'],
            'city' => $request->validated()['city'],
            'state' => $request->validated()['state'],
            'country' => $request->validated()['country'],
            'postal_code' => $request->validated()['postal_code'],
            'is_default' => $request->validated()['is_default'],
        ];

        $addressQuery = DeliveryAddress::where('customer_id', Auth::user()->customer_id);

        if (!$addressQuery->exists()) {
            $data['is_default'] = 1;
        } elseif (!empty($data['is_default'])) {
            $addressQuery
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

        DeliveryAddress::create($data);

        return response()->json([
            'status' => 'success',
        ]);
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
    public function update(DeliverAddressRequest $request, string $id)
    {
        $data = [
            'phone' => $request->validated()['phone'],
            'email' => $request->validated()['email'],
            'address_line1' => $request->validated()['address_line1'],
            'address_line2' => $request->validated()['address_line2'],
            'city' => $request->validated()['city'],
            'state' => $request->validated()['state'],
            'country' => $request->validated()['country'],
            'postal_code' => $request->validated()['postal_code'],
            'is_default' => $request->validated()['is_default'],
        ];

        if ($request->validated()['is_default']) {
            DeliveryAddress::where('customer_id', Auth::user()->customer_id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

        DeliveryAddress::where('address_id', $id)->update($data);

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user_id = Auth::user()->customer_id;
        $address = DeliveryAddress::where(['customer_id' => $user_id, 'address_id' => $id])->first();
        if (!$address) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Address not found'
            ]);
        }

        $address->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Address Deleted'
        ]);
    }
}
