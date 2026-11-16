<?php

namespace App\Services\Storefront;

use App\Models\Order;
use App\Models\Product;
use App\Models\SalesReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class Outbox
{
    public static function record(string $key, string $kind, array $payload): void
    {
        DB::table('storefront_outbox')->insertOrIgnore(['id' => hash('sha256', $key), 'kind' => $kind, 'payload' => json_encode($payload),
            'state' => 'pending', 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
        DB::afterCommit(function () {
            try {
                (new Order)->flushCache();
                (new SalesReturn)->flushCache();
                // Network effects are dispatched by supervised queue/scheduler workers.
            } catch (\Throwable $e) { /* Durable pending rows remain available to the dispatcher. */
            }
        });
    }

    public function dispatch(): int
    {
        CheckoutAccess::assertEnabled();
        $done = 0;
        $ids = DB::table('storefront_outbox')->where(fn ($q) => $q->where('state', 'pending')->orWhere(fn ($q) => $q->where('state', 'processing')->where('updated_at', '<', now()->subMinutes(5))))
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))->orderBy('updated_at')->limit(100)->pluck('id');
        foreach ($ids as $id) {
            $token = (string) \Illuminate\Support\Str::uuid();
            $row = DB::transaction(function () use ($id, $token) {
                $row = DB::table('storefront_outbox')->where('id', $id)->lockForUpdate()->first();
                if ($row->state === 'done' || ($row->state === 'processing' && now()->subMinutes(5)->lessThan($row->updated_at))) {
                    return null;
                }
                if ($row->next_attempt_at && now()->lessThan($row->next_attempt_at)) {
                    return null;
                }
                DB::table('storefront_outbox')->where('id', $id)->update(['state' => 'processing', 'lease_token' => $token, 'attempts' => $row->attempts + 1, 'updated_at' => now()]);

                return $row;
            }, 5);
            if (! $row) {
                continue;
            }
            try {
                $data = json_decode($row->payload, true);
                (new Order)->flushCache();
                (new SalesReturn)->flushCache();
                if ($row->kind === 'stock.changed') {
                    $product = Product::find($data['product_id']);
                    if ($product) {
                        app(\App\Observers\ProductObserver::class)->updated($product);
                    }
                } elseif ($row->kind === 'order.created') {
                    $order = Order::disableCache()->with('customer')->findOrFail($data['order_id']);
                    event(new \App\Events\NotifyOrderPlaced($order->customer->first_name, $order->total_amount, $order));
                } elseif ($row->kind === 'order.shipped') {
                    $order = Order::disableCache()->with(['customer', 'items.product'])->findOrFail($data['order_id']);
                    event(new \App\Events\orderShippedEvent($order, route('download.invoice', $data['invoice'])));
                } elseif ($row->kind === 'order.delivered') {
                    $order = Order::disableCache()->with(['customer', 'items.product'])->findOrFail($data['order_id']);
                    $snapshot = json_decode(DB::table('storefront_checkouts')->where('order_id', $order->order_id)->value('breakdown'), true);
                    if ($email = $snapshot['customer_snapshot']['email'] ?? null) {
                        Mail::to($email)->send(new \App\Mail\OrderDeliveredMail($order));
                    }
                } elseif ($row->kind === 'order.confirmed') {
                    $order = Order::disableCache()->with('customer')->findOrFail($data['order_id']);
                    $checkout = DB::table('storefront_checkouts')->where('order_id', $order->order_id)->first();
                    $snapshot = $checkout ? json_decode($checkout->breakdown, true) : null;
                    $email = $checkout ? ($snapshot['customer_snapshot']['email'] ?? null) : $order->customer->email;
                    if ($email) {
                        Mail::raw('Your Vanthara test order #'.$order->order_id.' is recorded. Total INR '.$order->total_amount.'. No live payment was processed.',
                            fn ($mail) => $mail->to($email)->subject('Vanthara test order #'.$order->order_id));
                    }
                } elseif ($row->kind === 'order.shipped') {
                    $order = Order::disableCache()->with(['customer', 'items.product'])->findOrFail($data['order_id']);
                    event(new \App\Events\orderShippedEvent($order, route('download.invoice', $data['invoice'])));
                } elseif ($row->kind === 'return.created') {
                    $r = SalesReturn::disableCache()->with(['customer', 'items'])->findOrFail($data['return_id']);
                    event(new \App\Events\sendReturnNotification(['return' => $r, 'customer' => $r->customer->first_name, 'order_id' => $r->order_id,
                        'subtotal' => $r->total_amount, 'item_id' => $r->items->first()?->product_id]));
                }
                DB::table('storefront_outbox')->where('id', $id)->where('lease_token', $token)->update(['state' => 'done', 'lease_token' => null, 'last_error' => null, 'updated_at' => now()]);
                OperationalAlert::resolve('outbox_effect_failed', $id);
                $done++;
            } catch (\Throwable $e) {
                DB::table('storefront_outbox')->where('id', $id)->where('lease_token', $token)->update(['state' => 'pending', 'lease_token' => null,
                    'next_attempt_at' => now()->addSeconds(min(3600, 30 * (2 ** min(7, $row->attempts)))), 'last_error' => substr(get_class($e), 0, 200), 'updated_at' => now()]);
                OperationalAlert::record('outbox_effect_failed', $id);
            }
        }

        return $done;
    }
}
