<?php

$test = require __DIR__.'/mysql-environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

DB::statement('SET SESSION innodb_lock_wait_timeout=5');
[$script, $barrier, $mode, $customer, $reference, $key] = $argv;
$deadline = microtime(true) + 10;
while (! is_file($barrier) && microtime(true) < $deadline) {
    usleep(10000);
}
if (! is_file($barrier)) {
    throw new RuntimeException('Concurrency barrier timed out.');
}
try {
    $service = app(App\Services\Storefront\CheckoutService::class);
    if ($mode === 'place') {
        $result = $service->place((int) $customer, $reference, $key);
    } elseif ($mode === 'release') {
        $service->release((int) $customer, (int) $reference, true);
        $result = ['released' => true];
    } elseif ($mode === 'replace') {
        app(App\Services\Storefront\ReturnService::class)->transition((int) $reference, 'replaced');
        $result = ['order_id' => DB::table('sales_returns')->where('return_id', $reference)->value('reissued_order_id')];
    } else {
        $attempt = DB::table('storefront_payment_attempts')->where('order_id', $reference)->first();
        $fake = new Tests\Support\FakeStorefrontGateway;
        $fake->orders[$attempt->provider_order_id] = ['id' => $attempt->provider_order_id, 'amount' => (int) $attempt->amount_minor,
            'currency' => 'INR', 'receipt' => $attempt->id, 'status' => 'paid', 'amount_paid' => (int) $attempt->amount_minor, 'amount_due' => 0];
        $fake->paymentState($attempt->provider_order_id, 'captured', 'pay_MySql'.$reference);
        $app->instance(App\Services\Storefront\PaymentGateway::class, $fake);
        $result = app(App\Services\Storefront\PaymentService::class)->verify($attempt, 'pay_MySql'.$reference, 'concurrency-test');
    }
    echo json_encode(['ok' => true, 'order_id' => $result['order_id'] ?? null]), PHP_EOL;
} catch (Illuminate\Validation\ValidationException $e) {
    echo json_encode(['ok' => false, 'status' => 422, 'message' => $e->getMessage()]), PHP_EOL;
} catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
    echo json_encode(['ok' => false, 'status' => $e->getStatusCode(), 'message' => $e->getMessage()]), PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, get_class($e).': '.$e->getMessage());
    exit(1);
}
