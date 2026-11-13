<?php

namespace App\Services\Storefront;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ReturnService
{
    public function policy(int $product): array
    {
        $p = DB::table('products')->where('product_id', $product)->first();
        if (! $p) {
            return ['refund' => 0, 'replacement' => 0];
        }
        $category = DB::table('categories')->where('categ_id', $p->category_id)->value('return_days');
        $subcategory = $p->sub_category_id ? DB::table('sub_categories')->where('subCat_id', $p->sub_category_id)->value('return_days') : null;
        $windows = array_filter([$category, $subcategory], fn ($v) => $v !== null);
        $result = [];
        foreach (['refund', 'replacement'] as $type) {
            $types = $type === 'refund' ? ['refund', 'refund_or_exchange'] : ['exchange', 'refund_or_exchange'];
            $policies = DB::table('return_policies')->where('is_active', 1)->whereIn('return_type', $types)->pluck('return_days')->all();
            $result[$type] = $policies ? max(0, min([...$policies, ...$windows])) : 0;
        }

        return $result;
    }

    public function eligibility(int $customer, int $orderId): array
    {
        $order = DB::table('orders')->where('order_id', $orderId)->where('customer_id', $customer)->first();
        abort_unless($order, 404, 'Order not found.');
        // Use the first recorded delivery, never extend the window with a repeated status update.
        $firstDelivery = DB::table('order_status_logs')->where('order_id', $orderId)->where('status', 'delivered')->min('changed_at');
        $firstFollowup = DB::table('delivery_followups')->where('order_id', $orderId)->where('delivery_status', 'delivered')->min('created_at');
        $dates = array_filter([$firstDelivery, $firstFollowup]);
        sort($dates);
        $date = $dates[0] ?? null;
        $checkout = DB::table('storefront_checkouts')->where('order_id', $orderId)->first();
        $snapshot = $checkout ? json_decode($checkout->breakdown, true)['items'] : [];
        $items = [];
        foreach (DB::table('order_items')->where('order_id', $orderId)->get() as $item) {
            $used = DB::table('sales_return_items as i')->join('sales_returns as r', 'r.return_id', '=', 'i.sales_return_id')
                ->where('i.order_item_id', $item->order_item_id)->where('r.status', '!=', 'rejected')->sum('i.quantity');
            $saved = collect($snapshot)->firstWhere('product_id', $item->product_id);
            $policy = $saved['return_policy'] ?? $this->policy($item->product_id);
            $types = [];
            $deadlines = [];
            foreach ($policy as $type => $days) {
                $deadline = $date ? CarbonImmutable::parse($date)->addDays((int) $days) : null;
                $deadlines[$type] = $deadline?->toIso8601String();
                if ($order->status === 'delivered' && in_array($order->payment_status, ['paid', 'partially_refunded']) && $date && $days > 0
                    && now()->greaterThanOrEqualTo($date) && now()->lessThanOrEqualTo($deadline) && $used < $item->quantity) {
                    $types[] = $type;
                }
            }
            $items[] = ['order_item_id' => $item->order_item_id, 'remaining_quantity' => max(0, (int) ($item->quantity - $used)),
                'allowed_types' => $types, 'deadlines' => $deadlines, 'eligible' => count($types) > 0];
        }

        return ['order_id' => $orderId, 'delivered_at' => $date, 'items' => $items];
    }

    public function request(int $customer, array $data): int
    {
        return DB::transaction(function () use ($customer, $data) {
            app(CheckoutService::class)->lockCustomer($customer);
            $order = DB::table('orders')->where('customer_id', $customer)->where('order_id', $data['order_id'])->lockForUpdate()->first();
            abort_unless($order, 404, 'Order not found.');
            $fingerprint = hash('sha256', json_encode($data));
            $key = $data['request_key'] ?? (string) Str::uuid();
            $old = DB::table('storefront_return_requests')->where('customer_id', $customer)->where('request_key', $key)->first();
            if ($old) {
                abort_unless(hash_equals($old->fingerprint, $fingerprint), 409, 'This return submission key was already used.');

                return $old->return_id;
            }
            $eligibility = collect($this->eligibility($customer, $order->order_id)['items'])->keyBy('order_item_id');
            $lines = [];
            $seen = [];
            $total = 0;
            foreach ($data['items'] as $line) {
                $query = DB::table('order_items')->where('order_id', $order->order_id)->where('product_id', $line['product_id']);
                if (! empty($line['order_item_id'])) {
                    $query->where('order_item_id', $line['order_item_id']);
                } elseif ((clone $query)->count() !== 1) {
                    throw ValidationException::withMessages(['items' => 'Select a specific purchased order item.']);
                }
                $item = $query->lockForUpdate()->first();
                abort_unless($item, 404, 'Order item not found.');
                $rule = $eligibility[$item->order_item_id];
                if (isset($seen[$item->order_item_id]) || ! $rule['eligible'] || ! in_array($data['return_type'], $rule['allowed_types']) || $line['quantity'] > $rule['remaining_quantity']) {
                    throw ValidationException::withMessages(['items' => 'Return quantity, delivery/payment status or policy deadline does not permit this request.']);
                }
                $seen[$item->order_item_id] = true;
                $already = (int) $item->quantity - $rule['remaining_quantity'];
                // Allocate from the purchased line total, avoiding rounded per-unit over-refunds.
                $minor = Money::minor($item->total);
                $amount = Money::ratio($minor, $already + $line['quantity'], $item->quantity) - Money::ratio($minor, $already, $item->quantity);
                $total += $amount;
                $lines[] = ['product_id' => $item->product_id, 'order_item_id' => $item->order_item_id, 'quantity' => $line['quantity'],
                    'price' => Money::decimal(Money::ratio($amount, 1, $line['quantity'])), 'subtotal' => Money::decimal($amount)];
            }
            $id = DB::table('sales_returns')->insertGetId(['order_id' => $order->order_id, 'customer_id' => $customer, 'return_date' => now(),
                'status' => 'requested', 'total_amount' => Money::decimal($total), 'return_type' => $data['return_type'],
                'return_reason' => $data['return_reason'], 'product_condition' => $data['product_condition'], 'notes' => $data['remarks'] ?? null,
                'created_at' => now(), 'updated_at' => now()], 'return_id');
            foreach ($lines as $line) {
                DB::table('sales_return_items')->insert([...$line, 'sales_return_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('storefront_return_requests')->insert(['return_id' => $id, 'customer_id' => $customer, 'request_key' => $key,
                'fingerprint' => $fingerprint, 'created_at' => now(), 'updated_at' => now()]);
            Outbox::record('return.created:'.$id, 'return.created', ['return_id' => $id]);

            return $id;
        }, 5);
    }

    public function transition(int $id, string $target): void
    {
        DB::transaction(function () use ($id, $target) {
            $initial = DB::table('sales_returns')->where('return_id', $id)->first();
            abort_unless($initial, 404, 'Return not found.');
            app(CheckoutService::class)->lockCustomer($initial->customer_id, false);
            DB::table('orders')->where('order_id', $initial->order_id)->lockForUpdate()->first();
            $return = DB::table('sales_returns')->where('return_id', $id)->lockForUpdate()->first();
            abort_unless($return, 404, 'Return not found.');
            if ($return->status === $target) {
                return;
            }
            $allowed = ['requested' => ['accepted', 'rejected'], 'accepted' => ['picked_up', 'rejected'], 'picked_up' => ['collected'], 'collected' => ['refunded', 'replaced']];
            abort_unless(in_array($target, $allowed[$return->status] ?? []), 409, 'This return status transition is not allowed.');
            if ($target === 'refunded') {
                abort_unless(DB::table('storefront_refunds')->where('return_id', $id)->where('state', 'processed')->exists(), 409, 'A verified processed refund is required. Use the test refund/reconciliation workflow.');
            }
            if ($target === 'replaced') {
                if (! $return->reissued_order_id) {
                    $return->reissued_order_id = app(ReplacementService::class)->create($return);
                }
                abort_unless(DB::table('orders')->where('order_id', $return->reissued_order_id)
                    ->where('customer_id', $return->customer_id)->where('reference_order_id', $return->order_id)->where('status', '!=', 'rejected')->exists(), 409, 'A real linked replacement order is required.');
            }
            DB::table('sales_returns')->where('return_id', $id)->update(['status' => $target, 'updated_at' => now()]);
            Outbox::record('return.changed:'.$id.':'.$target, 'cache.changed', ['return_id' => $id]);
            // Returned stock is never restocked automatically; replacement stock is checked separately.
        }, 5);
    }
}
