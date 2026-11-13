<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ReplacementService
{
    // Called only from the reviewed admin transition, inside its locked transaction.
    public function create(object $return): int
    {
        CheckoutAccess::assertEnabled();
        abort_unless($return->return_type === 'replacement' && $return->status === 'collected', 409, 'A collected replacement request is required.');
        if ($return->reissued_order_id) {
            return $return->reissued_order_id;
        }
        $original = DB::table('storefront_checkouts')->where('order_id', $return->order_id)->first();
        abort_unless($original, 409, 'This legacy order has no verified delivery-address snapshot. An approved delivery address is required before replacement.');
        $snapshot = json_decode($original->breakdown, true);
        $items = DB::table('sales_return_items')->where('sales_return_id', $return->return_id)->orderBy('product_id')->get();
        abort_if($items->isEmpty(), 409, 'Replacement items are unavailable.');
        $lines = [];
        foreach ($items as $item) {
            $product = DB::table('products')->where('product_id', $item->product_id)->lockForUpdate()->first();
            abort_unless($product && (int) $product->status === 1 && $product->stock >= $item->quantity, 409, 'Replacement stock is unavailable.');
            $saved = collect($snapshot['items'])->firstWhere('product_id', $item->product_id);
            abort_unless($saved, 409, 'Original product snapshot is unavailable.');
            $lines[] = [...$saved, 'quantity' => (int) $item->quantity, 'unit_minor' => 0, 'line_minor' => 0, 'discount_minor' => 0,
                'total_minor' => 0, 'tax_minor' => 0, 'base_minor' => 0, 'cart' => []];
        }
        $data = [...$snapshot, 'payment_method' => 'replacement', 'items' => $lines, 'subtotal_minor' => 0, 'discount_minor' => 0,
            'tax_minor' => 0, 'shipping_minor' => 0, 'shipping_tax_minor' => 0, 'total_minor' => 0, 'coupon' => null];
        $now = now();
        $orderId = DB::table('orders')->insertGetId(['reference_order_id' => $return->order_id, 'customer_id' => $return->customer_id,
            'total_amount' => 0, 'discount' => 0, 'total_tax_amount' => 0, 'status' => 'reissued', 'payment_method' => 'replacement',
            'payment_status' => 'not_required', 'paid_at' => null, 'delivery_state' => $snapshot['address']['state'], 'delivery_charge' => 0,
            'created_at' => $now, 'updated_at' => $now], 'order_id');
        foreach ($lines as $line) {
            $changed = DB::table('products')->where('product_id', $line['product_id'])->where('status', 1)->where('stock', '>=', $line['quantity'])->decrement('stock', $line['quantity']);
            abort_unless($changed === 1, 409, 'Replacement stock changed.');
            DB::table('order_items')->insert(['order_id' => $orderId, 'product_id' => $line['product_id'], 'quantity' => $line['quantity'],
                'actual_price' => Money::decimal($line['actual_minor']), 'price' => 0, 'total' => 0, 'created_at' => $now, 'updated_at' => $now]);
            DB::table('stock_management')->insert(['product_id' => $line['product_id'], 'quantity' => $line['quantity'], 'stock_status' => 'out_stock',
                'notes' => 'Replacement order #'.$orderId.' for return #'.$return->return_id, 'created_at' => $now, 'updated_at' => $now]);
            Outbox::record('replacement.stock:'.$orderId.':'.$line['product_id'], 'stock.changed', ['product_id' => $line['product_id']]);
        }
        $quoteId = (string) Str::uuid();
        DB::table('storefront_quotes')->insert(['id' => $quoteId, 'customer_id' => $return->customer_id, 'fingerprint' => hash('sha256', json_encode($data)),
            'payload' => json_encode($data), 'expires_at' => $now, 'consumed_order_id' => $orderId, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('storefront_checkouts')->insert(['order_id' => $orderId, 'customer_id' => $return->customer_id, 'quote_id' => $quoteId,
            'idempotency_key' => (string) Str::uuid(), 'state' => 'committed', 'expires_at' => $now, 'committed_at' => $now,
            'address_snapshot' => $original->address_snapshot, 'breakdown' => json_encode($data), 'created_at' => $now, 'updated_at' => $now]);
        DB::table('sales_returns')->where('return_id', $return->return_id)->update(['reissued_order_id' => $orderId, 'updated_at' => $now]);
        DB::table('order_status_logs')->insert(['order_id' => $orderId, 'status' => 'reissued', 'remarks' => 'Replacement for approved return #'.$return->return_id,
            'changed_by' => null, 'changed_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        Outbox::record('order.created:'.$orderId, 'order.created', ['order_id' => $orderId]);
        Outbox::record('order.confirmed:'.$orderId, 'order.confirmed', ['order_id' => $orderId]);

        return $orderId;
    }
}
