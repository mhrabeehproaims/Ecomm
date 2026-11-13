<?php

$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolation check failed.');
}
use Illuminate\Support\Facades\DB;

$stamp = ['created_at' => now(), 'updated_at' => now()];
$phone = '94'.random_int(10000000, 99999999);
$password = 'IsolatedPendingPassword123!';
$customer = DB::table('customers')->insertGetId(['first_name' => 'Pending fixture', 'phone' => $phone, 'password' => Illuminate\Support\Facades\Hash::make($password), 'status' => 1] + $stamp, 'customer_id');
$address = DB::table('delivery_addresses')->insertGetId(['customer_id' => $customer, 'phone' => $phone, 'address_line1' => 'Pending test lane',
    'city' => 'Test', 'state' => 'kerala (KL)', 'country' => 'India', 'postal_code' => '673001', 'is_default' => true] + $stamp, 'address_id');
$product = DB::table('products')->where('product_name', 'QA Cotton Bag')->value('product_id');
DB::table('carts')->insert(['user_id' => $customer, 'product_id' => $product, 'quantity' => 1, 'price' => 1] + $stamp);
$service = app(App\Services\Storefront\CheckoutService::class);
$quote = $service->quote($customer, $address, '', 'online');
$order = $service->place($customer, $quote['quote_id'], (string) Illuminate\Support\Str::uuid());
echo json_encode(['phone' => $phone, 'password' => $password, 'order_id' => $order['order_id'], 'amount' => $quote['total_minor']]), PHP_EOL;
