<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class FinancialOperations
{
    public function run(int $limit = 100): array
    {
        CheckoutAccess::assertEnabled();
        $result = ['expired_reviewed' => 0, 'webhooks_processed' => 0, 'payments_reviewed' => 0, 'outbox_dispatched' => 0, 'errors' => 0];
        [$result['expired_reviewed'], $result['errors']] = $this->expireReserved($limit);
        try {
            $result['outbox_dispatched'] = app(Outbox::class)->dispatch();
        } catch (\Throwable $error) {
            OperationalAlert::record('outbox_dispatch', 'batch');
            $result['errors']++;
        }
        $gatewayConfigured = str_starts_with(config('storefront.razorpay_key', ''), 'rzp_test_') && strlen(config('storefront.razorpay_secret', '')) >= 8;
        if ($gatewayConfigured) {
            $result['webhooks_processed'] = app(WebhookProcessor::class)->drain($limit);
            $ids = DB::table('storefront_payment_attempts')->where(fn ($q) => $q->whereNull('next_reconcile_at')->orWhere('next_reconcile_at', '<=', now()))->where(fn ($q) => $q->whereNull('reconcile_lease_expires_at')->orWhere('reconcile_lease_expires_at', '<=', now()))->orderBy('next_reconcile_at')->limit($limit)->pluck('id');
            foreach ($ids as $id) {
                $token = (string) Str::uuid();
                $row = DB::transaction(function () use ($id, $token) {
                    $row = DB::table('storefront_payment_attempts')->where('id', $id)->lockForUpdate()->first();
                    if (($row->reconcile_lease_expires_at && now()->lessThan($row->reconcile_lease_expires_at)) || ($row->next_reconcile_at && now()->lessThan($row->next_reconcile_at))) {
                        return null;
                    }
                    DB::table('storefront_payment_attempts')->where('id', $id)->update(['reconcile_lease' => $token, 'reconcile_lease_expires_at' => now()->addMinutes(10)]);

                    return $row;
                }, 5);
                if (! $row) {
                    continue;
                }
                try {
                    app(PaymentService::class)->reconcile($row->order_id);
                    DB::table('storefront_payment_attempts')->where('id', $id)->where('reconcile_lease', $token)->update(['reconcile_lease' => null, 'reconcile_lease_expires_at' => null, 'next_reconcile_at' => now()->addMinutes(5), 'reconcile_failures' => 0]);
                    OperationalAlert::resolve('payment_reconciliation', (string) $row->order_id);
                    $result['payments_reviewed']++;
                } catch (\Throwable $error) {
                    DB::table('storefront_payment_attempts')->where('id', $id)->where('reconcile_lease', $token)->update(['reconcile_lease' => null, 'reconcile_lease_expires_at' => null, 'next_reconcile_at' => now()->addSeconds(min(3600, 30 * (2 ** min(7, $row->reconcile_failures)))), 'reconcile_failures' => $row->reconcile_failures + 1]);
                    OperationalAlert::record('payment_reconciliation', (string) $row->order_id);
                    $result['errors']++;
                }
            }
            foreach (DB::table('storefront_refunds')->whereNotIn('state', ['processed'])->orderBy('updated_at')->limit($limit)->get() as $refund) {
                try {
                    if ($refund->provider_refund_id) {
                        app(ProviderRefunds::class)->observe($refund->provider_refund_id);
                    } else {
                        foreach (app(PaymentGateway::class)->paymentRefunds($refund->provider_payment_id) as $remote) {
                            if (($remote['receipt'] ?? null) === $refund->id) {
                                app(RefundService::class)->recoverIntent($refund->id, $remote['id']);
                            }
                        }
                    }
                    OperationalAlert::resolve('refund_reconciliation', $refund->id);
                } catch (\Throwable $error) {
                    OperationalAlert::record('refund_reconciliation', $refund->id);
                    $result['errors']++;
                }
                DB::table('storefront_refunds')->where('id', $refund->id)->update(['updated_at' => now()]);
            }
        } elseif (DB::table('storefront_payment_attempts')->exists() || DB::table('storefront_webhook_events')->whereNull('processed_at')->exists()) {
            OperationalAlert::record('gateway_not_configured', 'test_gateway');
        }
        if ($gatewayConfigured) {
            OperationalAlert::resolve('gateway_not_configured', 'test_gateway');
        }

        return $result;
    }

    public function expireReserved(int $limit = 100): array
    {
        CheckoutAccess::assertEnabled();
        $done = 0;
        $errors = 0;
        $rows = DB::table('storefront_checkouts')->where('state', 'reserved')->where('expires_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('next_expiry_attempt_at')->orWhere('next_expiry_attempt_at', '<=', now()))->orderBy('expires_at')->limit($limit)->get();
        foreach ($rows as $row) {
            try {
                app(CheckoutService::class)->release($row->customer_id, $row->order_id, true);
                OperationalAlert::resolve('reservation_expiry', (string) $row->order_id);
                $done++;
            } catch (\Throwable $error) {
                if (DB::table('storefront_checkouts')->where('order_id', $row->order_id)->value('state') !== 'reserved') {
                    continue;
                }
                DB::table('storefront_checkouts')->where('order_id', $row->order_id)->update(['next_expiry_attempt_at' => now()->addMinutes(5)]);
                OperationalAlert::record('reservation_expiry', (string) $row->order_id);
                $errors++;
            }
        }

        return [$done, $errors];
    }
}
