<?php

namespace App\Http\Controllers;

use App\Events\NotifyOrderPlaced;
use App\Events\OrderPlaceEvent;
use App\Http\Resources\OrderHistoryResource;
use App\Models\Cart;
use App\Models\CheckoutEvent;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\StockManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    // ////////Without coupon code
    // public function checkout()
    // {
    //     $user = Auth::user();

    //     // Get cart items
    //     $cartItems = Cart::with('product')
    //         ->where('user_id', $user->customer_id)
    //         ->get();

    //     if ($cartItems->isEmpty()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Cart is empty',
    //         ], 400);
    //     }

    //     foreach ($cartItems as $item) {
    //         if ($item->product->stock < $item->quantity) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Product '{$item->product->product_name}' has only {$item->product->stock} left in stock.",
    //             ], 400);
    //         }
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Calculate totals
    //         $subtotal = $cartItems->sum(fn($item) => $item->product->default_price_value * $item->quantity);
    //         $tax = 0;
    //         $discount = 0;
    //         $totalTax_amount = 0;
    //         $base_price = 0;

    //         // Create Order
    //         $order = Order::create([
    //             'customer_id' => $user->customer_id,
    //             'total_tax_amount' => $totalTax_amount,
    //             'discount' => $discount,
    //             'total_amount' => $subtotal,
    //             'status' => 'ordered',   //    'pending','confirmed','processing','shipped','delivered','completed','cancelled','refunded'
    //             'payment_method' => '',
    //             'payment_status' => 'unpaid',
    //             'paid_at' => Carbon::now(),
    //             // 'payment_status' => $request->payment_status,
    //             // 'paid_at' => $request->payment_status === 'paid' ? Carbon::now() : null,
    //         ]);

    //         // Save Order Items
    //         foreach ($cartItems as $item) {
    //             $products->push([
    //                 'product_id' => $item->product_id,
    //                 'quantity'   => $item->quantity,
    //             ]);
    //             if ($item->product->taxSchedule->tax_percentage) {
    //                 $tax_value = $this->reverseTaxCalc($item->product->taxSchedule->tax_percentage, ($item->product->default_price_value * $item->quantity));
    //                 $tax_amount = $tax_value['tax_amount'] ?? 0;
    //                 $base_price = $tax_value['base_price'] ?? 0;
    //                 $totalTax_amount += $tax_amount;
    //             }

    //             OrderItem::create([
    //                 'order_id' => $order->order_id,
    //                 'product_id' => $item->product_id,
    //                 'quantity' => $item->quantity,
    //                 'price' => $item->product->default_price_value,
    //                 'total' => $item->product->default_price_value * $item->quantity,
    //                 'tax_percent' => $item->product->taxSchedule->tax_percentage ?? 0,
    //                 'cgst_percent' => $item->product->taxSchedule->cgst,
    //                 'sgst_percent' => $item->product->taxSchedule->sgst,
    //                 'igst_percent' => $item->product->taxSchedule->igst,
    //                 'amount_wo_tax' => $base_price,
    //                 'cgst_amount' =>  $tax_amount != 0 ? $tax_amount / 2 : 0,
    //                 'sgst_amount' => $tax_amount != 0 ? $tax_amount / 2 : 0,
    //                 'igst_amount' => $tax_amount != 0 ? $tax_amount : 0,
    //             ]);
    //         }

    //         $order->update(['total_tax_amount' => $totalTax_amount]);

    //         // 🔹 Simulate Payment Gateway (replace with real API like Stripe/Razorpay)
    //         $paymentStatus = 'success'; // or failed
    //         $transactionId = 'TXN' . time(); // mock transaction ID

    //         // $payment = Payment::create([
    //         //     'order_id' => $order->id,
    //         //     'gateway' => $request->gateway ?? 'test_gateway',
    //         //     'transaction_id' => $transactionId,
    //         //     'amount' => $total,
    //         //     'currency' => 'INR',
    //         //     'status' => $paymentStatus,
    //         //     'response' => json_encode(['mock' => 'payment response']),
    //         // ]);

    //         // Update order payment status
    //         // $order->update([
    //         //     'payment_status' => $paymentStatus === 'success' ? 'paid' : 'failed',
    //         //     'status' => $paymentStatus === 'success' ? 'confirmed' : 'pending',
    //         // ]);

    //         // Clear cart if payment success
    //         // if ($paymentStatus === 'success') {
    //         //     Cart::where('user_id', $user->id)->delete();

    //         foreach ($products as $product) {
    //             $prod = Product::select('product_id', 'stock')->where('product_id', $product['product_id'])->first();
    //             if ($prod) {
    //                 $prod->decrement('stock', $product['quantity']);
    //                 StockManagement::create([
    //                     'product_id' => $product['product_id'],
    //                     'quantity' =>  $product['quantity'],
    //                     'stock_status' => 'out_stock',
    //                     'notes' => 'Sold to ' . $user->first_name . ' ' . $user->last_name,
    //                 ]);
    //             }
    //         }
    //         // }
    //         $this->updateStatus($order->order_id, 'pending', 0);
    //         // $user->carts()->delete();
    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Checkout completed',
    //             'order' => $order,
    //             'payment' => '', //$payment
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Checkout failed',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /////////////With coupon code
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'coupon_code' => 'nullable|string',
            'total_amount' => 'required_with:coupon_code|numeric',
            'delivery_state' => 'required|string',
        ]);
        $couponCode = $request->input('coupon_code');
        $total_amount = $request->input('total_amount');
        $delivery_state = $request->input('delivery_state');
        $actual_total_amount = $request->input('actual_total_amount');
        $sessionId = $request->input('session_id');

        $today = now();

        // 🛒 Get user's cart items
        $cartItems = Cart::with('product.taxSchedule')
            ->where('user_id', $user->customer_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty',
            ], 400);
        }

        // 🔍 Validate stock
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Product '{$item->product->product_name}' has only {$item->product->stock} left in stock.",
                ], 400);
            }
        }

        // -------------------------------------------
        // 🎟️ Coupon Code Validation and Discount Logic
        // -------------------------------------------
        $coupon_discount = 0;
        $coupon = null;
        // $subtotal = $cartItems->sum(fn($item) => $item->product->default_price_value * $item->quantity);
        // 🔥 SUBTOTAL NOW USES OFFER PRICE IF AVAILABLE
        $subtotal = $cartItems->sum(function ($item) {
            $price = $this->getOfferPriceIfApplicable($item->product);
            return $price * $item->quantity;
        });

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if (!$coupon) {
                return response()->json(['status' => 'error', 'message' => 'Invalid coupon code'], 400);
            }

            if ($coupon->is_active !== 1) {
                return response()->json(['status' => 'error', 'message' => 'This coupon is inactive'], 400);
            }

            if ($today->lt($coupon->start_date) || $today->gt($coupon->end_date)) {
                return response()->json(['status' => 'error', 'message' => 'This coupon has expired or not yet started'], 400);
            }

            $totalUsed = CouponUsage::where('coupon_id', $coupon->id)->count();

            if ($coupon->max_uses && $totalUsed >= $coupon->max_uses) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This coupon usage limit has been reached'
                ], 400);
            }

            // Check per-user limit
            $totalUsedByUser = CouponUsage::where('customer_id', $user->customer_id)
                ->where('coupon_id', $coupon->id)
                ->count();

            if ($coupon->max_uses_per_user && $totalUsedByUser >= $coupon->max_uses_per_user) {
                return response()->json(['status' => 'error', 'message' => 'You have already used this coupon'], 400);
            }

            if ($coupon->min_order_amount && $actual_total_amount <= $coupon->min_order_amount) {
                return response()->json(['status' => 'error', 'message' => "The minimum order amount is {$coupon->min_order_amount} for this coupon"], 400);
            }
        }

        // -------------------------------------------
        // 💰 Order Calculation
        // -------------------------------------------
        DB::beginTransaction();

        try {
            $totalTax_amount = 0;
            $base_price = 0;
            $coupon_discount = 0;

            // Apply coupon discount
            if ($coupon) {
                if ($coupon->type === 'percentage') {
                    $coupon_discount = ($subtotal * $coupon->value) / 100;
                } else {
                    $coupon_discount = $coupon->value;
                }
            }

            $discountedTotal = max(0, $subtotal - $coupon_discount);

            // Prepare Order Data
            $order_data = [
                'customer_id' => $user->customer_id,
                'total_tax_amount' => $totalTax_amount,
                'discount' => $coupon_discount,
                'total_amount' => $discountedTotal,
                'status' => 'ordered',
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => $coupon?->value,
                'coupon_discount_type' => $coupon?->type,
                'payment_method' => $request->payment_method ?? 'cod',
                'payment_status' => $request->payment_method == 'online' ? 'unpaid' : null,
                'paid_at' => now(),
                'delivery_state' => $delivery_state
            ];

            $order = Order::create($order_data);

            // Save Order Items
            foreach ($cartItems as $item) {
                $product = $item->product;

                // 🔥 Step 1: Determine offer price
                $offerPrice = $this->getOfferPriceIfApplicable($product);
                $actualPrice = $product->default_price_value;
                $unitPrice = $offerPrice ?? $actualPrice;

                // Step 2: total for this item before coupon
                $itemTotal = $unitPrice * $item->quantity;

                // Step 3: proportional coupon discount
                $share = $subtotal > 0 ? ($itemTotal / $subtotal) : 0;
                $itemCouponDiscount = round($coupon_discount * $share, 2);

                // Step 4: discounted item total
                $finalItemTotal = $itemTotal - $itemCouponDiscount;

                // Step 5: discounted price per unit
                $finalUnitPrice = $finalItemTotal / $item->quantity;

                // Step 6: tax calculation
                $taxSchedule = $product->taxSchedule;
                $taxData = $this->reverseTaxCalc(
                    $taxSchedule->tax_percentage ?? 0,
                    $finalItemTotal
                );

                $tax_amount = $taxData['tax_amount'] ?? 0;
                $base_price = $taxData['base_price'] ?? 0;
                $totalTax_amount += $tax_amount;

                $offer_details = $product->offer;

                if ($offer_details) {
                    $offer_applied = true;
                    $offerDetails = [
                        'offer_id' => $offer_details->id ?? '',
                        'title' => $offer_details->offer_type ?? '',
                        'discount_type' => $offer_details->discount_type ?? '',
                        'discount_value' => $offer_details->discount_value ?? '',
                        'discount_from' => $offer_details->discount_from ?? '',
                        'discount_to' => $offer_details->end_date ?? '',
                    ];
                } else {
                    $offer_applied = false;
                    $offerDetails = null;
                }

                // Save Order Item
                $newOrder = OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $product->product_id,
                    'quantity' => $item->quantity,
                    'actual_price' => $actualPrice,
                    'offer_price' => $offerPrice,
                    'price' => $finalUnitPrice,
                    'total' => $finalItemTotal,
                    'offer_applied' => $offer_applied,
                    'offer_details' => $offerDetails ? json_encode($offerDetails) : null,
                    'tax_percent' => $taxSchedule->tax_percentage ?? 0,
                    'cgst_percent' => $taxSchedule->cgst ?? 0,
                    'sgst_percent' => $taxSchedule->sgst ?? 0,
                    'igst_percent' => $taxSchedule->igst ?? 0,
                    'amount_wo_tax' => $base_price,
                    'cgst_amount' => $tax_amount / 2,
                    'sgst_amount' => $tax_amount / 2,
                    'igst_amount' => $tax_amount,
                ]);
            }

            $order->update(['total_tax_amount' => $totalTax_amount]);

            // -------------------------------------------
            // Record Coupon Usage
            // -------------------------------------------
            // if ($coupon) {
            //     CouponUsage::create([
            //         'coupon_id' => $coupon->id,
            //         'customer_id' => $user->customer_id,
            //         'order_id' => $order->order_id,
            //         'discount_amount' => $coupon_discount,
            //         'discount_type' => $coupon->type,
            //         'used_at' => now(),
            //     ]);

            //     // Increment coupon usage count
            //     $coupon->increment('usage_count');
            // }

            if ($coupon) {
                $couponUsage = CouponUsage::firstOrCreate(
                    [
                        'coupon_id' => $coupon->id,
                        'customer_id' => $user->customer_id,
                    ],
                    [
                        'order_id' => $order->order_id,
                        'discount_amount' => $coupon_discount,
                        'discount_type' => $coupon->type,
                        'used_at' => now(),
                        'usage_count' => 0,  // initial value if new
                    ]
                );

                // Increment the usage_count
                $couponUsage->increment('usage_count');
            }

            // -------------------------------------------
            // Stock Management
            // -------------------------------------------
            foreach ($cartItems as $item) {
                $product = $item->product;

                $product->decrement('stock', $item->quantity);  // decrease quantity from product table
                StockManagement::create([  // Add quantity history to stocks table
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'stock_status' => 'out_stock',
                    'notes' => 'Sold to ' . $user->first_name . ' ' . $user->last_name,
                ]);
            }

            $this->updateStatus($order->order_id, 'ordered', 0);
            $user->carts()->delete();
            if ($sessionId) {
                CheckoutEvent::where('session_id', $sessionId)->delete();
            }
            DB::commit();

            event(new NotifyOrderPlaced($user->first_name, $order->total_amount, $order));  // Admin notification
            if (filled($user->email)) {
                event(new OrderPlaceEvent($user->email, $user->full_name, $newOrder, $cartItems, $discountedTotal));  // Mail
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Checkout completed successfully',
                'order' => $order,
                'discount_applied' => $coupon_discount,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getOfferPriceIfApplicable($product)
    {
        if (!$product->offer)
            return $product->default_price_value;

        $offer = $product->offer;

        if ($offer->discount_type === 'percentage') {
            return round($product->default_price_value - ($product->default_price_value * $offer->discount_value / 100), 2);
        }

        if ($offer->discount_type === 'fixed') {
            return max($product->default_price_value - $offer->discount_value, 0);
        }

        return null;
    }

    public function reverseTaxCalc($tax_percentage, $total_amount)
    {
        // Method: 1
        // $base = ($total_amount*100)/(100+$tax_percentage);
        // $tax = $total_amount - $base;

        // Method: 2
        $base = $total_amount / (1 + ($tax_percentage / 100));
        $tax = $total_amount - $base;

        return [
            'base_price' => round($base, 2),
            'tax_amount' => round($tax, 2),
        ];
    }

    public function updateStatus($orderId, string $newStatus, int $check)
    {
        $order = Order::findOrFail($orderId);

        if ($check == 1) {
            $order->update(['status' => $newStatus]);
        }

        OrderStatusLog::create([
            'order_id' => $order->order_id,
            'status' => $newStatus,
            'changed_by' => $check ? 1 : $order->customer_id,
            'remarks' => $check ? 'Status updated by Admin' : 'Customer Placed Order',
        ]);

        return response()->json(['message' => 'Order status updated successfully']);
    }

    public function purchase_history()
    {
        $user = Auth::user();
        $orders = Order::with(['items.product.PrimaryImage', 'invoice'])->where('customer_id', $user->customer_id)->get();

        return response()->json([
            'status' => 'success',
            'data' => OrderHistoryResource::collection($orders),
            'product_image_path' => 'https://vantara.datacubeglobal.com/storage/',
        ]);
    }
}
