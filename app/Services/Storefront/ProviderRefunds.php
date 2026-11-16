<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;

final class ProviderRefunds
{
    public function __construct(private PaymentGateway $gateway) {}

    public function observe(string $providerId): array
    {
        CheckoutAccess::assertGateway();
        $remote = $this->gateway->refund($providerId);
        abort_unless(($remote['id'] ?? '') === $providerId && preg_match('/^rfnd_[A-Za-z0-9]+$/D', $providerId)
            && is_int($remote['amount'] ?? null) && $remote['amount'] > 0 && ($remote['currency'] ?? '') === 'INR'
            && in_array($remote['status'] ?? '', ['pending', 'processed', 'failed']), 409, 'Refund evidence is invalid.');
        $attempt = DB::table('storefront_payment_attempts')->where('provider_payment_id', $remote['payment_id'] ?? '')->where('state', 'captured')->first();
        abort_unless($attempt, 409, 'Refund capture mapping is not ready.');
        DB::transaction(function () use ($attempt, $remote, $providerId) {
            $customer = DB::table('orders')->where('order_id', $attempt->order_id)->value('customer_id');
            app(CheckoutService::class)->lockCustomer($customer, false);
            DB::table('orders')->where('order_id', $attempt->order_id)->lockForUpdate()->first();
            $old = DB::table('storefront_provider_refunds')->where('provider_refund_id', $providerId)->lockForUpdate()->first();
            abort_if($old && ((int) $old->order_id !== (int) $attempt->order_id || (int) $old->amount_minor !== $remote['amount'] || $old->provider_payment_id !== $remote['payment_id']), 409, 'Refund identity changed.');
            $processed = (int) DB::table('storefront_provider_refunds')->where('order_id', $attempt->order_id)->where('state', 'processed')->where('provider_refund_id', '!=', $providerId)->lockForUpdate()->get()->sum('amount_minor');
            abort_unless($processed + $remote['amount'] <= $attempt->amount_minor, 409, 'Refund evidence exceeds captured amount.');
            $state = $old?->state === 'processed' ? 'processed' : $remote['status'];
            DB::table('storefront_provider_refunds')->updateOrInsert(['provider_refund_id' => $providerId], ['order_id' => $attempt->order_id,
                'provider_payment_id' => $remote['payment_id'], 'amount_minor' => $remote['amount'], 'state' => $state, 'created_at' => $old?->created_at ?? now(), 'updated_at' => now()]);
            if ($state === 'processed') {
                FinancialLedger::record($attempt->order_id, 'refund:'.$providerId, 'refund', $remote['amount']);
                $sum = (int) DB::table('storefront_provider_refunds')->where('order_id', $attempt->order_id)->where('state', 'processed')->lockForUpdate()->get()->sum('amount_minor');
                $status = $sum === (int) $attempt->amount_minor ? 'refunded' : 'partially_refunded';
                DB::table('storefront_payment_attempts')->where('id', $attempt->id)->update(['refunded_minor' => $sum, 'updated_at' => now()]);
                DB::table('orders')->where('order_id', $attempt->order_id)->update(['payment_status' => $status, 'updated_at' => now()]);
                DB::table('payments')->where('id', $attempt->legacy_payment_id)->update(['status' => $status, 'updated_at' => now()]);
                FinancialLedger::closeReleasedRefund($attempt->order_id, $sum, $attempt->amount_minor);
                Outbox::record('provider.refund:'.$providerId, 'cache.changed', ['order_id' => $attempt->order_id]);
            }
        }, 5);
        $mapped = DB::table('storefront_refunds')->where('provider_refund_id', $providerId)->first();
        if (! $mapped && is_string($remote['receipt'] ?? null)) {
            $intent = DB::table('storefront_refunds')->where('id', $remote['receipt'])->whereNull('provider_refund_id')->first();
            if ($intent) {
                app(RefundService::class)->recoverIntent($intent->id, $providerId);
                $mapped = $intent;
            }
        }
        if ($mapped) {
            app(RefundService::class)->reconcile($providerId);
        } else {
            OperationalAlert::record('external_refund_review', $providerId);
        }
        if ($remote['status'] === 'failed') {
            OperationalAlert::record('refund_failed', $providerId);
        }

        return ['status' => DB::table('storefront_provider_refunds')->where('provider_refund_id', $providerId)->value('state'),
            'order_id' => $attempt->order_id, 'amount_minor' => $remote['amount']];
    }

    public function synchronize(object $attempt, int $expected): void
    {
        abort_unless($expected >= 0 && $expected <= $attempt->amount_minor, 409, 'Invalid refunded payment total.');
        $refunds = $this->gateway->paymentRefunds($attempt->provider_payment_id);
        $sum = 0;
        $seen = [];
        foreach ($refunds as $refund) {
            abort_unless(is_string($refund['id'] ?? null) && ! isset($seen[$refund['id']]), 409, 'Duplicate or invalid provider refund evidence.');
            $seen[$refund['id']] = true;
            abort_unless(($refund['payment_id'] ?? '') === $attempt->provider_payment_id, 409, 'Refund belongs to another payment.');
            $result = $this->observe($refund['id']);
            if ($result['status'] === 'processed') {
                $sum += $result['amount_minor'];
            }
        }
        abort_unless($sum === $expected, 409, 'Provider refunded balance is not yet reconciled.');
    }
}
