<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderHistoryResource;
use App\Models\Order;
use App\Services\Storefront\CheckoutAccess;
use App\Services\Storefront\CheckoutPricing;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class StorefrontCheckoutController extends Controller
{
    public function capabilities()
    {
        return response()->json(['checkoutEnabled' => CheckoutAccess::enabled(), 'testMode' => true,
            'paymentTestConfigured' => str_starts_with(config('storefront.razorpay_key', ''), 'rzp_test_') && strlen(config('storefront.razorpay_secret', '')) >= 8,
            'codEnabled' => (bool) config('storefront.cod_enabled', false)]);
    }

    public function quote(Request $request, CheckoutService $service)
    {
        CheckoutAccess::assertEnabled();
        $data = $request->validate(['address_id' => 'required|integer|min:1', 'coupon_code' => 'nullable|string|max:100', 'payment_method' => 'required|in:cod,online']);

        return response()->json(['status' => 'success', 'quote' => $service->quote($request->user()->customer_id, $data['address_id'], $data['coupon_code'] ?? '', $data['payment_method'])]);
    }

    public function place(Request $request, CheckoutService $service)
    {
        CheckoutAccess::assertEnabled();
        $request->merge(['idempotency_key' => $request->header('Idempotency-Key') ?? $request->input('idempotency_key')]);
        $data = $request->validate(['quote_id' => 'required|uuid', 'idempotency_key' => 'required|uuid']);

        return response()->json(['status' => 'success', 'message' => 'Order recorded. Online payment is pending until capture is verified.',
            'order' => $service->place($request->user()->customer_id, $data['quote_id'], $data['idempotency_key'])], 201);
    }

    public function coupon(Request $request, CheckoutPricing $pricing)
    {
        $data = $request->validate(['coupon_code' => 'required|string|max:100']);
        $result = DB::transaction(function () use ($request, $data, $pricing) {
            app(CheckoutService::class)->lockCustomer($request->user()->customer_id);

            return $pricing->calculate($request->user()->customer_id, null, $data['coupon_code'], 'online', true);
        }, 5);

        return response()->json(['status' => 'success', 'message' => 'Coupon is valid. Delivery is quoted at checkout.', 'coupon_code' => $data['coupon_code'],
            'subtotal_minor' => $result['subtotal_minor'], 'discount_minor' => $result['discount_minor'], 'product_total_minor' => $result['subtotal_minor'] - $result['discount_minor']]);
    }

    public function history(Request $request)
    {
        $orders = Order::disableCache()->with(['items.product.primaryImage', 'items.returned.salesReturn', 'invoice'])->where('customer_id', $request->user()->customer_id)->latest()->get();

        return response()->json(['status' => 'success', 'data' => OrderHistoryResource::collection($orders), 'product_image_path' => url('/storage').'/']);
    }

    public function detail(Request $request, int $order, CheckoutService $service)
    {
        return response()->json(['status' => 'success', 'order' => $service->order($request->user()->customer_id, $order)]);
    }

    public function cancel(Request $request, int $order, CheckoutService $service)
    {
        CheckoutAccess::assertEnabled();
        $service->release($request->user()->customer_id, $order);

        return response()->json(['status' => 'success', 'message' => 'Reservation cancelled. Any late captured payment will require refund reconciliation.']);
    }

    public function eligibility(Request $request, int $order, ReturnService $service)
    {
        return response()->json(['status' => 'success', 'eligibility' => $service->eligibility($request->user()->customer_id, $order)]);
    }

    public function tracking(Request $request, int $order)
    {
        $record = DB::table('orders')->where('order_id', $order)->where('customer_id', $request->user()->customer_id)->first();
        abort_unless($record, 404, 'Order not found.');

        return response()->json(['status' => 'success', 'tracking' => ['order_status' => $record->status, 'tracking_number' => $record->delivery_tracking_id,
            'carrier_integration' => null, 'tracking_url' => null,
            'events' => DB::table('order_status_logs')->where('order_id', $order)->orderBy('changed_at')->get(['status', 'changed_at'])
                ->map(fn ($event) => ['status' => $event->status, 'changed_at' => \Carbon\Carbon::parse($event->changed_at)->toIso8601String()])]]);
    }
}
