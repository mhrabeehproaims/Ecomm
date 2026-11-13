<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Storefront\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return view('RazorPay.index');
    }

    public function payment(Request $request, PaymentService $service)
    {
        $data = $request->validate(['order_id' => 'required|integer|min:1']);

        return $this->respond(fn () => $service->start($request->user()->customer_id, $data['order_id']));
    }

    public function success(Request $request, PaymentService $service)
    {
        $data = $request->validate(['razorpay_order_id' => 'required|string|max:100', 'razorpay_payment_id' => 'required|string|max:100',
            'razorpay_signature' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/D']]);

        return $this->respond(fn () => $service->confirm($request->user()->customer_id, $data));
    }

    public function failed(Request $request, PaymentService $service)
    {
        $data = $request->validate(['razorpay_order_id' => 'required|string|max:100', 'razorpay_payment_id' => 'required|string|max:100']);

        return $this->respond(fn () => $service->failed($request->user()->customer_id, $data['razorpay_order_id'], $data['razorpay_payment_id']));
    }

    public function webhook(Request $request, PaymentService $service)
    {
        try {
            return $this->respond(fn () => $service->webhook($request->getContent(), (string) $request->header('X-Razorpay-Signature'), (string) $request->header('X-Razorpay-Event-Id')));
        } catch (\JsonException $e) {
            return response()->json(['message' => 'Invalid webhook body.'], 422);
        }
    }

    private function respond(callable $operation)
    {
        try {
            $result = $operation();

            return response()->json($result, ($result['status'] ?? '') === 'pending' ? 202 : 200);
        } catch (\Illuminate\Http\Client\HttpClientException $e) {
            return response()->json(['message' => 'Payment provider verification is unavailable. Check order status before retrying.'], 503);
        }
    }
}
