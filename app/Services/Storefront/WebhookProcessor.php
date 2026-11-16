<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class WebhookProcessor
{
    public function receive(string $raw, string $signature, string $eventId): array
    {
        CheckoutAccess::assertGateway();
        $secrets = [config('storefront.webhook_secret'), ...config('storefront.previous_webhook_secrets', [])];
        $valid = false;
        foreach ($secrets as $secret) {
            if (is_string($secret) && strlen($secret) >= 16) {
                $valid = hash_equals(hash_hmac('sha256', $raw, $secret), $signature) || $valid;
            }
        }
        abort_unless(strlen($raw) <= 262144 && $valid, 400, 'Invalid webhook signature.');
        abort_unless(preg_match('/^[a-zA-Z0-9_-]{1,150}$/D', $eventId), 422, 'Invalid webhook event ID.');
        try {
            $body = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            abort(422, 'Invalid webhook JSON.');
        }
        $type = $body['event'] ?? null;
        abort_unless(is_string($type) && preg_match('/^[a-z][a-z0-9_.]{1,99}$/D', $type), 422, 'Invalid event type.');
        $supported = in_array($type, ['payment.captured', 'payment.authorized', 'payment.failed', 'order.paid', 'refund.processed', 'refund.failed', 'refund.created']);
        $order = $body['payload']['payment']['entity']['order_id'] ?? $body['payload']['order']['entity']['id'] ?? null;
        $payment = $body['payload']['payment']['entity']['id'] ?? null;
        $refund = $body['payload']['refund']['entity']['id'] ?? null;
        foreach (['order' => $order, 'pay' => $payment, 'rfnd' => $refund] as $prefix => $value) {
            if ($value !== null) {
                abort_unless(is_string($value) && preg_match('/^'.$prefix.'_[a-zA-Z0-9]{1,90}$/D', $value), 422, 'Invalid webhook entity.');
            }
        }
        if ($supported) {
            abort_unless(str_starts_with($type, 'refund.') ? $refund : $order, 422, 'Webhook binding is missing.');
        }
        $hash = hash('sha256', $raw);
        $event = DB::transaction(function () use ($eventId, $hash, $type, $supported, $order, $payment, $refund) {
            DB::table('storefront_webhook_events')->insertOrIgnore(['event_id' => $eventId, 'body_hash' => $hash, 'event_type' => $type,
                'provider_order_id' => $order, 'provider_payment_id' => $payment, 'provider_refund_id' => $refund,
                'next_attempt_at' => now(), 'processed_at' => $supported ? null : now(), 'created_at' => now(), 'updated_at' => now()]);
            $event = DB::table('storefront_webhook_events')->where('event_id', $eventId)->lockForUpdate()->first()
                ?? DB::table('storefront_webhook_events')->where('body_hash', $hash)->lockForUpdate()->first();
            abort_unless($event && hash_equals($event->body_hash, $hash), 409, 'Webhook event ID was reused with different content.');
            // A signed replay can recover bindings absent from pre-migration pending rows.
            if (! $event->processed_at && ! $event->provider_order_id && ! $event->provider_refund_id) {
                DB::table('storefront_webhook_events')->where('event_id', $event->event_id)->update([
                    'provider_order_id' => $order, 'provider_payment_id' => $payment, 'provider_refund_id' => $refund, 'updated_at' => now(),
                ]);
            }

            return $event;
        }, 5);
        if (! $event->processed_at && config('queue.default') !== 'sync') {
            try {
                \App\Jobs\ProcessStorefrontWebhook::dispatch($event->event_id)->onQueue('storefront-financial');
            } catch (\Throwable $error) {
                OperationalAlert::record('webhook_queue_unavailable', $event->event_id);
            }
        }

        return ['status' => 'accepted', 'duplicate' => $event->processed_at !== null, 'queued' => $event->processed_at === null];
    }

    public function process(string $eventId): bool
    {
        CheckoutAccess::assertGateway();
        $token = (string) Str::uuid();
        $event = DB::transaction(function () use ($eventId, $token) {
            $row = DB::table('storefront_webhook_events')->where('event_id', $eventId)->lockForUpdate()->first();
            if (! $row || $row->processed_at || ($row->lease_expires_at && now()->lessThan($row->lease_expires_at)) || ($row->next_attempt_at && now()->lessThan($row->next_attempt_at))) {
                return null;
            }
            DB::table('storefront_webhook_events')->where('event_id', $eventId)->update(['lease_token' => $token, 'lease_expires_at' => now()->addMinutes(5), 'attempts' => $row->attempts + 1, 'updated_at' => now()]);

            return $row;
        }, 5);
        if (! $event) {
            return false;
        }
        try {
            if (str_starts_with($event->event_type, 'refund.')) {
                $result = app(ProviderRefunds::class)->observe($event->provider_refund_id);
                abort_if($result['status'] === 'pending', 409, 'Refund is still pending.');
            } else {
                $attempt = DB::table('storefront_payment_attempts')->where('provider_order_id', $event->provider_order_id)->first();
                abort_unless($attempt, 409, 'Payment mapping is pending.');
                $result = $event->provider_payment_id ? app(PaymentService::class)->verify($attempt, $event->provider_payment_id, 'webhook') : app(PaymentService::class)->reconcile($attempt->order_id);
                // A pending result must stay retryable, including early order.paid visibility.
                abort_if(! $result['paid'] && ! ($event->event_type === 'payment.failed' && ($result['provider_state'] ?? '') === 'failed'), 409, 'Provider capture is pending.');
            }
            DB::table('storefront_webhook_events')->where('event_id', $eventId)->where('lease_token', $token)->update(['processed_at' => now(), 'lease_token' => null, 'lease_expires_at' => null, 'last_error' => null, 'updated_at' => now()]);
            OperationalAlert::resolve('webhook_reconciliation', $eventId);
            OperationalAlert::resolve('webhook_queue_unavailable', $eventId);
            OperationalAlert::resolve('webhook_job_failed', $eventId);

            return true;
        } catch (\Throwable $error) {
            DB::table('storefront_webhook_events')->where('event_id', $eventId)->where('lease_token', $token)->update(['lease_token' => null, 'lease_expires_at' => null,
                'next_attempt_at' => now()->addSeconds(min(3600, 30 * (2 ** min(7, $event->attempts)))), 'last_error' => class_basename($error), 'updated_at' => now()]);
            OperationalAlert::record('webhook_reconciliation', $eventId);

            return false;
        }
    }

    public function drain(int $limit = 100): int
    {
        $ids = DB::table('storefront_webhook_events')->whereNull('processed_at')->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('lease_expires_at')->orWhere('lease_expires_at', '<=', now()))->orderBy('created_at')->limit($limit)->pluck('event_id');
        $done = 0;
        foreach ($ids as $id) {
            if ($this->process($id)) {
                $done++;
            }
        }

        return $done;
    }
}
