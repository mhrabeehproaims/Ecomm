<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\PaymentGateway;
use App\Services\Storefront\PaymentService;
use App\Services\Storefront\RefundService;
use App\Services\Storefront\ReturnService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Support\FakeStorefrontGateway;
use Tests\Support\StorefrontFixtures;
use Tests\TestCase;

class StorefrontTransactionsTest extends TestCase
{
    use StorefrontFixtures;

    private FakeStorefrontGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Never use the application's configured file or a production connection.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null,
            'cache.default' => 'array', 'cache.limiter' => 'array', 'storefront.test_checkout' => true, 'storefront.cod_enabled' => true,
            'storefront.shipping_minor' => ['kerala (KL)' => 0, 'tamil_nadu (TN)' => 5000],
            'storefront.shipping_tax_basis_points' => 0,
            'storefront.razorpay_key' => 'rzp_test_fixture', 'storefront.razorpay_secret' => 'test-provider-secret-not-a-real-key',
            'storefront.webhook_secret' => 'test-webhook-secret-at-least-16-characters']);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        Http::preventStrayRequests();
        $this->gateway = new FakeStorefrontGateway;
        $this->app->instance(PaymentGateway::class, $this->gateway);
        $this->fixtures();
        $this->asCustomer($this->customer);
    }

    private function asCustomer(int $id): void
    {
        $this->app['auth']->forgetGuards();
        $this->withToken(Customer::disableCache()->findOrFail($id)->createToken('test')->plainTextToken);
    }

    private function quote(string $method = 'online', string $coupon = '', ?int $address = null): array
    {
        return app(CheckoutService::class)->quote($this->customer, $address ?? $this->address, $coupon, $method);
    }

    private function place(string $method = 'online', string $coupon = ''): array
    {
        $q = $this->quote($method, $coupon);

        return app(CheckoutService::class)->place($this->customer, $q['quote_id'], (string) Str::uuid());
    }

    private function payment(array $order, string $status = 'captured'): array
    {
        $start = app(PaymentService::class)->start($this->customer, $order['order_id']);
        $this->gateway->paymentState($start['razorpayOrderId'], $status);

        return ['razorpay_order_id' => $start['razorpayOrderId'], 'razorpay_payment_id' => 'pay_Test1',
            'razorpay_signature' => hash_hmac('sha256', $start['razorpayOrderId'].'|pay_Test1', config('storefront.razorpay_secret'))];
    }

    private function webhook(array $body, string $event = 'evt_Test1')
    {
        $raw = json_encode($body);

        return $this->call('POST', '/api/razorpay/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $raw, config('storefront.webhook_secret')), 'HTTP_X_RAZORPAY_EVENT_ID' => $event], $raw);
    }

    private function deliver(int $order, int $daysAgo = 0): void
    {
        DB::table('orders')->where('order_id', $order)->update(['status' => 'delivered', 'payment_status' => 'paid']);
        DB::table('order_status_logs')->insert(['order_id' => $order, 'status' => 'delivered', 'changed_at' => now()->subDays($daysAgo), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function returnPayload(int $order, int $quantity = 1): array
    {
        return ['order_id' => $order, 'items' => [['product_id' => encrypt($this->product), 'quantity' => $quantity, 'price' => 0.01]],
            'return_type' => 'refund', 'return_reason' => 'Test item not suitable', 'product_condition' => 'unused', 'request_key' => (string) Str::uuid()];
    }

    public function test_customer_registration_login_logout_and_inactive_access(): void
    {
        $this->postJson('/api/create-customer', ['first_name' => 'Country code', 'phone' => '+919000000099', 'password' => 'StrongPassword123!'])->assertOk();
        $this->postJson('/api/login', ['mobile' => '+919000000099', 'password' => 'StrongPassword123!'])->assertOk();
        $this->postJson('/api/create-customer', ['first_name' => 'New', 'phone' => '9000000099', 'password' => 'StrongPassword123!', 'status' => 0])->assertOk();
        $this->assertDatabaseHas('customers', ['phone' => '9000000099', 'status' => 1]);
        $this->postJson('/api/login', ['mobile' => '9000000099', 'password' => 'bad'])->assertUnauthorized();
        $token = $this->postJson('/api/login', ['mobile' => '9000000099', 'password' => 'StrongPassword123!'])->assertOk()->json('token');
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/show-customer')->assertUnauthorized();
        DB::table('customers')->where('customer_id', $this->customer)->update(['status' => 0]);
        $this->postJson('/api/login', ['mobile' => '9000000011', 'password' => 'FixturePassword123!'])->assertForbidden();
        $this->asCustomer($this->customer);
        $this->getJson('/api/cart')->assertForbidden();
        $this->postJson('/api/logout')->assertOk();
    }

    public function test_profile_cannot_change_status_or_change_password_without_old_password(): void
    {
        $this->postJson('/api/update-customer', ['first_name' => 'Updated', 'phone' => '9000000011', 'status' => 0])->assertOk();
        $this->assertDatabaseHas('customers', ['customer_id' => $this->customer, 'status' => 1]);
        $this->postJson('/api/update-customer', ['first_name' => 'Updated', 'phone' => '9000000011', 'new_password' => 'PasswordNew123!', 'new_password_confirmation' => 'PasswordNew123!'])->assertUnprocessable();
    }

    public function test_address_ownership_and_optional_fields(): void
    {
        $foreign = $this->addressFor($this->other);
        $data = ['phone' => '9000000011', 'address_line1' => 'Changed', 'city' => 'Test', 'state' => 'kerala (KL)', 'postal_code' => '673001'];
        $this->putJson('/api/delivery-address/'.$foreign, $data)->assertNotFound();
        $this->putJson('/api/delivery-address/'.$this->address, $data)->assertOk();
        $this->postJson('/api/checkout/quote', ['address_id' => $foreign, 'payment_method' => 'online'])->assertNotFound();
        $this->assertDatabaseHas('delivery_addresses', ['address_id' => $foreign, 'address_line1' => 'Test lane']);
    }

    public function test_cart_uses_owned_lines_and_validates_quantities_stock_and_ciphertext(): void
    {
        $this->postJson('/api/cart', ['product_id' => encrypt($this->product), 'quantity' => 2])->assertCreated();
        $this->postJson('/api/cart/update-count', ['product_id' => encrypt($this->product), 'count' => -1])->assertUnprocessable();
        $this->postJson('/api/cart/update-count', ['product_id' => encrypt($this->product), 'count' => 21])->assertUnprocessable();
        $this->postJson('/api/cart', ['product_id' => 'malformed', 'quantity' => 1])->assertUnprocessable();
        $this->asCustomer($this->other);
        $this->postJson('/api/cart/remove-item', ['product_id' => encrypt($this->product)])->assertNotFound();
        $this->assertDatabaseHas('carts', ['user_id' => $this->customer, 'quantity' => 2]);
        $this->asCustomer($this->customer);
        $this->postJson('/api/cart/update-count', ['product_id' => encrypt($this->product), 'count' => 3])->assertOk();
        $this->postJson('/api/cart/remove-item', ['product_id' => encrypt($this->product)])->assertOk();
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_authoritative_totals_coupon_shipping_tax_and_integer_allocation(): void
    {
        $this->bag(3);
        DB::table('delivery_addresses')->where('address_id', $this->address)->update(['state' => 'tamil_nadu (TN)']);
        DB::table('coupons')->where('code', 'TESTSAVE')->update(['type' => 'percentage', 'value' => '12.50']);
        $quote = $this->postJson('/api/checkout/quote', ['address_id' => $this->address, 'coupon_code' => 'TESTSAVE', 'payment_method' => 'online', 'total_amount' => 1])->assertOk()->json('quote');
        $this->assertSame(30000, $quote['subtotal_minor']);
        $this->assertSame(3750, $quote['discount_minor']);
        $this->assertSame(5000, $quote['shipping_minor']);
        $this->assertSame(31250, $quote['total_minor']);
        $this->assertSame(4004, $quote['tax_minor']);
        $order = $this->postJson('/api/cart/checkout', ['quote_id' => $quote['quote_id'], 'idempotency_key' => (string) Str::uuid(), 'total_amount' => 1, 'payment_status' => 'paid'])->assertCreated()->json('order');
        $this->assertEquals(312.50, $order['total_amount']);
        $this->assertNull($order['paid_at']);
        $this->assertSame('unpaid', $order['payment_status']);
        $item = DB::table('order_items')->first();
        $this->assertEquals(0, $item->cgst_amount);
        $this->assertEquals('40.04', $item->igst_amount);
    }

    public function test_coupon_limits_minimum_and_dates_ignore_browser_totals(): void
    {
        $this->bag();
        $this->postJson('/api/coupon/validate', ['coupon_code' => 'TESTSAVE', 'total_amount' => 1])->assertOk()->assertJsonPath('discount_minor', 1000);
        $this->postJson('/api/coupon/validate', ['coupon_code' => 'WRONG', 'total_amount' => 99999])->assertUnprocessable();
        DB::table('products')->where('product_id', $this->product)->update(['retail' => 50]);
        $this->postJson('/api/coupon/validate', ['coupon_code' => 'TESTSAVE', 'total_amount' => 99999])->assertUnprocessable();
        DB::table('products')->where('product_id', $this->product)->update(['retail' => 100]);
        DB::table('coupons')->where('code', 'TESTSAVE')->update(['end_date' => now()->subDay()->toDateString()]);
        $this->postJson('/api/coupon/validate', ['coupon_code' => 'TESTSAVE'])->assertUnprocessable();
    }

    public function test_order_idempotency_and_immutable_snapshot(): void
    {
        $this->bag(2);
        $quote = $this->quote('cod', 'TESTSAVE');
        $key = (string) Str::uuid();
        $first = app(CheckoutService::class)->place($this->customer, $quote['quote_id'], $key);
        $this->assertSame($first['order_id'], app(CheckoutService::class)->place($this->customer, $quote['quote_id'], $key)['order_id']);
        $this->assertSame($first['order_id'], app(CheckoutService::class)->place($this->customer, $quote['quote_id'], (string) Str::uuid())['order_id']);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('coupon_usages', 1);
        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseHas('products', ['product_id' => $this->product, 'stock' => 18]);
        DB::table('delivery_addresses')->where('address_id', $this->address)->update(['address_line1' => 'Changed after order']);
        $this->assertSame('Test lane', app(CheckoutService::class)->order($this->customer, $first['order_id'])['address_snapshot']['address_line1']);
        $this->bag();
        $next = $this->quote('cod');
        $this->postJson('/api/checkout/orders', ['quote_id' => $next['quote_id'], 'idempotency_key' => $key])->assertConflict();
    }

    public function test_expired_and_changed_quotes_do_not_write_orders(): void
    {
        $this->bag();
        $quote = $this->quote();
        DB::table('storefront_quotes')->where('id', $quote['quote_id'])->update(['expires_at' => now()->subSecond()]);
        $this->postJson('/api/checkout/orders', ['quote_id' => $quote['quote_id'], 'idempotency_key' => (string) Str::uuid()])->assertConflict();
        $quote = $this->quote();
        DB::table('products')->where('product_id', $this->product)->update(['retail' => 101]);
        $this->postJson('/api/checkout/orders', ['quote_id' => $quote['quote_id'], 'idempotency_key' => (string) Str::uuid()])->assertConflict();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', ['stock' => 20]);
    }

    public function test_stock_and_order_changes_roll_back_on_database_failure(): void
    {
        $this->bag();
        $quote = $this->quote('cod');
        DB::unprepared("CREATE TRIGGER refuse_test_stock BEFORE UPDATE OF stock ON products BEGIN SELECT RAISE(ABORT, 'injected inventory failure'); END");
        try {
            app(CheckoutService::class)->place($this->customer, $quote['quote_id'], (string) Str::uuid());
            $this->fail('Expected rollback');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('injected inventory failure', $e->getMessage());
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('storefront_checkouts', 0);
        $this->assertDatabaseCount('stock_management', 0);
        $this->assertDatabaseHas('products', ['stock' => 20]);
    }

    public function test_provider_order_creation_is_owned_unique_and_reused_on_retry(): void
    {
        $this->bag();
        $order = $this->place();
        $first = $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertOk()->json();
        $second = $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertOk()->json();
        $this->assertSame($first['razorpayOrderId'], $second['razorpayOrderId']);
        $this->assertSame(1, $this->gateway->creations);
        $this->assertDatabaseCount('payments', 1);
        $this->asCustomer($this->other);
        $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertNotFound();
    }

    public function test_missing_live_keys_and_production_environment_cannot_enable_checkout(): void
    {
        $this->bag();
        $order = $this->place();
        config(['storefront.razorpay_key' => 'rzp_live_forbidden']);
        $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertStatus(503);
        $this->assertSame(0, $this->gateway->creations);
        $this->assertDatabaseCount('payments', 0);
        $this->app->detectEnvironment(fn () => 'production');
        $this->postJson('/api/checkout/quote', ['address_id' => $this->address, 'payment_method' => 'online'])->assertStatus(503);
        $this->getJson('/api/checkout/capabilities')->assertJsonPath('checkoutEnabled', false);
    }

    public function test_signature_amount_and_capture_are_verified_then_committed_once(): void
    {
        $this->bag(2);
        $order = $this->place('online', 'TESTSAVE');
        $data = $this->payment($order);
        $this->postJson('/api/razorpay/success', [...$data, 'razorpay_signature' => str_repeat('0', 64)])->assertUnprocessable();
        $this->gateway->payments['pay_Test1']['amount'] = 1;
        $this->postJson('/api/razorpay/success', $data)->assertConflict();
        $this->gateway->paymentState($data['razorpay_order_id'], 'captured');
        $this->postJson('/api/razorpay/success', $data)->assertOk()->assertJsonPath('paid', true);
        $this->postJson('/api/razorpay/success', $data)->assertOk();
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'paid']);
        $this->assertDatabaseCount('coupon_usages', 1);
        $this->assertDatabaseCount('storefront_payment_events', 1);
        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseHas('products', ['stock' => 18]);
    }

    public function test_pending_failed_retry_and_stale_failure_cannot_downgrade_capture(): void
    {
        $this->bag();
        $order = $this->place();
        $data = $this->payment($order, 'authorized');
        $this->postJson('/api/razorpay/success', $data)->assertStatus(202)->assertJsonPath('paid', false);
        $this->assertDatabaseCount('carts', 1);
        $this->gateway->paymentState($data['razorpay_order_id'], 'failed', 'pay_Failed1');
        $this->postJson('/api/razorpay/failed', ['razorpay_order_id' => $data['razorpay_order_id'], 'razorpay_payment_id' => 'pay_Failed1'])->assertStatus(202);
        $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertOk();
        $this->assertSame(1, $this->gateway->creations);
        $this->gateway->paymentState($data['razorpay_order_id'], 'captured');
        $this->postJson('/api/razorpay/success', $data)->assertOk();
        $this->postJson('/api/razorpay/failed', ['razorpay_order_id' => $data['razorpay_order_id'], 'razorpay_payment_id' => 'pay_Failed1'])->assertOk();
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'paid']);
    }

    public function test_webhook_signatures_duplicates_and_out_of_order_events(): void
    {
        $this->bag();
        $order = $this->place();
        $data = $this->payment($order);
        $body = ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'pay_Test1', 'order_id' => $data['razorpay_order_id']]]]];
        $this->postJson('/api/razorpay/webhook', $body)->assertStatus(400);
        $this->webhook($body)->assertOk()->assertJsonPath('duplicate', false);
        $this->webhook($body)->assertOk()->assertJsonPath('duplicate', true);
        $this->webhook($body, 'evt_NewIdSameBody')->assertOk()->assertJsonPath('duplicate', true);
        $this->webhook([...$body, 'event' => 'payment.failed'])->assertConflict();
        $this->gateway->paymentState($data['razorpay_order_id'], 'authorized', 'pay_OldAuthorized');
        $this->webhook(['event' => 'payment.authorized', 'payload' => ['payment' => ['entity' => ['id' => 'pay_OldAuthorized', 'order_id' => $data['razorpay_order_id']]]]], 'evt_Older')->assertOk();
        $this->assertDatabaseHas('orders', ['payment_status' => 'paid']);
        $this->assertDatabaseCount('storefront_payment_events', 1);
    }

    public function test_ambiguous_provider_creation_is_reconciled_without_duplicate_creation(): void
    {
        $this->bag();
        $order = $this->place();
        $this->gateway->loseOrderResponse = true;
        $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertStatus(503);
        $this->postJson('/api/razorpay/payment', ['order_id' => $order['order_id']])->assertConflict();
        $this->assertSame(1, $this->gateway->creations);
        $this->gateway->paymentState('order_Test1', 'captured');
        $this->assertTrue(app(PaymentService::class)->reconcile($order['order_id'])['paid']);
        $this->assertSame(1, $this->gateway->creations);
    }

    public function test_expiry_restores_stock_once_and_late_capture_requires_refund_review(): void
    {
        $this->bag();
        $order = $this->place();
        $data = $this->payment($order);
        DB::table('storefront_checkouts')->where('order_id', $order['order_id'])->update(['expires_at' => now()->subSecond()]);
        app(CheckoutService::class)->release($this->customer, $order['order_id'], true);
        app(CheckoutService::class)->release($this->customer, $order['order_id'], true);
        $this->assertDatabaseHas('products', ['stock' => 20]);
        $this->postJson('/api/razorpay/success', $data)->assertOk()->assertJsonPath('requires_review', true);
        $this->assertDatabaseHas('storefront_checkouts', ['state' => 'refund_required']);
        $this->assertDatabaseHas('orders', ['status' => 'rejected', 'payment_status' => 'paid']);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_returns_enforce_delivery_payment_policy_quantity_and_server_prices(): void
    {
        $this->bag(3);
        $order = $this->place('cod', 'TESTSAVE');
        $this->deliver($order['order_id']);
        $payload = $this->returnPayload($order['order_id']);
        $id = $this->postJson('/api/sales-return/save', $payload)->assertCreated()->json('return_id');
        $this->postJson('/api/sales-return/save', $payload)->assertCreated()->assertJsonPath('return_id', $id);
        $this->assertDatabaseHas('sales_returns', ['return_id' => $id, 'total_amount' => '96.67', 'status' => 'requested']);
        $this->postJson('/api/sales-return/save', [...$this->returnPayload($order['order_id'], 3), 'status' => 'refunded'])->assertUnprocessable();
        $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id'], 3))->assertUnprocessable();
        $this->asCustomer($this->other);
        $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertNotFound();
    }

    public function test_expired_undelivered_unpaid_or_undated_returns_are_rejected(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertUnprocessable();
        DB::table('orders')->where('order_id', $order['order_id'])->update(['status' => 'delivered', 'payment_status' => 'paid']);
        $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertUnprocessable();
        $this->deliver($order['order_id'], 8);
        $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertUnprocessable();
    }

    public function test_return_transitions_and_verified_refund_are_idempotent_without_automatic_restock(): void
    {
        $this->bag();
        $order = $this->place();
        $data = $this->payment($order);
        $this->postJson('/api/razorpay/success', $data)->assertOk();
        $this->deliver($order['order_id']);
        $id = $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertCreated()->json('return_id');
        foreach (['accepted', 'accepted', 'picked_up', 'collected'] as $state) {
            app(ReturnService::class)->transition($id, $state);
        }
        $result = app(RefundService::class)->request($id);
        $this->assertSame('pending', $result['status']);
        app(RefundService::class)->request($id);
        $this->assertSame(1, $this->gateway->refundCreations);
        $this->gateway->refunds['rfnd_Test1']['status'] = 'processed';
        app(RefundService::class)->reconcile('rfnd_Test1');
        app(RefundService::class)->reconcile('rfnd_Test1');
        $this->assertDatabaseHas('sales_returns', ['return_id' => $id, 'status' => 'refunded']);
        $this->assertDatabaseHas('products', ['stock' => 19]);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'refunded']);
    }

    public function test_tracking_and_order_history_are_owned_and_do_not_invent_carrier_data(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->getJson('/api/purchase/history')->assertOk()->assertJsonPath('data.0.address_snapshot.address_line1', 'Test lane');
        $this->getJson('/api/orders/'.$order['order_id'].'/tracking')->assertOk()->assertJsonPath('tracking.carrier_integration', null)->assertJsonCount(1, 'tracking.events');
        $this->asCustomer($this->other);
        $this->getJson('/api/orders/'.$order['order_id'])->assertNotFound();
        $this->getJson('/api/orders/'.$order['order_id'].'/tracking')->assertNotFound();
    }

    public function test_backend_login_rate_limit_is_enforced(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', ['mobile' => '9999999999', 'password' => 'bad'])->assertUnauthorized();
        }
        $this->postJson('/api/login', ['mobile' => '9999999999', 'password' => 'bad'])->assertTooManyRequests();
    }

    public function test_offer_prices_and_paise_rounding_are_authoritative(): void
    {
        DB::table('offer_zones')->insert(['offer_type' => 'test', 'applicable_product_ids' => json_encode([(string) $this->product]),
            'product_details' => json_encode([['product_id' => (string) $this->product, 'final_price' => '90.00']]), 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        $this->bag(2);
        $q = $this->quote('cod', 'TESTSAVE');
        $this->assertSame(18000, $q['subtotal_minor']);
        $this->assertSame(17000, $q['total_minor']);
        $this->assertSame(17000, array_sum(array_column($q['items'], 'total_minor')));
        $this->assertSame(1000, array_sum(array_column($q['items'], 'discount_minor')));
    }

    public function test_delivery_serviceability_and_shipping_tax_fail_closed(): void
    {
        $this->bag();
        DB::table('delivery_addresses')->where('address_id', $this->address)->update(['state' => 'unknown']);
        $this->postJson('/api/checkout/quote', ['address_id' => $this->address, 'payment_method' => 'online'])->assertUnprocessable();
        DB::table('delivery_addresses')->where('address_id', $this->address)->update(['state' => 'tamil_nadu (TN)']);
        config(['storefront.shipping_tax_basis_points' => null]);
        $this->postJson('/api/checkout/quote', ['address_id' => $this->address, 'payment_method' => 'online'])->assertUnprocessable();
        config(['storefront.shipping_tax_basis_points' => 1800]);
        $q = $this->quote();
        $this->assertSame(763, $q['shipping_tax_minor']);
        $this->assertSame(2288, $q['tax_minor']);
    }

    public function test_reserved_coupon_usage_is_released_or_committed_without_double_counting(): void
    {
        DB::table('coupons')->where('code', 'TESTSAVE')->update(['max_uses' => 1]);
        $this->bag();
        $order = $this->place('online', 'TESTSAVE');
        $this->bag(1, $this->other);
        $this->asCustomer($this->other);
        $this->postJson('/api/coupon/validate', ['coupon_code' => 'TESTSAVE'])->assertUnprocessable();
        app(CheckoutService::class)->release($this->customer, $order['order_id']);
        $this->postJson('/api/coupon/validate', ['coupon_code' => 'TESTSAVE'])->assertOk();
    }

    public function test_admin_pages_and_legacy_order_updates_are_preserved_but_unpaid_fulfillment_is_blocked(): void
    {
        $this->bag();
        $secure = $this->place();
        $this->actingAs(\App\Models\User::factory()->create(), 'web');
        $this->get('/products')->assertOk();
        $this->get('/orders')->assertOk();
        $this->putJson('/update-order-status/'.$secure['order_id'], ['status' => 'confirmed'])->assertConflict();
        $this->postJson('/today_deliveries/add_followup', ['order_id' => $secure['order_id'], 'delivery_status' => 'delivered'])->assertConflict();
        $this->putJson('/update-order-status/'.$secure['order_id'], ['status' => 'rejected'])->assertOk()->assertJsonPath('status2', 'rejected');
        $legacy = DB::table('orders')->insertGetId(['customer_id' => $this->customer, 'total_amount' => 100, 'status' => 'ordered', 'payment_method' => 'cod', 'payment_status' => 'unpaid', 'created_at' => now(), 'updated_at' => now()], 'order_id');
        $this->putJson('/update-order-status/'.$legacy, ['status' => 'confirmed'])->assertOk();
        $this->assertDatabaseHas('orders', ['order_id' => $legacy, 'status' => 'confirmed']);
    }

    public function test_committed_effects_preserve_stock_notifications_and_failed_effects_stay_retryable(): void
    {
        DB::table('products')->where('product_id', $this->product)->update(['stock' => 1, 'minimum_stock' => 0]);
        $this->bag();
        $order = $this->place('cod');
        $this->assertDatabaseHas('notifications', ['type' => 'stock', 'notifiable_id' => $this->product]);
        $this->assertDatabaseCount('storefront_outbox', 3);
        $notifications = DB::table('notifications')->count();
        app(\App\Services\Storefront\Outbox::class)->dispatch();
        $this->assertDatabaseCount('notifications', $notifications);
        \Illuminate\Support\Facades\Event::listen(\App\Events\NotifyOrderPlaced::class, fn () => throw new \RuntimeException('test transport unavailable'));
        DB::table('storefront_outbox')->where('kind', 'order.created')->update(['state' => 'pending']);
        app(\App\Services\Storefront\Outbox::class)->dispatch();
        $this->assertDatabaseHas('storefront_outbox', ['kind' => 'order.created', 'state' => 'pending']);
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id']]);
    }

    public function test_return_transition_cannot_skip_review_or_refund_verification(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->deliver($order['order_id']);
        $id = $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertCreated()->json('return_id');
        $this->actingAs(\App\Models\User::factory()->create(), 'web');
        $this->putJson('/update-return-status/'.$id, ['status' => 'refunded'])->assertConflict();
        foreach (['accepted', 'picked_up', 'collected'] as $state) {
            $this->putJson('/update-return-status/'.$id, ['status' => $state])->assertOk();
        }
        $this->putJson('/update-return-status/'.$id, ['status' => 'refunded'])->assertConflict();
        $this->putJson('/update-return-status/'.$id, ['status' => 'requested'])->assertConflict();
    }

    public function test_notification_payloads_do_not_expose_customer_records_and_returns_notify_admin(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->deliver($order['order_id']);
        $id = $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertCreated()->json('return_id');
        $this->assertDatabaseHas('notifications', ['type' => 'return', 'notifiable_id' => $order['order_id']]);
        $model = \App\Models\Order::disableCache()->with('customer')->find($order['order_id']);
        $payload = (new \App\Events\NotifyOrderPlaced('Test', 100, $model))->broadcastWith();
        $this->assertArrayNotHasKey('customer', $payload['order_details']);
        $return = \App\Models\SalesReturn::disableCache()->with('customer')->find($id);
        $payload = (new \App\Events\sendReturnNotification(['return' => $return, 'customer' => 'Test', 'order_id' => $order['order_id'], 'subtotal' => 100, 'item_id' => $this->product]))->broadcastWith();
        $this->assertSame(['return_id', 'total_amount'], array_keys($payload['return']));
    }

    public function test_refund_response_loss_can_be_recovered_without_another_refund(): void
    {
        $this->bag();
        $order = $this->place();
        $data = $this->payment($order);
        $this->postJson('/api/razorpay/success', $data)->assertOk();
        $this->deliver($order['order_id']);
        $id = $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id']))->assertCreated()->json('return_id');
        foreach (['accepted', 'picked_up', 'collected'] as $status) {
            app(ReturnService::class)->transition($id, $status);
        }
        $this->gateway->loseRefundResponse = true;
        try {
            app(RefundService::class)->request($id);
            $this->fail('Expected ambiguous response');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            $this->assertSame(503, $e->getStatusCode());
        }
        $this->assertSame('indeterminate', app(RefundService::class)->request($id)['status']);
        $this->gateway->refunds['rfnd_Test1']['status'] = 'processed';
        app(RefundService::class)->recover($id, 'rfnd_Test1');
        $this->assertSame(1, $this->gateway->refundCreations);
        $this->assertDatabaseHas('sales_returns', ['return_id' => $id, 'status' => 'refunded']);
    }

    public function test_wrong_currency_and_foreign_payment_callbacks_are_rejected(): void
    {
        $this->bag();
        $order = $this->place();
        $data = $this->payment($order);
        $this->gateway->payments['pay_Test1']['currency'] = 'USD';
        $this->postJson('/api/razorpay/success', $data)->assertConflict();
        $this->gateway->payments['pay_Test1']['currency'] = 'INR';
        $this->asCustomer($this->other);
        $this->postJson('/api/razorpay/success', $data)->assertNotFound();
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'unpaid']);
    }

    public function test_concrete_gateway_uses_fixed_test_endpoint_and_never_retries_an_ambiguous_mutation(): void
    {
        $this->bag();
        $order = $this->place();
        Http::fake(['api.razorpay.com/*' => Http::failedConnection()]);
        $service = new PaymentService(new \App\Services\Storefront\RazorpayTestGateway);
        try {
            $service->start($this->customer, $order['order_id']);
            $this->fail('Expected provider outage');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            $this->assertSame(503, $e->getStatusCode());
        }
        try {
            $service->start($this->customer, $order['order_id']);
            $this->fail('Expected reconciliation requirement');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => $r->url() === 'https://api.razorpay.com/v1/orders' && $r['amount'] === 10000 && $r['currency'] === 'INR');
    }

    public function test_checkout_is_not_enabled_for_an_arbitrary_database_even_in_local_mode(): void
    {
        config(['database.connections.sqlite.database' => database_path('not-an-isolated-test.sqlite')]);
        $this->assertFalse(\App\Services\Storefront\CheckoutAccess::enabled());
    }

    public function test_large_money_allocations_do_not_overflow_and_oversized_quotes_fail_validation(): void
    {
        $this->assertSame(9999999999, \App\Services\Storefront\Money::ratio(9999999999, 9999999999, 9999999999));
        DB::table('products')->where('product_id', $this->product)->update(['retail' => '99999999.99', 'stock' => 99]);
        $this->bag(99);
        $this->postJson('/api/checkout/quote', ['address_id' => $this->address, 'payment_method' => 'online'])->assertUnprocessable();
    }

    public function test_partially_refunded_orders_keep_remaining_purchased_items_eligible(): void
    {
        $this->bag(2);
        $order = $this->place();
        $data = $this->payment($order);
        $this->postJson('/api/razorpay/success', $data)->assertOk();
        $this->deliver($order['order_id']);
        $id = $this->postJson('/api/sales-return/save', $this->returnPayload($order['order_id'], 1))->assertCreated()->json('return_id');
        foreach (['accepted', 'picked_up', 'collected'] as $state) {
            app(ReturnService::class)->transition($id, $state);
        }
        app(RefundService::class)->request($id);
        $this->gateway->refunds['rfnd_Test1']['status'] = 'processed';
        app(RefundService::class)->reconcile('rfnd_Test1');
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'payment_status' => 'partially_refunded']);
        $this->getJson('/api/orders/'.$order['order_id'].'/return-eligibility')->assertOk()->assertJsonPath('eligibility.items.0.remaining_quantity', 1)->assertJsonPath('eligibility.items.0.eligible', true);
    }

    public function test_private_admin_notification_channels_require_an_admin_session(): void
    {
        config(['broadcasting.default' => 'pusher', 'broadcasting.connections.pusher.key' => 'test_broadcast_key',
            'broadcasting.connections.pusher.secret' => 'test_broadcast_secret', 'broadcasting.connections.pusher.app_id' => 'test_id',
            'broadcasting.connections.pusher.options.cluster' => 'ap2']);
        \Illuminate\Support\Facades\Broadcast::purge();
        require base_path('routes/channels.php');
        $this->actingAs(\App\Models\User::factory()->create(), 'web');
        $this->postJson('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => 'private-my-channel'])->assertOk()->assertJsonStructure(['auth']);
        $this->actingAs(Customer::disableCache()->find($this->customer), 'web');
        $this->postJson('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => 'private-my-channel'])->assertForbidden();
        $this->postJson('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => 'private-sales-return'])->assertForbidden();
    }

    public function test_admin_cannot_silently_change_an_accepted_storefront_shipping_charge(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->actingAs(\App\Models\User::factory()->create(), 'web');
        $this->putJson('/update-order-status/'.$order['order_id'], ['status' => 'confirmed', 'approx_delivery_charge' => '0.01'])->assertUnprocessable();
        $this->putJson('/update-order-status/'.$order['order_id'], ['status' => 'confirmed'])->assertOk();
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'delivery_charge' => 0]);
    }

    public function test_collected_replacements_create_one_real_linked_order_and_deduct_stock_once(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->deliver($order['order_id']);
        $payload = [...$this->returnPayload($order['order_id']), 'return_type' => 'replacement'];
        $id = $this->postJson('/api/sales-return/save', $payload)->assertCreated()->json('return_id');
        foreach (['accepted', 'picked_up', 'collected', 'replaced', 'replaced'] as $status) {
            app(ReturnService::class)->transition($id, $status);
        }
        $replacement = DB::table('sales_returns')->where('return_id', $id)->value('reissued_order_id');
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseHas('orders', ['order_id' => $replacement, 'reference_order_id' => $order['order_id'], 'total_amount' => 0, 'status' => 'reissued', 'payment_status' => 'not_required']);
        $this->assertDatabaseHas('products', ['stock' => 18]);
        $this->assertSame('Test lane', app(CheckoutService::class)->order($this->customer, $replacement)['address_snapshot']['address_line1']);
    }

    public function test_in_transit_updates_keep_legacy_order_value_but_use_valid_status_log_enum(): void
    {
        $this->bag();
        $order = $this->place('cod');
        $this->actingAs(\App\Models\User::factory()->create(), 'web');
        $this->putJson('/update-order-status/'.$order['order_id'], ['status' => 'ontransist'])->assertOk();
        $this->assertDatabaseHas('orders', ['order_id' => $order['order_id'], 'status' => 'ontransist']);
        $this->assertDatabaseHas('order_status_logs', ['order_id' => $order['order_id'], 'status' => 'ontransit']);
    }
}
