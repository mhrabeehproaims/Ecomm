<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;

final class CodAccounting
{
    public function record(int $orderId, int $amount, string $reference, string $evidence, int $actor, bool $remittance = false): array
    {
        CheckoutAccess::assertEnabled();
        abort_unless(config('storefront.cod_enabled') && $amount > 0 && strlen($evidence) >= 3 && strlen($evidence) <= 200
            && \Illuminate\Support\Str::isUuid($reference) && DB::table('users')->where('id', $actor)->exists(), 422, 'Valid operator, receipt reference and positive amount are required.');

        return DB::transaction(function () use ($orderId, $amount, $reference, $evidence, $actor, $remittance) {
            $initial = DB::table('orders')->where('order_id', $orderId)->first();
            abort_unless($initial, 404);
            app(CheckoutService::class)->lockCustomer($initial->customer_id, false);
            $order = DB::table('orders')->where('order_id', $orderId)->lockForUpdate()->first();
            $checkout = DB::table('storefront_checkouts')->where('order_id', $orderId)->first();
            abort_unless($order->payment_method === 'cod' && $checkout?->state === 'committed' && $order->status === 'delivered'
                && DB::table('order_status_logs')->where('order_id', $orderId)->where('status', 'delivered')->exists(), 409, 'A delivered, committed COD order is required.');
            $kind = $remittance ? 'cod_remittance' : 'cod_collection';
            $receipt = DB::table('storefront_financial_entries')->where('order_id', $orderId)->where('kind', $kind)->where('evidence', $evidence)->lockForUpdate()->first();
            if ($receipt && $receipt->reference !== 'cod:'.$reference) {
                abort_unless((int) $receipt->amount_minor === $amount, 409, 'This receipt was already recorded with a different amount.');

                return FinancialLedger::balance($orderId);
            }
            $existing = DB::table('storefront_financial_entries')->where('reference', 'cod:'.$reference)->lockForUpdate()->first();
            if ($existing) {
                FinancialLedger::record($orderId, 'cod:'.$reference, $kind, $amount, $actor, $evidence);

                return FinancialLedger::balance($orderId);
            }
            $collected = (int) DB::table('storefront_financial_entries')->where('order_id', $orderId)->where('kind', 'cod_collection')->lockForUpdate()->get()->sum('amount_minor');
            $remitted = (int) DB::table('storefront_financial_entries')->where('order_id', $orderId)->where('kind', 'cod_remittance')->lockForUpdate()->get()->sum('amount_minor');
            $due = Money::minor($order->total_amount);
            abort_unless($due === json_decode($checkout->breakdown, true)['total_minor'], 409, 'The accepted COD order total requires reconciliation.');
            abort_unless($remittance ? $amount <= $collected - $remitted : $amount <= $due - $collected, 409, 'The receipt exceeds the unrecorded COD balance.');
            FinancialLedger::record($orderId, 'cod:'.$reference, $kind, $amount, $actor, $evidence);
            if (! $remittance && $collected + $amount === $due) {
                DB::table('orders')->where('order_id', $orderId)->update(['payment_status' => 'paid', 'paid_at' => now(), 'updated_at' => now()]);
            }
            Outbox::record('cod:'.$reference, 'cache.changed', ['order_id' => $orderId]);

            return FinancialLedger::balance($orderId);
        }, 5);
    }
}
