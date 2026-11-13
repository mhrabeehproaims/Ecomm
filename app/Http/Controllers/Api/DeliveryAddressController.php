<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliverAddressRequest;
use App\Services\Storefront\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryAddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['status' => 'success', 'data' => DB::table('delivery_addresses')->where('customer_id', $request->user()->customer_id)->orderBy('address_id')->get()]);
    }

    public function show(Request $request, string $id)
    {
        $address = DB::table('delivery_addresses')->where('customer_id', $request->user()->customer_id)->where('address_id', $id)->first();
        abort_unless($address, 404, 'Address not found.');

        return response()->json(['status' => 'success', 'data' => $address]);
    }

    public function store(DeliverAddressRequest $request)
    {
        return $this->save($request);
    }

    public function update(DeliverAddressRequest $request, string $id)
    {
        return $this->save($request, $id);
    }

    private function save(DeliverAddressRequest $request, ?string $id = null)
    {
        $customer = $request->user()->customer_id;
        $data = $request->validated();
        $data = array_replace(['email' => null, 'address_line2' => null, 'country' => 'India', 'is_default' => false], $data);
        $data['country'] ??= 'India';
        $data['is_default'] = (bool) $data['is_default'];
        DB::transaction(function () use ($customer, $id, $data) {
            app(CheckoutService::class)->lockCustomer($customer);
            $owned = DB::table('delivery_addresses')->where('customer_id', $customer);
            if ($id) {
                abort_unless((clone $owned)->where('address_id', $id)->exists(), 404, 'Address not found.');
            }
            if (! $id && ! (clone $owned)->exists()) {
                $data['is_default'] = true;
            }
            if ($data['is_default']) {
                (clone $owned)->where('is_default', 1)->update(['is_default' => false, 'updated_at' => now()]);
            }
            if ($id) {
                (clone $owned)->where('address_id', $id)->update([...$data, 'updated_at' => now()]);
            } else {
                DB::table('delivery_addresses')->insert([...$data, 'customer_id' => $customer, 'created_at' => now(), 'updated_at' => now()]);
            }
        }, 5);

        return response()->json(['status' => 'success']);
    }

    public function destroy(Request $request, string $id)
    {
        DB::transaction(function () use ($request, $id) {
            $customer = $request->user()->customer_id;
            app(CheckoutService::class)->lockCustomer($customer);
            $owned = DB::table('delivery_addresses')->where('customer_id', $customer);
            $address = (clone $owned)->where('address_id', $id)->first();
            abort_unless($address, 404, 'Address not found.');
            (clone $owned)->where('address_id', $id)->delete();
            if ($address->is_default && ($next = (clone $owned)->orderBy('address_id')->first())) {
                (clone $owned)->where('address_id', $next->address_id)->update(['is_default' => true, 'updated_at' => now()]);
            }
        }, 5);

        return response()->json(['status' => 'success', 'message' => 'Address Deleted']);
    }
}
