<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function start(int $customer, int $orderId): array
    {
        CheckoutAccess::assertGateway();
        [$attempt, $create] = DB::transaction(function () use ($customer, $orderId) {
            app(CheckoutService::class)->lockCustomer($customer);
            $order = DB::table('orders')->where('customer_id', $customer)->where('order_id', $orderId)->lockForUpdate()->first();
            $checkout = DB::table('storefront_checkouts')->where('order_id', $orderId)->lockForUpdate()->first();
            abort_unless($order && $checkout, 404, 'Payable storefront order not found.');
            abort_unless($order->payment_method === 'online' && $order->payment_status !== 'paid' && $order->status !== 'rejected'
                && $checkout->state === 'reserved' && now()->lessThan($checkout->expires_at), 409, 'This order is no longer payable.');
            $existing = DB::table('storefront_payment_attempts')->where('order_id', $orderId)->first();
            if ($existing) {
                abort_unless($existing->provider_order_id, 409, 'Payment initiation is awaiting reconciliation. Do not create another order.');

                return [$existing, false];
            }
            $amount = json_decode($checkout->breakdown, true)['total_minor'];
            abort_unless(Money::minor($order->total_amount) === $amount && $amount >= 100, 409, 'Order amount requires reconciliation.');
            $payment = DB::table('payments')->insertGetId(['user_id' => $customer, 'order_id' => $orderId, 'amount' => Money::decimal($amount),
                'currency' => 'INR', 'status' => 'created', 'meta' => json_encode(['storefront' => true, 'mode' => 'test']), 'created_at' => now(), 'updated_at' => now()]);
            $id = (string) Str::uuid();
            DB::table('storefront_payment_attempts')->insert(['id' => $id, 'order_id' => $orderId, 'legacy_payment_id' => $payment,
                'amount_minor' => $amount, 'currency' => 'INR', 'state' => 'creating', 'created_at' => now(), 'updated_at' => now()]);

            return [DB::table('storefront_payment_attempts')->where('id', $id)->first(), true];
        }, 5);
        if ($create) {
            try {
                $provider = $this->gateway->createOrder(['amount' => $attempt->amount_minor, 'currency' => 'INR',
                    'receipt' => $attempt->id, 'partial_payment' => false, 'notes' => ['storefront_order_id' => (string) $orderId, 'mode' => 'test']]);
                $this->adoptProviderOrder($attempt, $provider);
            } catch (\Throwable $e) {
                DB::table('storefront_payment_attempts')->where('id', $attempt->id)->whereNull('provider_order_id')->update(['state' => 'indeterminate', 'updated_at' => now()]);
                abort(503, 'The payment provider response could not be confirmed. Check order status; another payment order was not created.');
            }
        }
        $attempt = DB::table('storefront_payment_attempts')->where('id', $attempt->id)->first();

        return ['razorpayOrderId' => $attempt->provider_order_id, 'razorpayKey' => config('storefront.razorpay_key'),
            'amount' => $attempt->amount_minor, 'currency' => 'INR', 'order_id' => $orderId, 'mode' => 'test'];
    }

    private function adoptProviderOrder(object $attempt, array $provider): void
    {
        abort_unless(is_string($provider['id'] ?? null) && str_starts_with($provider['id'], 'order_')
            && ($provider['amount'] ?? null) === (int) $attempt->amount_minor && ($provider['currency'] ?? null) === 'INR'
            && ($provider['receipt'] ?? null) === $attempt->id, 409, 'Provider order amount or receipt mismatch.');
        DB::transaction(function () use ($attempt, $provider) {
            $row = DB::table('storefront_payment_attempts')->where('id', $attempt->id)->lockForUpdate()->first();
            abort_if($row->provider_order_id && $row->provider_order_id !== $provider['id'], 409, 'Payment order mapping changed.');
            if ($row->provider_order_id) {
                return;
            } // A delayed create response cannot downgrade a reconciled capture.
            DB::table('storefront_payment_attempts')->where('id', $attempt->id)->update(['provider_order_id' => $provider['id'], 'state' => 'created', 'updated_at' => now()]);
            DB::table('payments')->where('id', $row->legacy_payment_id)->update(['razorpay_order_id' => $provider['id'], 'updated_at' => now()]);
        }, 5);
    }

    public function confirm(int $customer, array $data): array
    {
        CheckoutAccess::assertGateway();
        $attempt = $this->ownedAttempt($customer, $data['razorpay_order_id']);
        $expected = hash_hmac('sha256', $attempt->provider_order_id.'|'.$data['razorpay_payment_id'], config('storefront.razorpay_secret'));
        abort_unless(hash_equals($expected, $data['razorpay_signature']), 422, 'Invalid payment signature.');

        return $this->verify($attempt, $data['razorpay_payment_id'], 'callback');
    }

    public function failed(int $customer, string $providerOrder, string $payment): array
    {
        CheckoutAccess::assertGateway();

        return $this->verify($this->ownedAttempt($customer, $providerOrder), $payment, 'failure-check');
    }

    private function ownedAttempt(int $customer, string $providerOrder): object
    {
        $attempt = DB::table('storefront_payment_attempts as a')->join('orders as o', 'o.order_id', '=', 'a.order_id')
            ->where('o.customer_id', $customer)->where('a.provider_order_id', $providerOrder)->select('a.*')->first();
        abort_unless($attempt, 404, 'Payment not found.');

        return $attempt;
    }

    public function verify(object $attempt, string $paymentId, string $source): array
    {
        $payment = $this->gateway->payment($paymentId);
        $provider = $this->gateway->order($attempt->provider_order_id);
        abort_unless(($payment['id'] ?? null) === $paymentId && ($payment['order_id'] ?? null) === $attempt->provider_order_id
            && ($payment['amount'] ?? null) === (int) $attempt->amount_minor && ($payment['currency'] ?? null) === 'INR'
            && ($provider['id'] ?? null) === $attempt->provider_order_id && ($provider['amount'] ?? null) === (int) $attempt->amount_minor
            && ($provider['currency'] ?? null) === 'INR' && ($provider['receipt'] ?? null) === $attempt->id, 409, 'Payment amount, currency or order binding mismatch.');
        $state = $payment['status'] ?? '';
        abort_unless(in_array($state, ['captured', 'authorized', 'failed']), 409, 'Payment has not reached a verifiable state.');
        if ($state === 'captured') {
            abort_unless(($payment['captured'] ?? false) === true && ($provider['status'] ?? '') === 'paid'
                && ($provider['amount_paid'] ?? null) === (int) $attempt->amount_minor && ($provider['amount_due'] ?? null) === 0, 409, 'Captured payment is not reconciled with the provider order.');
        }

        return DB::transaction(function () use ($attempt, $payment, $state, $source, $provider) {
            $orderRow = DB::table('orders')->where('order_id', $attempt->order_id)->first();
            app(CheckoutService::class)->lockCustomer($orderRow->customer_id, false);
            $order = DB::table('orders')->where('order_id', $attempt->order_id)->lockForUpdate()->first();
            $checkout = DB::table('storefront_checkouts')->where('order_id', $attempt->order_id)->lockForUpdate()->first();
            $row = DB::table('storefront_payment_attempts')->where('id', $attempt->id)->lockForUpdate()->first();
            abort_unless($checkout && Money::minor($order->total_amount) === (int) $row->amount_minor, 409, 'Order amount changed; reconciliation is required.');
            if ($row->state === 'captured') {
                abort_if($state === 'captured' && $row->provider_payment_id !== $payment['id'], 409, 'A second capture needs manual reconciliation.');

                return ['status' => 'success', 'paid' => true, 'order_id' => $order->order_id, 'requires_review' => $checkout->state === 'refund_required'];
            }
            DB::table('storefront_payment_events')->insertOrIgnore(['event_key' => hash('sha256', $row->id.'|'.$payment['id'].'|'.$state),
                'attempt_id' => $row->id, 'provider_payment_id' => $payment['id'], 'state' => $state, 'source' => $source,
                'amount_minor' => $row->amount_minor, 'created_at' => now(), 'updated_at' => now()]);
            if ($state === 'captured') {
                $review = $checkout->state !== 'reserved' || $order->status === 'rejected';
                DB::table('storefront_payment_attempts')->where('id', $row->id)->update(['state' => 'captured', 'provider_payment_id' => $payment['id'], 'failure_code' => null, 'updated_at' => now()]);
                DB::table('payments')->where('id', $row->legacy_payment_id)->update(['status' => 'paid', 'razorpay_payment_id' => $payment['id'], 'updated_at' => now()]);
                DB::table('orders')->where('order_id', $order->order_id)->update(['payment_status' => 'paid', 'paid_at' => now(), 'updated_at' => now()]);
                if ($review) {
                    DB::table('storefront_checkouts')->where('order_id', $order->order_id)->update(['state' => 'refund_required', 'updated_at' => now()]);
                } else {
                    app(CheckoutService::class)->commit($order->order_id);
                }
                Outbox::record('order.captured:'.$order->order_id, 'cache.changed', ['order_id' => $order->order_id]);

                return ['status' => 'success', 'paid' => true, 'order_id' => $order->order_id, 'requires_review' => $review];
            }
            if ($state === 'failed' && ($provider['status'] ?? '') !== 'paid') {
                DB::table('storefront_payment_attempts')->where('id', $row->id)->update(['state' => 'failed', 'failure_code' => substr($payment['error_code'] ?? 'payment_failed', 0, 100), 'updated_at' => now()]);
                DB::table('payments')->where('id', $row->legacy_payment_id)->where('status', '!=', 'paid')->update(['status' => 'failed', 'updated_at' => now()]);
                if ($checkout->state === 'reserved') {
                    DB::table('orders')->where('order_id', $order->order_id)->where('payment_status', '!=', 'paid')->update(['payment_status' => 'failed', 'updated_at' => now()]);
                }
            } elseif ($state === 'authorized') {
                DB::table('storefront_payment_attempts')->where('id', $row->id)->update(['state' => 'authorized', 'updated_at' => now()]);
            }

            return ['status' => 'pending', 'paid' => false, 'order_id' => $order->order_id, 'message' => $state === 'failed' ? 'Payment failed. You may retry this order before its reservation expires.' : 'Capture is pending. Check order status before retrying.'];
        }, 5);
    }

    public function reconcile(int $orderId): array
    {
        CheckoutAccess::assertGateway();
        $attempt = DB::table('storefront_payment_attempts')->where('order_id', $orderId)->first();
        abort_unless($attempt, 404, 'Payment attempt not found.');
        if (! $attempt->provider_order_id) {
            $found = $this->gateway->findOrder($attempt->id);
            abort_unless($found, 409, 'Provider order was not found. The indeterminate request requires manual review; no duplicate will be created.');
            $this->adoptProviderOrder($attempt, $found);
            $attempt = DB::table('storefront_payment_attempts')->where('order_id', $orderId)->first();
        }
        $payments = $this->gateway->orderPayments($attempt->provider_order_id);
        usort($payments, fn ($a, $b) => (($b['status'] ?? '') === 'captured') <=> (($a['status'] ?? '') === 'captured'));
        foreach ($payments as $payment) {
            if (in_array($payment['status'] ?? '', ['captured', 'authorized', 'failed'])) {
                $result = $this->verify($attempt, $payment['id'], 'reconciliation');
                if ($result['paid']) {
                    return $result;
                }
            }
        }

        return ['status' => 'pending', 'paid' => false, 'order_id' => $orderId];
    }

    public function webhook(string $raw, string $signature, string $eventId): array
    {
        CheckoutAccess::assertGateway();
        $secret = config('storefront.webhook_secret');
        abort_unless(is_string($secret) && strlen($secret) >= 16, 503, 'Webhook verification is not configured.');
        abort_unless(strlen($raw) <= 262144 && hash_equals(hash_hmac('sha256', $raw, $secret), $signature), 400, 'Invalid webhook signature.');
        $body = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $raw);
        abort_unless(preg_match('/^[a-zA-Z0-9_-]{1,150}$/D', $eventId), 422, 'Invalid webhook event ID.');
        $type = $body['event'] ?? '';
        abort_unless(is_string($type) && strlen($type) <= 100, 422, 'Invalid event type.');
        DB::table('storefront_webhook_events')->insertOrIgnore(['event_id' => $eventId, 'body_hash' => $hash, 'event_type' => $type, 'created_at' => now(), 'updated_at' => now()]);
        $event = DB::table('storefront_webhook_events')->where('event_id', $eventId)->first();
        if (! $event) {
            $event = DB::table('storefront_webhook_events')->where('body_hash', $hash)->first();
        }
        abort_unless($event && hash_equals($event->body_hash, $hash), 409, 'Webhook event ID was reused with different content.');
        if ($event->processed_at) {
            return ['status' => 'success', 'duplicate' => true];
        }
        if (in_array($type, ['payment.captured', 'payment.authorized', 'payment.failed', 'order.paid'])) {
            $entity = $body['payload']['payment']['entity'] ?? [];
            $orderId = $entity['order_id'] ?? $body['payload']['order']['entity']['id'] ?? null;
            $attempt = DB::table('storefront_payment_attempts')->where('provider_order_id', $orderId)->first();
            abort_unless($attempt, 409, 'Payment mapping is not available yet. Retry this webhook.');
            if (! empty($entity['id'])) {
                $this->verify($attempt, $entity['id'], 'webhook');
            } else {
                $this->reconcile($attempt->order_id);
            }
        } elseif (in_array($type, ['refund.processed', 'refund.failed'])) {
            app(RefundService::class)->reconcile($body['payload']['refund']['entity']['id'] ?? '');
        }
        DB::table('storefront_webhook_events')->where('event_id', $event->event_id)->update(['processed_at' => now(), 'updated_at' => now()]);

        return ['status' => 'success', 'duplicate' => false];
    }
}
