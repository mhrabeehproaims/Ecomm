<?php

$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Services\Storefront\CheckoutAccess::assertEnabled();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolation check failed.');
}
$stamp = ['created_at' => now(), 'updated_at' => now()];
$phone = '96'.random_int(10000000, 99999999);
$password = 'DocumentFixturePassword123!';
$customer = Illuminate\Support\Facades\DB::table('customers')->insertGetId(['first_name' => 'Document fixture', 'phone' => $phone, 'password' => Illuminate\Support\Facades\Hash::make($password), 'status' => 1] + $stamp, 'customer_id');
$address = Illuminate\Support\Facades\DB::table('delivery_addresses')->insertGetId(['customer_id' => $customer, 'phone' => $phone, 'address_line1' => 'Recorded document lane', 'city' => 'Test', 'state' => 'kerala (KL)', 'country' => 'India', 'postal_code' => '673001', 'is_default' => true] + $stamp, 'address_id');
$product = Illuminate\Support\Facades\DB::table('products')->where('product_name', 'QA Cotton Bag')->value('product_id');
Illuminate\Support\Facades\DB::table('carts')->insert(['user_id' => $customer, 'product_id' => $product, 'quantity' => 1, 'price' => 1] + $stamp);
$service = app(App\Services\Storefront\CheckoutService::class);
$quote = $service->quote($customer, $address, '', 'cod');
$order = $service->place($customer, $quote['quote_id'], (string) Illuminate\Support\Str::uuid());
$invoice = app(App\Services\Storefront\OrderDocuments::class)->generate($order['order_id']);
echo json_encode(['phone' => $phone, 'password' => $password, 'order_id' => $order['order_id'], 'invoice' => $invoice->invoice_number]), PHP_EOL;
