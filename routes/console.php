<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('storefront:expire-reservations', function () {
    [$done, $errors] = app(\App\Services\Storefront\FinancialOperations::class)->expireReserved();
    $this->line(json_encode(['expired_reviewed' => $done, 'errors' => $errors]));

    return $errors ? 1 : 0;
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

Artisan::command('storefront:financial-maintenance', function () {
    $result = app(\App\Services\Storefront\FinancialOperations::class)->run();
    $this->line(json_encode($result));

    return $result['errors'] ? 1 : 0;
})->purpose('Process durable local/test financial recovery without issuing new provider payments or refunds');

Artisan::command('storefront:financial-status', function () {
    \App\Services\Storefront\CheckoutAccess::assertEnabled();
    $count = \Illuminate\Support\Facades\DB::table('storefront_operational_alerts')->whereNull('resolved_at')->count();
    $this->line(json_encode(['unresolved_alerts' => $count]));

    return $count ? 1 : 0;
})->purpose('Return a nonzero monitoring status when financial alerts require review');

\Illuminate\Support\Facades\Schedule::command('storefront:financial-maintenance')->everyMinute()->withoutOverlapping(10)
    ->preventOverlapsUsing((new \Illuminate\Console\Scheduling\CacheEventMutex(app('cache')))->useStore('database'))
    ->when(fn () => \App\Services\Storefront\CheckoutAccess::enabled());

Artisan::command('storefront:refund-late-capture {order}', function () {
    $this->line(json_encode(app(\App\Services\Storefront\RefundService::class)->lateCapture((int) $this->argument('order'))));
})->purpose('Operator-only test refund of verified funds received after stock release');

Artisan::command('storefront:archive-documents', function () {
    \App\Services\Storefront\CheckoutAccess::assertEnabled();
    $copied = 0;
    foreach (\App\Models\Invoice::orderBy('id')->cursor() as $invoice) {
        foreach (['invoice', 'packing-slip'] as $kind) {
            try {
                app(\App\Services\Storefront\OrderDocuments::class)->preserveHistorical($invoice, $kind);
                $copied++;
            } catch (\Throwable $error) {
                \App\Services\Storefront\OperationalAlert::record('historical_document_missing', $invoice->id.':'.$kind);
            }
        }
    }
    $this->info($copied.' document copies verified. Original bytes and records were retained.');
})->purpose('Copy and hash-verify existing test invoices to private storage without deleting originals');

Artisan::command('storefront:recover-refund-intent {intent} {provider_refund}', function () {
    $this->line(json_encode(app(\App\Services\Storefront\RefundService::class)->recoverIntent($this->argument('intent'), $this->argument('provider_refund'))));
})->purpose('Recover a bound test refund intent without resubmitting funds');
