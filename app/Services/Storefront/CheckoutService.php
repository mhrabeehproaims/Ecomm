<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CheckoutService
{
    public function quote(int $customer, int $address, string $coupon, string $method): array
    {
        CheckoutAccess::assertEnabled();
        $payload = DB::transaction(function () use ($customer, $address, $coupon, $method) {
            $this->lockCustomer($customer);

            return app(CheckoutPricing::class)->calculate($customer, $address, $coupon, $method, true);
        }, 5);
        $id = (string) Str::uuid();
        $expires = now()->addMinutes(config('storefront.quote_minutes'));
        DB::table('storefront_quotes')->insert(['id' => $id, 'customer_id' => $customer, 'payload' => json_encode($payload),
            'fingerprint' => $this->fingerprint($payload), 'expires_at' => $expires, 'created_at' => now(), 'updated_at' => now()]);

        return ['quote_id' => $id, 'expires_at' => $expires->toIso8601String(), ...$payload];
    }

    public function place(int $customer, string $quoteId, string $key): array
    {
        CheckoutAccess::assertEnabled();

        return DB::transaction(function () use ($customer, $quoteId, $key) {
            $this->lockCustomer($customer);
            $previous = DB::table('storefront_checkouts')->where('customer_id', $customer)->where('idempotency_key', $key)->first();
            if ($previous) {
                abort_unless($previous->quote_id === $quoteId, 409, 'This submission key belongs to a different quote.');

                return $this->order($customer, $previous->order_id);
            }
            $quote = DB::table('storefront_quotes')->where('customer_id', $customer)->where('id', $quoteId)->lockForUpdate()->first();
            abort_unless($quote, 404, 'Quote not found.');
            if ($quote->consumed_order_id) {
                return $this->order($customer, $quote->consumed_order_id);
            }
            abort_if(now()->greaterThanOrEqualTo($quote->expires_at), 409, 'This quote expired. Review a new quote before ordering.');
            $pending = DB::table('storefront_checkouts')->where('customer_id', $customer)->where('state', 'reserved')->first();
            abort_if($pending, 409, 'An unpaid order is already reserved. Open your order history to pay or cancel it.');
            $old = json_decode($quote->payload, true, flags: JSON_THROW_ON_ERROR);
            $fresh = app(CheckoutPricing::class)->calculate($customer, $old['address']['address_id'], $old['coupon']['code'] ?? '', $old['payment_method'], true);
            abort_unless(hash_equals($quote->fingerprint, $this->fingerprint($fresh)), 409, 'Your bag, address, prices or delivery quote changed. Review a new quote.');
            $now = now();
            $orderId = DB::table('orders')->insertGetId(['customer_id' => $customer, 'total_amount' => Money::decimal($fresh['total_minor']),
                'discount' => Money::decimal($fresh['discount_minor']), 'total_tax_amount' => Money::decimal($fresh['tax_minor']),
                'status' => 'ordered', 'payment_method' => $fresh['payment_method'], 'payment_status' => 'unpaid', 'paid_at' => null,
                'delivery_state' => $fresh['address']['state'], 'delivery_charge' => Money::decimal($fresh['shipping_minor']),
                'coupon_id' => $fresh['coupon']['id'] ?? null, 'coupon_code' => $fresh['coupon']['code'] ?? null,
                'coupon_discount' => $fresh['coupon']['value'] ?? null, 'coupon_discount_type' => $fresh['coupon']['type'] ?? null,
                'created_at' => $now, 'updated_at' => $now], 'order_id');
            foreach ($fresh['items'] as $line) {
                // Conditional decrement remains safe even on drivers without row locks.
                $changed = DB::table('products')->where('product_id', $line['product_id'])->where('status', 1)->where('stock', '>=', $line['quantity'])
                    ->decrement('stock', $line['quantity'], ['updated_at' => $now]);
                if ($changed !== 1) {
                    throw ValidationException::withMessages(['stock' => 'Stock changed. Please review a new quote.']);
                }
                $local = $fresh['address']['state'] === config('storefront.origin_state');
                $cgst = $local && $line['tax_basis_points'] > 0 ? Money::ratio($line['tax_minor'], $line['cgst_basis_points'], $line['tax_basis_points']) : 0;
                DB::table('order_items')->insert(['order_id' => $orderId, 'product_id' => $line['product_id'], 'quantity' => $line['quantity'],
                    'actual_price' => Money::decimal($line['actual_minor']), 'price' => Money::decimal(Money::ratio($line['total_minor'], 1, $line['quantity'])),
                    'discountPerUnit' => Money::decimal(Money::ratio($line['discount_minor'], 1, $line['quantity'])),
                    'total' => Money::decimal($line['total_minor']), 'tax_percent' => Money::decimal($line['tax_basis_points']),
                    'cgst_percent' => Money::decimal($line['cgst_basis_points']), 'sgst_percent' => Money::decimal($line['sgst_basis_points']),
                    'igst_percent' => Money::decimal($line['igst_basis_points']),
                    'amount_wo_tax' => Money::decimal($line['base_minor']), 'cgst_amount' => Money::decimal($cgst),
                    'sgst_amount' => Money::decimal($local ? $line['tax_minor'] - $cgst : 0), 'igst_amount' => Money::decimal($local ? 0 : $line['tax_minor']),
                    'offer_applied' => $line['unit_minor'] < $line['actual_minor'], 'created_at' => $now, 'updated_at' => $now]);
                $this->stockLog($orderId, $line['product_id'], $line['quantity'], 'out_stock', 'Reserved');
            }
            DB::table('storefront_checkouts')->insert(['order_id' => $orderId, 'customer_id' => $customer, 'quote_id' => $quoteId,
                'idempotency_key' => $key, 'state' => 'reserved', 'expires_at' => now()->addMinutes(config('storefront.reservation_minutes')),
                'address_snapshot' => json_encode($fresh['address']), 'breakdown' => json_encode($fresh), 'coupon_id' => $fresh['coupon']['id'] ?? null,
                'created_at' => $now, 'updated_at' => $now]);
            DB::table('storefront_quotes')->where('id', $quoteId)->update(['consumed_order_id' => $orderId, 'updated_at' => $now]);
            DB::table('order_status_logs')->insert(['order_id' => $orderId, 'status' => 'ordered', 'changed_by' => null,
                'remarks' => 'Storefront order created', 'changed_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
            if ($fresh['payment_method'] === 'cod') {
                $this->commit($orderId);
            }
            Outbox::record('order.created:'.$orderId, 'order.created', ['order_id' => $orderId]);

            return $this->order($customer, $orderId);
        }, 5);
    }

    public function lockCustomer(int $customer, bool $active = true): void
    {
        $user = DB::table('customers')->where('customer_id', $customer)->lockForUpdate()->first();
        abort_unless($user && (! $active || (int) $user->status === 1), 403, 'This account is inactive.');
    }

    public function order(int $customer, int $orderId): array
    {
        $order = DB::table('orders')->where('customer_id', $customer)->where('order_id', $orderId)->first();
        abort_unless($order, 404, 'Order not found.');
        $checkout = DB::table('storefront_checkouts')->where('order_id', $orderId)->first();

        return [...(array) $order, 'checkout_state' => $checkout?->state,
            'address_snapshot' => $checkout ? json_decode($checkout->address_snapshot, true) : null,
            'quote' => $checkout ? json_decode($checkout->breakdown, true) : null, 'reservation_expires_at' => $checkout?->expires_at];
    }

    // Caller holds the checkout/order locks and an enclosing transaction.
    public function commit(int $orderId): void
    {
        $checkout = DB::table('storefront_checkouts')->where('order_id', $orderId)->lockForUpdate()->first();
        if (! $checkout || $checkout->state !== 'reserved') {
            return;
        }
        $data = json_decode($checkout->breakdown, true);
        if ($data['coupon']) {
            DB::table('coupon_usages')->insert(['coupon_id' => $data['coupon']['id'], 'customer_id' => $checkout->customer_id, 'order_id' => $orderId,
                'discount_amount' => intdiv($data['discount_minor'], 100), 'discount_amount_minor' => $data['discount_minor'],
                'discount_type' => $data['coupon']['type'], 'usage_count' => 1, 'used_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach ($data['items'] as $line) {
            foreach ($line['cart'] as $entry) {
                $cart = DB::table('carts')->where('cart_id', $entry['id'])->where('user_id', $checkout->customer_id)->where('product_id', $line['product_id'])->lockForUpdate()->first();
                if (! $cart) {
                    continue;
                }
                if ($cart->quantity <= $entry['quantity']) {
                    DB::table('carts')->where('cart_id', $cart->cart_id)->delete();
                } else {
                    DB::table('carts')->where('cart_id', $cart->cart_id)->decrement('quantity', $entry['quantity']);
                }
            }
        }
        DB::table('storefront_checkouts')->where('order_id', $orderId)->update(['state' => 'committed', 'committed_at' => now(), 'updated_at' => now()]);
        Outbox::record('order.confirmed:'.$orderId, 'order.confirmed', ['order_id' => $orderId]);
    }

    public function release(int $customer, int $orderId, bool $expired = false): void
    {
        DB::transaction(function () use ($customer, $orderId, $expired) {
            $this->lockCustomer($customer, false);
            $order = DB::table('orders')->where('customer_id', $customer)->where('order_id', $orderId)->lockForUpdate()->first();
            $checkout = DB::table('storefront_checkouts')->where('order_id', $orderId)->lockForUpdate()->first();
            abort_unless($order && $checkout, 404, 'Order not found.');
            if ($checkout->state === 'released') {
                return;
            }
            abort_unless($checkout->state === 'reserved' && $order->payment_status !== 'paid', 409, 'This order cannot be cancelled.');
            if ($expired && now()->lessThan($checkout->expires_at)) {
                return;
            }
            $data = json_decode($checkout->breakdown, true);
            foreach ($data['items'] as $line) {
                DB::table('products')->where('product_id', $line['product_id'])->increment('stock', $line['quantity'], ['updated_at' => now()]);
                $this->stockLog($orderId, $line['product_id'], $line['quantity'], 'in_stock', 'Reservation released');
            }
            DB::table('storefront_checkouts')->where('order_id', $orderId)->update(['state' => 'released', 'released_at' => now(), 'updated_at' => now()]);
            DB::table('orders')->where('order_id', $orderId)->update(['status' => 'rejected', 'payment_status' => $expired ? 'expired' : 'cancelled', 'updated_at' => now()]);
            Outbox::record('order.released:'.$orderId, 'cache.changed', ['order_id' => $orderId]);
        }, 5);
    }

    private function stockLog(int $order, int $product, int $quantity, string $status, string $reason): void
    {
        DB::table('stock_management')->insert(['product_id' => $product, 'quantity' => $quantity, 'stock_status' => $status,
            'notes' => $reason.' for storefront order #'.$order, 'created_at' => now(), 'updated_at' => now()]);
        Outbox::record('stock:'.$order.':'.$product.':'.$status, 'stock.changed', ['product_id' => $product]);
    }

    private function fingerprint(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
