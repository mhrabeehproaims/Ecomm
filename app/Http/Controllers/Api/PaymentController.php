<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function index()
    {
        return view('RazorPay.index');
    }

    public function payment(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'order_id' => 'required|integer',
            'email' => 'nullable|email',
            'phone' => 'required',
            'description' => 'nullable|string',
        ]);

        $api = new Api(
            config('razorpay.key'),
            config('razorpay.secret')
        );

        $cutomer_order = Order::where('order_id', $request->order_id)->first();

        if (!$cutomer_order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found',
            ]);
        }

        if ($cutomer_order->payment_status === 'paid') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order already paid',
            ]);
        }

        if ($cutomer_order->customer_id !== Auth::id()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized order access',
            ], 403);
        }

        $payment = Payment::where('order_id', $request->order_id)
            ->latest()
            ->first();

        if (!$payment || $payment->status == 'failed') {
            $amount = $cutomer_order->total_amount;

            // Create Razorpay Order
            $order = $api->order->create([
                'receipt' => 'rcpt_' . uniqid(),
                'amount' => $amount * 100,  // paise
                'currency' => 'INR',
            ]);
        } elseif ($payment->status == 'created') {
            return response()->json([
                'message' => 'Payment is in pending state',
                'razorpayOrderId' => $payment->razorpay_order_id,
            ]);
        } elseif ($payment->status == 'paid') {
            return response()->json([
                'message' => 'Already Paid'
            ]);
        }

        $payment = Payment::create([
            'user_id' => Auth::user()->customer_id,  // change it////////////////////
            'order_id' => $request->order_id,
            'razorpay_order_id' => $order['id'],
            'amount' => $amount,
            'status' => 'created',
            'description' => $request->description ?? null,
        ]);

        // For blade view page testing
        // return view('RazorPay.payment', [
        //     'razorpayOrderId' => $order['id'],
        //     'name'            => $request->name,
        //     'email'           => $request->email ?? null,
        //     'phone'           => $request->phone,
        //     'amount'          => $order['amount'],
        //     'currency'        => $order['currency'],
        //     'description'     => $request->description ?? null,
        //     'razorpayKey'     => config('razorpay.key'),
        // ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'razorpayOrderId' => $order['id'],
            'name' => $request->name,
            'email' => $request->email ?? null,
            'phone' => $request->phone,
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'description' => $request->description ?? null,
            'razorpayKey' => config('razorpay.key'),
        ]);
    }

    public function success(Request $request)
    {
        if (
            empty($request->razorpay_payment_id) ||
            empty($request->razorpay_order_id) ||
            empty($request->razorpay_signature)
        ) {
            return back()->with('error', 'Payment was not completed.');
        }

        $api = new Api(
            config('razorpay.key'),
            config('razorpay.secret')
        );

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);

            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if (!$payment) {
                return back()->with('error', 'Invalid payment record.');
            }

            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'paid'
            ]);

            Order::where('order_id', $payment->order_id)->update([
                'payment_method' => 'razorpay',
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function failed(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

        if (!$payment) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment record not found.',
                ], 404);
            }
            return back()->with('error', 'Payment record not found.');
        }

        $payment->update([
            'status' => 'failed',
            'razorpay_payment_id' => $request->razorpay_payment_id ?? $payment->razorpay_payment_id,
            'meta' => ['reason' => $request->reason ?? null]
        ]);

        Order::where('order_id', $payment->order_id)->update([
            'payment_status' => 'failed',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'failed',
                'message' => $request->reason ?? 'Payment failed or cancelled.',
            ]);
        }

        return back()->with('error', $request->reason ?? 'Payment failed or cancelled.');
    }
}
