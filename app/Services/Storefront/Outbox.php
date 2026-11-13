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
                app(self::class)->dispatch();
            } catch (\Throwable $e) { /* Durable pending rows remain available to the dispatcher. */
            }
        });
    }

    public function dispatch(): int
    {
        CheckoutAccess::assertEnabled();
        $done = 0;
        $ids = DB::table('storefront_outbox')->where('state', 'pending')->orWhere(fn ($q) => $q->where('state', 'processing')->where('updated_at', '<', now()->subMinutes(5)))->limit(100)->pluck('id');
        foreach ($ids as $id) {
            $row = DB::transaction(function () use ($id) {
                $row = DB::table('storefront_outbox')->where('id', $id)->lockForUpdate()->first();
                if ($row->state === 'done' || ($row->state === 'processing' && now()->subMinutes(5)->lessThan($row->updated_at))) {
                    return null;
                }
                DB::table('storefront_outbox')->where('id', $id)->update(['state' => 'processing', 'attempts' => $row->attempts + 1, 'updated_at' => now()]);

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
                } elseif ($row->kind === 'order.confirmed') {
                    $order = Order::disableCache()->with('customer')->findOrFail($data['order_id']);
                    if ($order->customer->email) {
                        Mail::raw('Your Vanthara test order #'.$order->order_id.' is recorded. Total INR '.$order->total_amount.'. No live payment was processed.',
                            fn ($mail) => $mail->to($order->customer->email)->subject('Vanthara test order #'.$order->order_id));
                    }
                } elseif ($row->kind === 'return.created') {
                    $r = SalesReturn::disableCache()->with(['customer', 'items'])->findOrFail($data['return_id']);
                    event(new \App\Events\sendReturnNotification(['return' => $r, 'customer' => $r->customer->first_name, 'order_id' => $r->order_id,
                        'subtotal' => $r->total_amount, 'item_id' => $r->items->first()?->product_id]));
                }
                DB::table('storefront_outbox')->where('id', $id)->update(['state' => 'done', 'last_error' => null, 'updated_at' => now()]);
                $done++;
            } catch (\Throwable $e) {
                DB::table('storefront_outbox')->where('id', $id)->update(['state' => 'pending', 'last_error' => substr(get_class($e), 0, 200), 'updated_at' => now()]);
            }
        }

        return $done;
    }
}
