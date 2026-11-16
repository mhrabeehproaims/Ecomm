<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;

final class FinancialLedger
{
    public static function record(int $order, string $reference, string $kind, int $amount, ?int $actor = null, ?string $evidence = null): void
    {
        abort_unless($amount >= 0, 409, 'Invalid financial amount.');
        DB::table('storefront_financial_entries')->insertOrIgnore(['order_id' => $order, 'reference' => $reference, 'kind' => $kind,
            'amount_minor' => $amount, 'currency' => 'INR', 'actor_id' => $actor, 'evidence' => $evidence, 'created_at' => now(), 'updated_at' => now()]);
        $old = DB::table('storefront_financial_entries')->where('reference', $reference)->lockForUpdate()->first();
        abort_unless($old && (int) $old->order_id === $order && $old->kind === $kind && (int) $old->amount_minor === $amount && $old->actor_id == $actor && $old->evidence === $evidence, 409, 'Financial reference was reused with different evidence.');
    }

    public static function balance(int $order): array
    {
        $rows = DB::table('storefront_financial_entries')->where('order_id', $order)->lockForUpdate()->get();
        $captured = (int) $rows->whereIn('kind', ['capture', 'cod_collection'])->sum('amount_minor');
        $refunded = (int) $rows->where('kind', 'refund')->sum('amount_minor');

        return ['currency' => 'INR', 'received_minor' => $captured, 'refunded_minor' => $refunded,
            'net_received_minor' => $captured - $refunded, 'bank_settlement_verified' => false];
    }

    public static function closeReleasedRefund(int $order, int $refunded, int $captured): void
    {
        if ($refunded !== $captured) {
            return;
        }
        $released = DB::table('storefront_checkouts')->where('order_id', $order)->whereNotNull('released_at')->first();
        if ($released && $released->state === 'refund_required') {
            DB::table('storefront_checkouts')->where('order_id', $order)->update(['state' => 'refunded', 'updated_at' => now()]);
            OperationalAlert::resolve('refund_required', (string) $order);
        }
    }
}
