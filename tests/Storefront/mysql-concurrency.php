<?php

$test = require __DIR__.'/mysql-environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\PaymentGateway;
use App\Services\Storefront\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$fixture = new class
{
    use Tests\Support\StorefrontFixtures;

    public function seed()
    {
        $this->fixtures();

        return [$this->customer, $this->other, $this->product, $this->address];
    }
};
if (DB::table('customers')->exists()) {
    throw new RuntimeException('Use a fresh isolated MySQL test DB; no existing sample data will be overwritten.');
}
[$customer, $other, $product, $address] = $fixture->seed();
$checkout = app(CheckoutService::class);
$stamp = ['created_at' => now(), 'updated_at' => now()];
$results = [];
$assert = function ($condition, $message) {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};
$newProduct = function (int $stock) use ($product, $stamp) {
    $copy = (array) DB::table('products')->where('product_id', $product)->first();
    unset($copy['product_id']);
    $copy['stock'] = $stock;
    $copy['product_name'] = 'Concurrent test '.Str::uuid();

    return DB::table('products')->insertGetId($copy + $stamp, 'product_id');
};
$newCustomer = function () use ($stamp) {
    return DB::table('customers')->insertGetId(['first_name' => 'Concurrent test', 'phone' => '93'.random_int(10000000, 99999999),
        'password' => Illuminate\Support\Facades\Hash::make('IsolatedPassword123!'), 'status' => 1] + $stamp, 'customer_id');
};
$prepare = function ($c, $p, $method = 'cod', $coupon = '') use ($checkout, $stamp) {
    $a = DB::table('delivery_addresses')->insertGetId(['customer_id' => $c, 'phone' => '9000000011', 'address_line1' => 'Concurrent sample lane',
        'city' => 'Test', 'state' => 'kerala (KL)', 'country' => 'India', 'postal_code' => '673001', 'is_default' => true] + $stamp, 'address_id');
    DB::table('carts')->insert(['user_id' => $c, 'product_id' => $p, 'quantity' => 1, 'price' => 1] + $stamp);

    return $checkout->quote($c, $a, $coupon, $method);
};
$race = function (array $calls) use ($test, $assert) {
    $barrier = $test['directory'].'/barrier-'.Str::uuid();
    $processes = [];
    foreach ($calls as [$mode, $c, $reference, $key]) {
        $proc = proc_open([PHP_BINARY, __DIR__.'/concurrency-worker.php', $barrier, $mode, (string) $c, (string) $reference, $key],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $test['root'], null, ['bypass_shell' => true]);
        $assert(is_resource($proc), 'Could not launch concurrency worker');
        fclose($pipes[0]);
        $processes[] = [$proc, $pipes];
    }
    file_put_contents($barrier, 'go');
    $output = [];
    foreach ($processes as [$proc, $pipes]) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($proc);
        $assert($status === 0, 'Worker failed: '.$stderr);
        $output[] = json_decode($stdout, true, flags: JSON_THROW_ON_ERROR);
    }

    return $output;
};

// Last unit of stock, two customers, two independent MySQL transactions.
$p = $newProduct(1);
$q1 = $prepare($customer, $p);
$q2 = $prepare($other, $p);
$raceResult = $race([['place', $customer, $q1['quote_id'], (string) Str::uuid()], ['place', $other, $q2['quote_id'], (string) Str::uuid()]]);
$assert(count(array_filter($raceResult, fn ($r) => $r['ok'])) === 1 && (int) DB::table('products')->where('product_id', $p)->value('stock') === 0, 'Stock oversold or no successful checkout');
$results['last_unit_stock'] = $raceResult;

// Same quote/key submitted simultaneously produces precisely one order/deduction.
$c = $newCustomer();
$p = $newProduct(2);
$q = $prepare($c, $p);
$key = (string) Str::uuid();
$before = DB::table('orders')->count();
$raceResult = $race([['place', $c, $q['quote_id'], $key], ['place', $c, $q['quote_id'], $key]]);
$assert($raceResult[0]['ok'] && $raceResult[1]['ok'] && $raceResult[0]['order_id'] === $raceResult[1]['order_id']
    && DB::table('orders')->count() === $before + 1 && (int) DB::table('products')->where('product_id', $p)->value('stock') === 1, 'Idempotency race failed');
$results['duplicate_order'] = $raceResult;

// Last global coupon use with enough stock for both; only one discount may be committed.
$c1 = $newCustomer();
$c2 = $newCustomer();
$p = $newProduct(2);
$coupon = DB::table('coupons')->insertGetId(['code' => 'RACEONCE', 'type' => 'fixed', 'value' => 10, 'min_order_amount' => 0, 'max_uses' => 1,
    'max_uses_per_user' => 1, 'is_active' => 1] + $stamp);
$q1 = $prepare($c1, $p, 'cod', 'RACEONCE');
$q2 = $prepare($c2, $p, 'cod', 'RACEONCE');
$raceResult = $race([['place', $c1, $q1['quote_id'], (string) Str::uuid()], ['place', $c2, $q2['quote_id'], (string) Str::uuid()]]);
$assert(count(array_filter($raceResult, fn ($r) => $r['ok'])) === 1 && (int) DB::table('coupon_usages')->where('coupon_id', $coupon)->sum('usage_count') === 1, 'Coupon overused');
$results['last_coupon_use'] = $raceResult;

// Callback/webhook-style simultaneous capture reconciliation.
$fake = new Tests\Support\FakeStorefrontGateway;
$app->instance(PaymentGateway::class, $fake);
$c = $newCustomer();
$p = $newProduct(1);
$q = $prepare($c, $p, 'online');
$order = $checkout->place($c, $q['quote_id'], (string) Str::uuid());
app(PaymentService::class)->start($c, $order['order_id']);
$raceResult = $race([['capture', $c, $order['order_id'], ''], ['capture', $c, $order['order_id'], '']]);
$assert($raceResult[0]['ok'] && $raceResult[1]['ok'] && DB::table('storefront_payment_events')->count() === 1
    && DB::table('storefront_checkouts')->where('order_id', $order['order_id'])->value('state') === 'committed', 'Capture reconciliation race failed');
$results['duplicate_capture'] = $raceResult;

// Expiry racing a capture must either keep reserved stock committed or flag refund review.
$c = $newCustomer();
$p = $newProduct(1);
$q = $prepare($c, $p, 'online');
$order = $checkout->place($c, $q['quote_id'], (string) Str::uuid());
app(PaymentService::class)->start($c, $order['order_id']);
DB::table('storefront_checkouts')->where('order_id', $order['order_id'])->update(['expires_at' => now()->subSecond()]);
$raceResult = $race([['capture', $c, $order['order_id'], ''], ['release', $c, $order['order_id'], '']]);
$state = DB::table('storefront_checkouts')->where('order_id', $order['order_id'])->value('state');
$stock = (int) DB::table('products')->where('product_id', $p)->value('stock');
$assert(in_array($state, ['committed', 'refund_required']) && $stock === ($state === 'committed' ? 0 : 1)
    && DB::table('orders')->where('order_id', $order['order_id'])->value('payment_status') === 'paid', 'Expiry/capture inventory invariant failed');
$results['expiry_vs_capture'] = ['workers' => $raceResult, 'state' => $state, 'stock' => $stock];
// Two repeated admin confirmations cannot create or deduct two replacement orders.
$c = $newCustomer();
$p = $newProduct(3);
$q = $prepare($c, $p, 'cod');
$order = $checkout->place($c, $q['quote_id'], (string) Str::uuid());
DB::table('orders')->where('order_id', $order['order_id'])->update(['status' => 'delivered', 'payment_status' => 'paid']);
DB::table('order_status_logs')->insert(['order_id' => $order['order_id'], 'status' => 'delivered', 'changed_at' => now()] + $stamp);
$returnId = app(App\Services\Storefront\ReturnService::class)->request($c, ['order_id' => $order['order_id'], 'items' => [['product_id' => $p, 'quantity' => 1]],
    'return_type' => 'replacement', 'return_reason' => 'Sample replacement test', 'product_condition' => 'damaged', 'request_key' => (string) Str::uuid()]);
foreach (['accepted', 'picked_up', 'collected'] as $status) {
    app(App\Services\Storefront\ReturnService::class)->transition($returnId, $status);
}
$before = DB::table('orders')->count();
$raceResult = $race([['replace', $c, $returnId, ''], ['replace', $c, $returnId, '']]);
$assert($raceResult[0]['ok'] && $raceResult[1]['ok'] && $raceResult[0]['order_id'] === $raceResult[1]['order_id']
    && DB::table('orders')->count() === $before + 1 && (int) DB::table('products')->where('product_id', $p)->value('stock') === 1, 'Replacement idempotency race failed');
$results['duplicate_replacement'] = $raceResult;
file_put_contents($test['directory'].'/mysql-concurrency-results-'.$databaseName.'.json', json_encode($results, JSON_PRETTY_PRINT));
echo 'PASS: six isolated MySQL multi-process race scenarios. No live provider calls.', PHP_EOL;
