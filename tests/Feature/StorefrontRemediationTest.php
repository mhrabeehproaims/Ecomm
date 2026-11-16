<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\CodAccounting;
use App\Services\Storefront\FinancialLedger;
use App\Services\Storefront\FinancialOperations;
use App\Services\Storefront\OrderDocuments;
use App\Services\Storefront\PaymentGateway;
use App\Services\Storefront\PaymentService;
use App\Services\Storefront\WebhookProcessor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\FakeStorefrontGateway;
use Tests\Support\StorefrontFixtures;
use Tests\TestCase;

class StorefrontRemediationTest extends TestCase
{
    use StorefrontFixtures;

    private FakeStorefrontGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $files = storage_path('framework/testing/remediation-'.Str::uuid());
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null,
            'cache.default' => 'array', 'cache.limiter' => 'array', 'storefront.test_checkout' => true, 'storefront.cod_enabled' => true,
            'storefront.shipping_minor' => ['kerala (KL)' => 5000], 'storefront.shipping_tax_basis_points' => 1800,
            'storefront.razorpay_key' => 'rzp_test_remediation', 'storefront.razorpay_secret' => 'fake-remediation-secret',
            'storefront.webhook_secret' => 'fake-remediation-webhook-secret', 'storefront.previous_webhook_secrets' => [],
            'filesystems.disks.documents.root' => $files.'/private', 'filesystems.disks.public.root' => $files.'/public']);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        Http::preventStrayRequests();
        $this->gateway = new FakeStorefrontGateway;
        $this->app->instance(PaymentGateway::class, $this->gateway);
        $this->fixtures();
    }

    private function order(string $method = 'online', int $quantity = 1): array
    {
        $this->bag($quantity);
        $quote = app(CheckoutService::class)->quote($this->customer, $this->address, 'TESTSAVE', $method);

        return app(CheckoutService::class)->place($this->customer, $quote['quote_id'], (string) Str::uuid());
    }

    private function capture(array $order): array
    {
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        $this->gateway->paymentState($start['razorpayOrderId'], 'captured');
        $attempt = DB::table('storefront_payment_attempts')->where('order_id', $order['order_id'])->first();
        app(PaymentService::class)->verify($attempt, 'pay_Test1', 'test');

        return $start;
    }

    private function token(int $customer): void
    {
        $this->app['auth']->forgetGuards();
        $this->withToken(Customer::disableCache()->findOrFail($customer)->createToken('test')->plainTextToken);
    }

    public function test_documents_are_private_owned_immutable_and_use_order_time_values(): void
    {
        $order = $this->order('cod', 2);
        DB::table('customers')->where('customer_id', $this->customer)->update(['first_name' => 'Changed later']);
        DB::table('delivery_addresses')->where('address_id', $this->address)->update(['address_line1' => 'Changed later address']);
        DB::table('products')->where('product_id', $this->product)->update(['product_name' => 'Changed later product']);
        $invoice = app(OrderDocuments::class)->generate($order['order_id']);
        $documents = DB::table('storefront_order_documents')->where('invoice_id', $invoice->id)->get();
        $this->assertCount(2, $documents);
        $snapshot = json_decode($documents->first()->snapshot, true);
        $this->assertSame('Test', $snapshot['customer']['first_name']);
        $this->assertSame('Test lane', $snapshot['address']['address_line1']);
        $this->assertSame('Test bag', $snapshot['pricing']['items'][0]['name']);
        $this->assertSame(24000, $snapshot['pricing']['total_minor']);
        $this->assertGreaterThan(0, $snapshot['pricing']['shipping_tax_minor']);
        $html = view('invoices.pdf', ['document' => $snapshot])->render();
        $this->assertStringNotContainsString('Changed later', $html);
        $this->assertStringContainsString($invoice->invoice_number, $html);
        $this->assertStringNotContainsString('04 Nov 2025', $html);
        $mail = new \App\Mail\sentOrderDespatchedMail(\App\Models\Order::disableCache()->with(['customer', 'items.product'])->findOrFail($order['order_id']), route('download.invoice', $invoice->invoice_number.'.pdf'));
        $emailHtml = $mail->render();
        $this->assertStringContainsString('Test bag', $emailHtml);
        $this->assertStringNotContainsString('Changed later', $emailHtml);
        $this->assertStringContainsString('240.00', $emailHtml);
        $this->assertStringNotContainsString('290.00', $emailHtml);
        $this->assertCount(1, $mail->attachments());
        $delivered = new \App\Mail\OrderDeliveredMail(\App\Models\Order::disableCache()->with(['customer', 'items.product'])->findOrFail($order['order_id']));
        $deliveredHtml = $delivered->render();
        $this->assertStringContainsString('Test lane', $deliveredHtml);
        $this->assertStringNotContainsString('Changed later', $deliveredHtml);
        $this->assertStringContainsString('240.00', $deliveredHtml);
        $this->assertStringNotContainsString('290.00', $deliveredHtml);
        $this->assertStringContainsString('Not assigned', view('invoices.packing_slip', ['document' => $snapshot])->render());
        $this->assertSame($invoice->id, app(OrderDocuments::class)->generate($order['order_id'])->id);
        $this->assertDatabaseCount('storefront_order_documents', 2);
        foreach ($documents as $document) {
            $this->assertSame($document->sha256, hash_file('sha256', Storage::disk('documents')->path($document->path)));
        }
        $this->get('/download-invoice/'.$invoice->invoice_number.'.pdf')->assertNotFound();
        $this->get('/download-invoice/UNKNOWN.pdf')->assertNotFound();
        $this->token($this->other);
        $this->get('/download-invoice/'.$invoice->invoice_number.'.pdf')->assertNotFound();
        $this->token($this->customer);
        $response = $this->get('/download-invoice/'.$invoice->invoice_number.'.pdf')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->get('/api/orders/'.$order['order_id'].'/documents/invoice')->assertOk();
        $this->actingAs(User::factory()->create(), 'web')->get('/download-packing-slip/'.$invoice->invoice_number.'_packing_slip.pdf')->assertOk();
        $this->get('/storage/Invoices/'.$invoice->invoice_number.'.pdf')->assertNotFound();
        $this->get('/storage/PackingSlips/'.$invoice->invoice_number.'_packing_slip.pdf')->assertNotFound();
        $output = base_path('storefront/artifacts/remediation/documents');
        if (! is_dir($output)) {
            mkdir($output, 0777, true);
        }
        foreach ($documents as $document) {
            copy(Storage::disk('documents')->path($document->path), $output.'/'.$document->kind.'.pdf');
        }
    }

    public function test_legacy_pdf_is_preserved_and_missing_legacy_snapshots_are_not_invented(): void
    {
        $legacy = DB::table('orders')->insertGetId(['customer_id' => $this->customer, 'total_amount' => 100, 'status' => 'delivered', 'payment_method' => 'cod', 'payment_status' => 'paid', 'created_at' => now(), 'updated_at' => now()]);
        $invoice = Invoice::create(['order_id' => $legacy, 'invoice_number' => 'LEGACY-ORIGINAL', 'invoice_date' => '2020-01-01', 'total_amount' => 100]);
        $bytes = '%PDF-1.4 original historical fixture';
        Storage::disk('public')->put('Invoices/LEGACY-ORIGINAL.pdf', $bytes);
        app(OrderDocuments::class)->preserveHistorical($invoice, 'invoice');
        $this->assertSame(hash('sha256', $bytes), hash_file('sha256', app(OrderDocuments::class)->file($invoice, 'invoice')));
        $this->assertSame($bytes, Storage::disk('public')->get('Invoices/LEGACY-ORIGINAL.pdf'));
        $this->assertDatabaseHas('storefront_order_documents', ['source' => 'legacy_preserved', 'snapshot' => null]);
        $missing = DB::table('orders')->insertGetId(['customer_id' => $this->customer, 'total_amount' => 100, 'status' => 'ordered', 'created_at' => now(), 'updated_at' => now()]);
        try {
            app(OrderDocuments::class)->generate($missing);
            $this->fail('Missing historical evidence must not be invented.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
        $this->assertSame(1, Invoice::count());
    }

    public function test_invoice_uses_nondefault_interstate_address_actual_percentage_discount_and_delivery_tax(): void
    {
        config(['storefront.shipping_minor' => ['kerala (KL)' => 5000, 'tamil_nadu (TN)' => 5000]]);
        $selected = $this->addressFor($this->customer);
        DB::table('delivery_addresses')->where('address_id', $selected)->update(['is_default' => false, 'address_line1' => 'Selected nondefault address', 'state' => 'tamil_nadu (TN)']);
        DB::table('coupons')->where('code', 'TESTSAVE')->update(['type' => 'percentage', 'value' => '13.33']);
        $this->bag(3);
        $quote = app(CheckoutService::class)->quote($this->customer, $selected, 'TESTSAVE', 'cod');
        $order = app(CheckoutService::class)->place($this->customer, $quote['quote_id'], (string) Str::uuid());
        DB::table('coupons')->where('code', 'TESTSAVE')->update(['value' => 80]);
        DB::table('delivery_addresses')->where('address_id', $selected)->update(['address_line1' => 'Later edit', 'state' => 'kerala (KL)']);
        $invoice = app(OrderDocuments::class)->generate($order['order_id']);
        $document = json_decode(DB::table('storefront_order_documents')->where('invoice_id', $invoice->id)->where('kind', 'invoice')->value('snapshot'), true);
        $this->assertSame(3999, $document['pricing']['discount_minor']);
        $this->assertSame(31001, $document['pricing']['total_minor']);
        $this->assertSame(763, $document['pricing']['shipping_tax_minor']);
        $this->assertSame(1800, $document['pricing']['items'][0]['igst_basis_points']);
        $html = view('invoices.pdf', ['document' => $document])->render();
        $this->assertStringContainsString('Selected nondefault address', $html);
        $this->assertStringNotContainsString('Test lane', $html);
        $this->assertStringNotContainsString('Later edit', $html);
        $this->assertStringContainsString('39.99', $html);
        $this->assertStringContainsString('310.01', $html);
    }

    public function test_early_order_paid_is_queued_then_recovers_without_a_browser_or_duplicate_effects(): void
    {
        $order = $this->order();
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        $raw = json_encode(['event' => 'order.paid', 'payload' => ['order' => ['entity' => ['id' => $start['razorpayOrderId']]]]]);
        $signature = hash_hmac('sha256', $raw, config('storefront.webhook_secret'));
        $processor = app(WebhookProcessor::class);
        $received = $processor->receive($raw, $signature, 'evt_Early');
        $this->assertTrue($received['queued']);
        $this->assertFalse($received['duplicate']);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'unpaid']);
        $this->assertFalse($processor->process('evt_Early'));
        $this->assertNull(DB::table('storefront_webhook_events')->where('event_id', 'evt_Early')->value('processed_at'));
        $this->gateway->paymentState($start['razorpayOrderId'], 'captured');
        $this->travel(31)->seconds();
        $this->assertTrue($processor->process('evt_Early'));
        $this->assertTrue($processor->receive($raw, $signature, 'evt_Early')['duplicate']);
        $this->assertTrue($processor->receive($raw, $signature, 'evt_AnotherId')['duplicate']);
        $this->assertSame(1, $this->gateway->creations);
        $this->assertDatabaseCount('storefront_financial_entries', 1);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'paid']);
        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('coupon_usages', 1);
        $this->assertSame(19, DB::table('products')->where('product_id', $this->product)->value('stock'));
    }

    public function test_invalid_webhooks_never_touch_provider_and_previous_secret_can_verify_retries(): void
    {
        $raw = json_encode(['event' => 'some.unknown']);
        try {
            app(WebhookProcessor::class)->receive($raw, str_repeat('0', 64), 'evt_Bad');
            $this->fail('Invalid signature accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(400, $error->getStatusCode());
        }
        config(['storefront.previous_webhook_secrets' => ['old-overlap-webhook-secret']]);
        $result = app(WebhookProcessor::class)->receive($raw, hash_hmac('sha256', $raw, 'old-overlap-webhook-secret'), 'evt_OldSecret');
        $this->assertFalse($result['queued']);
        $this->assertDatabaseCount('storefront_webhook_events', 1);
        $this->assertSame(0, $this->gateway->creations);
    }

    public function test_refunded_provider_payments_and_external_partial_refunds_reconcile_without_money_mutations(): void
    {
        $order = $this->order();
        $start = $this->capture($order);
        $amount = $start['amount'];
        $this->gateway->refunds['rfnd_External1'] = ['id' => 'rfnd_External1', 'payment_id' => 'pay_Test1', 'amount' => 2000, 'currency' => 'INR', 'status' => 'processed'];
        $this->gateway->payments['pay_Test1']['amount_refunded'] = 2000;
        app(PaymentService::class)->reconcile($order['order_id']);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'partially_refunded']);
        $this->gateway->refunds['rfnd_External2'] = ['id' => 'rfnd_External2', 'payment_id' => 'pay_Test1', 'amount' => $amount - 2000, 'currency' => 'INR', 'status' => 'processed'];
        $this->gateway->payments['pay_Test1']['amount_refunded'] = $amount;
        $this->gateway->payments['pay_Test1']['status'] = 'refunded';
        $oldFailed = $this->gateway->payments['pay_Test1'];
        $oldFailed['id'] = 'pay_OldFailed';
        $oldFailed['status'] = 'failed';
        $oldFailed['captured'] = false;
        $oldFailed['amount_refunded'] = 0;
        $this->gateway->payments = ['pay_OldFailed' => $oldFailed, ...$this->gateway->payments];
        app(PaymentService::class)->reconcile($order['order_id']);
        app(PaymentService::class)->reconcile($order['order_id']);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'refunded']);
        $this->assertDatabaseCount('storefront_financial_entries', 3);
        $this->assertSame(0, FinancialLedger::balance($order['order_id'])['net_received_minor']);
        $this->assertFalse(FinancialLedger::balance($order['order_id'])['bank_settlement_verified']);
        $this->assertSame(0, $this->gateway->refundCreations);
    }

    public function test_cod_collection_and_remittance_are_operator_evidence_based_and_idempotent(): void
    {
        $order = $this->order('cod');
        $operator = User::factory()->create();
        $service = app(CodAccounting::class);
        DB::table('orders')->where('order_id', $order['order_id'])->update(['status' => 'delivered']);
        DB::table('order_status_logs')->insert(['order_id' => $order['order_id'], 'status' => 'delivered', 'changed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($operator, 'web');
        $adminHtml = $this->get('/order-details/'.urlencode(encrypt($order['order_id'])))->assertOk()->getContent();
        $this->assertSame(1, substr_count($adminHtml, 'name="reference"'));
        $this->assertSame(1, substr_count($adminHtml, 'name="amount"'));
        $this->assertSame(1, substr_count($adminHtml, 'name="kind"'));
        $key = (string) Str::uuid();
        $amount = $order['quote']['total_minor'];
        $this->postJson('/orders/'.$order['order_id'].'/cod-receipt', ['amount' => '10.00', 'reference' => $key, 'receipt_reference' => 'receipt-001', 'kind' => 'collection'])->assertOk();
        $service->record($order['order_id'], 1000, $key, 'receipt-001', $operator->id);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'unpaid']);
        $service->record($order['order_id'], $amount - 1000, (string) Str::uuid(), 'receipt-002', $operator->id);
        $service->record($order['order_id'], $amount, (string) Str::uuid(), 'deposit-001', $operator->id, true);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'paid']);
        $this->assertDatabaseCount('storefront_financial_entries', 3);
        $this->assertTrue(app(\App\Services\Storefront\ReturnService::class)->eligibility($this->customer, $order['order_id'])['items'][0]['eligible']);
        try {
            $service->record($order['order_id'], 1, (string) Str::uuid(), 'over-collection', $operator->id);
            $this->fail('Excess collection accepted.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
    }

    public function test_operations_expire_reservations_and_schedule_is_disabled_in_production(): void
    {
        $order = $this->order();
        $this->travel(16)->minutes();
        $result = app(FinancialOperations::class)->run();
        $this->assertSame(1, $result['expired_reviewed']);
        $this->assertDatabaseHas('storefront_checkouts', ['order_id' => $order['order_id'], 'state' => 'released']);
        $this->assertSame(20, DB::table('products')->where('product_id', $this->product)->value('stock'));
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->filter(fn ($event) => str_contains($event->command ?? '', 'storefront:financial-maintenance'));
        $this->assertCount(1, $events);
        $this->assertSame('* * * * *', $events->first()->expression);
        $mutex = $events->first()->mutex;
        $this->assertSame('database', $mutex->store);
        $this->assertTrue($mutex->create($events->first()));
        $this->assertFalse($mutex->create($events->first()));
        $mutex->forget($events->first());
        $this->app->detectEnvironment(fn () => 'production');
        $this->assertFalse($events->first()->filtersPass($this->app));
    }

    public function test_late_capture_refund_is_operator_requested_once_and_closes_the_released_balance(): void
    {
        $order = $this->order();
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        app(CheckoutService::class)->release($this->customer, $order['order_id']);
        $this->gateway->paymentState($start['razorpayOrderId'], 'captured');
        app(PaymentService::class)->reconcile($order['order_id']);
        $this->assertDatabaseHas('storefront_checkouts', ['order_id' => $order['order_id'], 'state' => 'refund_required']);
        $refunds = app(\App\Services\Storefront\RefundService::class);
        $this->assertSame('pending', $refunds->lateCapture($order['order_id'])['status']);
        $refunds->lateCapture($order['order_id']);
        $this->assertSame(1, $this->gateway->refundCreations);
        $this->gateway->refunds['rfnd_Test1']['status'] = 'processed';
        app(\App\Services\Storefront\ProviderRefunds::class)->observe('rfnd_Test1');
        $this->assertDatabaseHas('storefront_checkouts', ['order_id' => $order['order_id'], 'state' => 'refunded']);
        $this->assertSame(0, FinancialLedger::balance($order['order_id'])['net_received_minor']);
        $this->assertSame(20, DB::table('products')->where('product_id', $this->product)->value('stock'));
        $this->assertDatabaseCount('sales_returns', 0);
    }

    public function test_webhook_worker_lease_recovers_after_crash_without_duplicate_capture(): void
    {
        $order = $this->order();
        $start = $this->capture($order);
        $raw = json_encode(['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'pay_Test1', 'order_id' => $start['razorpayOrderId']]]]]);
        $processor = app(WebhookProcessor::class);
        $processor->receive($raw, hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'evt_LeaseCrash');
        DB::table('storefront_webhook_events')->where('event_id', 'evt_LeaseCrash')->update(['lease_token' => 'crashed-worker', 'lease_expires_at' => now()->addMinutes(5)]);
        $this->assertSame(0, $processor->drain());
        $this->travel(6)->minutes();
        $this->assertSame(1, $processor->drain());
        $this->assertSame(0, $processor->drain());
        $this->assertDatabaseCount('storefront_financial_entries', 1);
        $this->assertSame(1, $this->gateway->creations);
    }

    public function test_one_inconsistent_expired_reservation_does_not_starve_other_customers(): void
    {
        $first = $this->order();
        DB::table('orders')->where('order_id', $first['order_id'])->update(['payment_status' => 'paid']);
        $this->bag(1, $this->other);
        $address = $this->addressFor($this->other);
        $quote = app(CheckoutService::class)->quote($this->other, $address, '', 'online');
        $second = app(CheckoutService::class)->place($this->other, $quote['quote_id'], (string) Str::uuid());
        $this->travel(16)->minutes();
        [$done, $errors] = app(FinancialOperations::class)->expireReserved();
        $this->assertSame(1, $done);
        $this->assertSame(1, $errors);
        $this->assertDatabaseHas('storefront_checkouts', ['order_id' => $first['order_id'], 'state' => 'reserved']);
        $this->assertDatabaseHas('storefront_checkouts', ['order_id' => $second['order_id'], 'state' => 'released']);
        $this->assertDatabaseHas('storefront_operational_alerts', ['code' => 'reservation_expiry', 'reference' => (string) $first['order_id']]);
        $this->assertSame([0, 0], app(FinancialOperations::class)->expireReserved());
    }

    public function test_pending_provider_visibility_retries_do_not_resubmit_refunds_or_create_payments(): void
    {
        $order = $this->order();
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        $this->gateway->paymentState($start['razorpayOrderId'], 'authorized');
        $raw = json_encode(['event' => 'payment.authorized', 'payload' => ['payment' => ['entity' => ['id' => 'pay_Test1', 'order_id' => $start['razorpayOrderId']]]]]);
        app(WebhookProcessor::class)->receive($raw, hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'evt_Authorized');
        $this->assertFalse(app(WebhookProcessor::class)->process('evt_Authorized'));
        $this->assertDatabaseHas('storefront_webhook_events', ['event_id' => 'evt_Authorized', 'processed_at' => null]);
        $this->gateway->paymentState($start['razorpayOrderId'], 'captured');
        $this->travel(31)->seconds();
        $this->assertTrue(app(WebhookProcessor::class)->process('evt_Authorized'));
        $this->assertSame(0, $this->gateway->refundCreations);
        $this->assertSame(1, $this->gateway->creations);
        $this->assertDatabaseHas('storefront_operational_alerts', ['code' => 'webhook_reconciliation', 'reference' => 'evt_Authorized']);
        $this->assertNotNull(DB::table('storefront_operational_alerts')->where('reference', 'evt_Authorized')->value('resolved_at'));
    }

    public function test_shipment_mail_preserves_snapshot_and_does_not_add_shipping_twice(): void
    {
        $placed = $this->order('cod', 2);
        $invoice = app(OrderDocuments::class)->generate($placed['order_id']);
        DB::table('customers')->where('customer_id', $this->customer)->update(['first_name' => 'Changed later']);
        DB::table('products')->where('product_id', $this->product)->update(['product_name' => 'Changed later product']);
        $order = \App\Models\Order::disableCache()->with(['customer', 'items.product'])->findOrFail($placed['order_id']);
        $mail = new \App\Mail\sentOrderDespatchedMail($order, route('download.invoice', $invoice->invoice_number.'.pdf'));
        $html = $mail->render();
        $this->assertStringContainsString('Test bag', $html);
        $this->assertStringContainsString('240.00', $html);
        $this->assertStringNotContainsString('290.00', $html);
        $this->assertStringNotContainsString('Changed later', $html);
        $this->assertCount(1, $mail->attachments());
    }

    public function test_admin_provisioning_rejects_missing_credentials_and_preserves_existing_accounts(): void
    {
        $email = 'provisioning@example.invalid';
        $original = User::factory()->create(['email' => $email]);
        $hash = $original->password;
        // Dotenv may populate $_ENV/$_SERVER from the operator's local .env.
        // Isolate all adapters, not only getenv(), and restore the caller's values.
        $keys = ['VANTHARA_BOOTSTRAP_ADMIN_EMAIL', 'VANTHARA_BOOTSTRAP_ADMIN_PASSWORD'];
        $saved = [];
        foreach ($keys as $key) {
            $saved[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            unset($_ENV[$key], $_SERVER[$key]);
        }
        putenv('VANTHARA_BOOTSTRAP_ADMIN_EMAIL='.$email);
        putenv('VANTHARA_BOOTSTRAP_ADMIN_PASSWORD');
        try {
            try {
                (new \Database\Seeders\AdminTableSeeder)->run();
                $this->fail('An implicit admin password was accepted.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('explicit valid email', $error->getMessage());
            }
            putenv('VANTHARA_BOOTSTRAP_ADMIN_PASSWORD=IsolatedProvisioningPassword123!');
            (new \Database\Seeders\AdminTableSeeder)->run();
            $this->assertSame($hash, $original->fresh()->password);
            $this->assertSame(1, User::where('email', $email)->count());
        } finally {
            foreach ($saved as $key => [$process, $environment, $server]) {
                putenv($process === false ? $key : $key.'='.$process);
                if ($environment !== null) { $_ENV[$key] = $environment; }
                if ($server !== null) { $_SERVER[$key] = $server; }
            }
        }
    }

    public function test_signed_malformed_webhook_is_rejected_and_replay_restores_legacy_pending_binding(): void
    {
        $processor = app(WebhookProcessor::class);
        try {
            $processor->receive('{', hash_hmac('sha256', '{', config('storefront.webhook_secret')), 'evt_InvalidJson');
            $this->fail('Malformed webhook accepted.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(422, $error->getStatusCode());
        }
        $order = $this->order();
        $start = $this->capture($order);
        $raw = json_encode(['event' => 'order.paid', 'payload' => ['order' => ['entity' => ['id' => $start['razorpayOrderId']]]]]);
        DB::table('storefront_webhook_events')->insert(['event_id' => 'evt_LegacyPending', 'body_hash' => hash('sha256', $raw), 'event_type' => 'order.paid', 'created_at' => now(), 'updated_at' => now()]);
        $processor->receive($raw, hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'evt_LegacyPending');
        $this->assertTrue($processor->process('evt_LegacyPending'));
        $this->assertDatabaseCount('storefront_financial_entries', 1);
    }

    public function test_async_queue_receipt_does_not_contact_provider_and_conflicting_replays_are_rejected(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        config(['queue.default' => 'database']);
        $order = $this->order();
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        $raw = json_encode(['event' => 'order.paid', 'payload' => ['order' => ['entity' => ['id' => $start['razorpayOrderId']]]]]);
        $processor = app(WebhookProcessor::class);
        $processor->receive($raw, hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'evt_Async');
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessStorefrontWebhook::class);
        $this->assertDatabaseHas('storefront_webhook_events', ['event_id' => 'evt_Async', 'processed_at' => null]);
        $this->assertDatabaseCount('storefront_financial_entries', 0);
        $processor->receive($raw, hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'evt_SameBody');
        $this->assertDatabaseCount('storefront_webhook_events', 1);
        $different = str_replace('order.paid', 'payment.authorized', $raw);
        try {
            $processor->receive($different, hash_hmac('sha256', $different, config('storefront.webhook_secret')), 'evt_Async');
            $this->fail('Conflicting event ID accepted.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
    }

    public function test_database_queue_worker_reconciles_a_durable_webhook_job(): void
    {
        config(['queue.default' => 'database', 'queue.connections.database.connection' => 'sqlite']);
        $order = $this->order();
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        $raw = json_encode(['event' => 'order.paid', 'payload' => ['order' => ['entity' => ['id' => $start['razorpayOrderId']]]]]);
        app(WebhookProcessor::class)->receive($raw, hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'evt_RealQueue');
        $this->assertDatabaseCount('jobs', 1);
        $this->gateway->paymentState($start['razorpayOrderId'], 'captured');
        $this->assertSame(0, Artisan::call('queue:work', ['connection' => 'database', '--queue' => 'storefront-financial', '--once' => true, '--tries' => 1]));
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'paid']);
        $this->assertNotNull(DB::table('storefront_webhook_events')->where('event_id', 'evt_RealQueue')->value('processed_at'));
    }

    public function test_shipment_rolls_back_on_invoice_mismatch_then_queues_snapshot_email_after_commit(): void
    {
        DB::table('customers')->where('customer_id', $this->customer)->update(['email' => 'original@example.invalid']);
        $order = $this->order('cod');
        $carrier = DB::table('delivery_partners')->insertGetId(['partner_name' => 'Test assigned carrier', 'created_at' => now(), 'updated_at' => now()], 'partner_id');
        $this->actingAs(User::factory()->create(), 'web');
        $shipment = ['status' => 'despatched', 'unique_tracking_id' => 'TEST-TRACKING', 'approx_delivery_date' => now()->addDays(3)->toDateString(), 'selectAgent' => $carrier];
        DB::table('orders')->where('order_id', $order['order_id'])->increment('total_amount', 1);
        $this->putJson('/update-order-status/'.$order['order_id'], $shipment)->assertConflict();
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'status' => 'ordered', 'delivery_tracking_id' => null]);
        $this->assertDatabaseCount('invoices', 0);
        DB::table('orders')->where('order_id', $order['order_id'])->decrement('total_amount', 1);
        DB::table('customers')->where('customer_id', $this->customer)->update(['email' => null, 'first_name' => 'Changed later']);
        \Illuminate\Support\Facades\Mail::fake();
        $this->putJson('/update-order-status/'.$order['order_id'], $shipment)->assertOk();
        $this->assertDatabaseHas('storefront_outbox', ['kind' => 'order.shipped', 'state' => 'pending']);
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        app(\App\Services\Storefront\Outbox::class)->dispatch();
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\sentOrderDespatchedMail::class, fn ($mail) => $mail->hasTo('original@example.invalid') && $mail->name === 'Test');
        $this->postJson('/today_deliveries/add_followup', ['order_id' => $order['order_id'], 'delivery_status' => 'delivered', 'email' => 'changed@example.invalid'])->assertOk();
        app(\App\Services\Storefront\Outbox::class)->dispatch();
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OrderDeliveredMail::class, fn ($mail) => $mail->hasTo('original@example.invalid') && $mail->name === 'Test' && $mail->delivered_date !== null);
    }
}
