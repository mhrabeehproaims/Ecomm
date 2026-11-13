<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RefundService
{
    public function __construct(private PaymentGateway $gateway) {}

    // Operator-only console entry point. There is no public/customer refund endpoint.
    public function request(int $returnId): array
    {
        CheckoutAccess::assertGateway();
        [$refund, $create] = DB::transaction(function () use ($returnId) {
            $r = DB::table('sales_returns')->where('return_id', $returnId)->first();
            abort_unless($r, 404, 'Return not found.');
            app(CheckoutService::class)->lockCustomer($r->customer_id, false);
            DB::table('orders')->where('order_id', $r->order_id)->lockForUpdate()->first();
            $r = DB::table('sales_returns')->where('return_id', $returnId)->lockForUpdate()->first();
            $old = DB::table('storefront_refunds')->where('return_id', $returnId)->first();
            if ($old) {
                return [$old, false];
            }
            abort_unless($r->status === 'collected' && $r->return_type === 'refund'
                && DB::table('storefront_return_requests')->where('return_id', $returnId)->exists(), 409, 'Only a collected, approved storefront refund request can be refunded.');
            $attempt = DB::table('storefront_payment_attempts')->where('order_id', $r->order_id)->where('state', 'captured')->first();
            abort_unless($attempt, 409, 'No verified captured test payment is available. COD/manual refunds require a separately audited workflow.');
            $amount = Money::minor($r->total_amount);
            $used = DB::table('storefront_refunds')->where('order_id', $r->order_id)->sum('amount_minor');
            abort_unless($amount >= 100 && $amount + $used <= $attempt->amount_minor, 409, 'Refund must be at least INR 1 and within the verified remaining payment amount.');
            $id = (string) Str::uuid();
            DB::table('storefront_refunds')->insert(['id' => $id, 'return_id' => $returnId, 'order_id' => $r->order_id,
                'provider_payment_id' => $attempt->provider_payment_id, 'amount_minor' => $amount, 'state' => 'creating', 'created_at' => now(), 'updated_at' => now()]);

            return [DB::table('storefront_refunds')->where('id', $id)->first(), true];
        }, 5);
        if ($create) {
            try {
                $response = $this->gateway->createRefund($refund->provider_payment_id, ['amount' => $refund->amount_minor, 'receipt' => $refund->id,
                    'notes' => ['storefront_return_id' => (string) $returnId]]);
                abort_unless(is_string($response['id'] ?? null) && str_starts_with($response['id'], 'rfnd_')
                    && ($response['payment_id'] ?? '') === $refund->provider_payment_id && ($response['amount'] ?? null) === (int) $refund->amount_minor
                    && ($response['currency'] ?? '') === 'INR', 409, 'Refund binding mismatch.');
                $this->bind($refund, $response['id']);
            } catch (\Throwable $e) {
                DB::table('storefront_refunds')->where('id', $refund->id)->whereNull('provider_refund_id')->update(['state' => 'indeterminate', 'updated_at' => now()]);
                abort(503, 'Refund initiation needs reconciliation. It will not be submitted again automatically.');
            }
        }
        $current = DB::table('storefront_refunds')->where('id', $refund->id)->first();
        if ($current->provider_refund_id) {
            return $this->reconcile($current->provider_refund_id);
        }

        return ['status' => $current->state, 'message' => 'Manual provider reconciliation is required; no duplicate refund was sent.'];
    }

    public function reconcile(string $providerId): array
    {
        CheckoutAccess::assertGateway();
        $refund = DB::table('storefront_refunds')->where('provider_refund_id', $providerId)->first();
        abort_unless($refund, 409, 'Refund mapping is unavailable; reconcile the original request before retrying.');
        $remote = $this->gateway->refund($providerId);
        abort_unless(($remote['id'] ?? '') === $providerId && ($remote['payment_id'] ?? '') === $refund->provider_payment_id
            && ($remote['amount'] ?? null) === (int) $refund->amount_minor && ($remote['currency'] ?? '') === 'INR', 409, 'Refund amount or payment mismatch.');
        $state = $remote['status'] ?? '';
        abort_unless(in_array($state, ['pending', 'processed', 'failed']), 409, 'Unknown refund status.');

        return DB::transaction(function () use ($refund, $state) {
            $customer = DB::table('orders')->where('order_id', $refund->order_id)->value('customer_id');
            app(CheckoutService::class)->lockCustomer($customer, false);
            DB::table('orders')->where('order_id', $refund->order_id)->lockForUpdate()->first();
            $row = DB::table('storefront_refunds')->where('id', $refund->id)->lockForUpdate()->first();
            if ($row->state !== 'processed') {
                DB::table('storefront_refunds')->where('id', $row->id)->update(['state' => $state, 'updated_at' => now()]);
            }
            if ($state === 'processed' || $row->state === 'processed') {
                app(ReturnService::class)->transition($row->return_id, 'refunded');
                $amount = (int) DB::table('storefront_refunds')->where('order_id', $row->order_id)->where('state', 'processed')->sum('amount_minor');
                $attempt = DB::table('storefront_payment_attempts')->where('order_id', $row->order_id)->first();
                $paymentStatus = $amount >= $attempt->amount_minor ? 'refunded' : 'partially_refunded';
                DB::table('orders')->where('order_id', $row->order_id)->update(['payment_status' => $paymentStatus, 'updated_at' => now()]);
                DB::table('payments')->where('id', $attempt->legacy_payment_id)->update(['status' => $paymentStatus, 'updated_at' => now()]);
                Outbox::record('refund.processed:'.$row->id, 'cache.changed', ['order_id' => $row->order_id]);
            }

            return ['status' => $row->state === 'processed' ? 'processed' : $state, 'return_id' => $row->return_id];
        }, 5);
    }

    public function recover(int $returnId, string $providerId): array
    {
        CheckoutAccess::assertGateway();
        $row = DB::table('storefront_refunds')->where('return_id', $returnId)->first();
        abort_unless($row, 404, 'Refund intent not found.');
        $remote = $this->gateway->refund($providerId);
        abort_unless(($remote['id'] ?? '') === $providerId && ($remote['payment_id'] ?? '') === $row->provider_payment_id
            && ($remote['amount'] ?? null) === (int) $row->amount_minor && ($remote['currency'] ?? '') === 'INR'
            && ($remote['receipt'] ?? '') === $row->id, 409, 'The provider refund does not match the original refund intent.');
        $this->bind($row, $providerId);

        return $this->reconcile($providerId);
    }

    private function bind(object $row, string $providerId): void
    {
        DB::transaction(function () use ($row, $providerId) {
            $current = DB::table('storefront_refunds')->where('id', $row->id)->lockForUpdate()->first();
            if ($current->provider_refund_id) {
                abort_unless($current->provider_refund_id === $providerId, 409, 'Refund is already bound to another provider record.');

                return;
            }
            DB::table('storefront_refunds')->where('id', $row->id)->update(['provider_refund_id' => $providerId, 'state' => 'pending', 'updated_at' => now()]);
        }, 5);
    }
}
