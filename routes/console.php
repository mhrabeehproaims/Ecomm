<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('storefront:expire-reservations', function () {
    \App\Services\Storefront\CheckoutAccess::assertEnabled();
    $rows = \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('state', 'reserved')->where('expires_at', '<=', now())->get();
    foreach ($rows as $row) {
        app(\App\Services\Storefront\CheckoutService::class)->release($row->customer_id, $row->order_id, true);
    }
    $this->info($rows->count().' expired reservations reviewed. Late captures remain flagged for refund reconciliation.');
})->purpose('Release expired local/test storefront reservations exactly once');

Artisan::command('storefront:reconcile-payment {order}', function () {
    $this->line(json_encode(app(\App\Services\Storefront\PaymentService::class)->reconcile((int) $this->argument('order'))));
})->purpose('Reconcile a test provider order without creating another payment');

Artisan::command('storefront:refund-return {return}', function () {
    $this->line(json_encode(app(\App\Services\Storefront\RefundService::class)->request((int) $this->argument('return'))));
})->purpose('Request/reconcile one refund for a collected return in test mode only');

Artisan::command('storefront:dispatch-events', function () {
    $this->info(app(\App\Services\Storefront\Outbox::class)->dispatch().' local/test events dispatched.');
})->purpose('Retry committed storefront notification effects without repeating financial changes');

Artisan::command('storefront:recover-refund {return} {provider_refund}', function () {
    $this->line(json_encode(app(\App\Services\Storefront\RefundService::class)->recover((int) $this->argument('return'), $this->argument('provider_refund'))));
})->purpose('Bind a verified provider refund to an indeterminate test refund intent without sending money again');
