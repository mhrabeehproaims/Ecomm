<?php

// Diagnostic reproductions, not assertions that the observed gaps are acceptable.
// Uses in-memory SQLite, a fake gateway, and a synthetic invoice under framework/testing.
$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
    'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'cache.limiter' => 'array',
    'hashing.bcrypt.rounds' => 4, 'storefront.test_checkout' => true, 'storefront.cod_enabled' => true,
    'storefront.shipping_minor' => ['kerala (KL)' => 0], 'storefront.shipping_tax_basis_points' => 0,
    'storefront.razorpay_key' => 'rzp_test_readiness', 'storefront.razorpay_secret' => 'fake-readiness-secret',
    'storefront.webhook_secret' => 'fake-readiness-webhook-secret']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
$gateway = new Tests\Support\FakeStorefrontGateway;
$app->instance(App\Services\Storefront\PaymentGateway::class, $gateway);
$fixture = new class
{
    use Tests\Support\StorefrontFixtures;

    public function prepare(): array
    {
        $this->fixtures();

        return [$this->customer, $this->product, $this->address];
    }

    public function fillBag(): void
    {
        $this->bag();
    }
};
[$customer, $product, $address] = $fixture->prepare();
$checkout = app(App\Services\Storefront\CheckoutService::class);
$payments = app(App\Services\Storefront\PaymentService::class);
$db = Illuminate\Support\Facades\DB::class;
$uuid = fn () => (string) Illuminate\Support\Str::uuid();
$create = function (string $method = 'online') use ($fixture, $checkout, $customer, $address, $uuid): array {
    $fixture->fillBag();
    $quote = $checkout->quote($customer, $address, '', $method);

    return $checkout->place($customer, $quote['quote_id'], $uuid());
};
$check = function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};
$result = ['audit_date' => '2026-11-16', 'scope' => 'Isolated diagnostic reproductions. No real provider requests.'];

// Route inspection confirmed public invoice downloads. Exercise only a new synthetic file.
$storage = storage_path();
$invoiceStorage = $test['directory'].'/readiness-invoice-storage';
$app->useStoragePath($invoiceStorage);
$invoiceDir = storage_path('app/public/Invoices');
if (! is_dir($invoiceDir)) {
    mkdir($invoiceDir, 0777, true);
}
$name = 'readiness-'.$uuid().'.pdf';
file_put_contents($invoiceDir.'/'.$name, "%PDF-1.4\nSynthetic readiness fixture only.\n");
$request = Illuminate\Http\Request::create('/download-invoice/'.$name, 'GET');
$http = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $http->handle($request);
$check($response->getStatusCode() === 200, 'Invoice route behavior changed; re-review ownership finding.');
$result['anonymous_invoice'] = ['status' => $response->getStatusCode(), 'only_synthetic_file' => true, 'finding' => 'Unauthenticated invoice response is not ownership protected.'];
$http->terminate($request, $response);
$app->useStoragePath($storage);

// Acknowledging order.paid before a payment becomes fetchable suppresses identical retries.
$order = $create();
$start = $payments->start($customer, $order['order_id']);
$raw = json_encode(['event' => 'order.paid', 'payload' => ['order' => ['entity' => ['id' => $start['razorpayOrderId']]]]]);
$signature = hash_hmac('sha256', $raw, config('storefront.webhook_secret'));
$event = 'evt_ReadinessEarlyOrder';
$payments->webhook($raw, $signature, $event);
$marked = $db::table('storefront_webhook_events')->where('event_id', $event)->value('processed_at') !== null;
$gateway->paymentState($start['razorpayOrderId'], 'captured');
$replay = $payments->webhook($raw, $signature, $event);
$unpaid = $db::table('orders')->where('order_id', $order['order_id'])->value('payment_status');
$check($marked && $replay['duplicate'] && $unpaid === 'unpaid', 'Early order.paid behavior changed; re-review replay finding.');
$recovery = $payments->reconcile($order['order_id']);
$result['early_order_paid'] = ['marked_processed' => $marked, 'replay_ignored' => $replay['duplicate'], 'local_payment_after_replay' => $unpaid, 'manual_reconciliation_recovers' => $recovery['paid']];

// Provider lifecycle includes refunded; local reconciliation currently skips that state.
$gateway->payments['pay_Test1']['status'] = 'refunded';
$gateway->payments['pay_Test1']['captured'] = true;
$afterRefund = $payments->reconcile($order['order_id']);
$attempt = $db::table('storefront_payment_attempts')->where('order_id', $order['order_id'])->first();
$callbackStatus = null;
try {
    $payments->verify($attempt, 'pay_Test1', 'readiness-probe');
} catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $error) {
    $callbackStatus = $error->getStatusCode();
}
$check($afterRefund['status'] === 'pending' && $callbackStatus === 409, 'Refunded payment behavior changed; re-review lifecycle finding.');
$result['refunded_provider_payment'] = ['reconciliation_status' => $afterRefund['status'], 'verification_http_status' => $callbackStatus, 'finding' => 'A refunded provider payment is not reconciled by PaymentService.'];

// Render existing fulfillment templates as HTML using only the synthetic order.
// No PDF is generated and no original address or invoice is touched.
$record = App\Models\Order::disableCache()->with('items.product')->findOrFail($order['order_id']);
$buyer = App\Models\Customer::disableCache()->findOrFail($customer);
$currentAddress = App\Models\DeliveryAddress::findOrFail($address);
$currentAddress->address_line1 = 'CURRENT-DEFAULT-ADDRESS-MARKER';
$buyer->state = 'tamil_nadu (TN)';
$buyer->setRelation('defaultDeliveryAddress', $currentAddress);
$record->setRelation('customer', $buyer);
$invoice = new App\Models\Invoice(['invoice_number' => 'READINESS-DYNAMIC-NUMBER', 'invoice_date' => '2026-11-16', 'total_amount' => $record->total_amount]);
$invoice->setRelation('order', $record);
$invoiceHtml = view('invoices.pdf', ['invoice' => $invoice, 'logo' => base64_encode('synthetic')])->render();
$packingHtml = view('invoices.packing_slip', ['invoice' => $invoice])->render();
$result['fulfillment_documents'] = [
    'invoice_uses_current_default_address' => str_contains($invoiceHtml, 'CURRENT-DEFAULT-ADDRESS-MARKER'),
    'packing_slip_uses_current_default_address' => str_contains($packingHtml, 'CURRENT-DEFAULT-ADDRESS-MARKER'),
    'invoice_uses_hardcoded_number' => str_contains($invoiceHtml, '#INV0000001'),
    'invoice_uses_hardcoded_date' => str_contains($invoiceHtml, '04 Nov 2025'),
    'packing_slip_invents_fallback_carrier' => str_contains($packingHtml, 'DELHIVERYSURFACE'),
];
$check(! in_array(false, $result['fulfillment_documents'], true), 'Fulfillment document behavior changed; re-review these findings.');

// COD commits immediately but no cash-collection status transition exists in these services.
$cod = $create('cod');
$db::table('orders')->where('order_id', $cod['order_id'])->update(['status' => 'delivered']);
$db::table('order_status_logs')->insert(['order_id' => $cod['order_id'], 'status' => 'delivered', 'changed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
$eligible = app(App\Services\Storefront\ReturnService::class)->eligibility($customer, $cod['order_id']);
$cancelStatus = null;
try {
    $checkout->release($customer, $cod['order_id']);
} catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $error) {
    $cancelStatus = $error->getStatusCode();
}
$check(! $eligible['items'][0]['eligible'] && $cancelStatus === 409, 'COD behavior changed; re-review business workflow findings.');
$result['delivered_cod'] = ['payment_status' => $db::table('orders')->where('order_id', $cod['order_id'])->value('payment_status'), 'return_eligible' => $eligible['items'][0]['eligible'], 'customer_cancellation_status' => $cancelStatus];
$app->detectEnvironment(fn () => 'staging');
$result['standard_staging_environment_checkout_enabled'] = App\Services\Storefront\CheckoutAccess::enabled();
$check($result['standard_staging_environment_checkout_enabled'] === false, 'Staging guard unexpectedly changed.');

$output = $test['root'].'/storefront/artifacts/readiness-20261116';
if (! is_dir($output)) {
    mkdir($output, 0777, true);
}
file_put_contents($output.'/diagnostic-probes.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
