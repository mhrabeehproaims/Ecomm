<?php

if (PHP_SAPI !== 'cli' || ! str_starts_with(getenv('STOREFRONT_TEST_RUN') ?: '', 'admin-cart-')) {
    throw new RuntimeException('A dedicated admin-cart-* test run is required.');
}
$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolated database required.');
}
$db = Illuminate\Support\Facades\DB::class;
App\Models\User::firstOrCreate(['email' => 'admin-cart@example.test'], ['name' => 'Isolated Cart Admin', 'password' => Illuminate\Support\Facades\Hash::make('IsolatedCartBrowser123!')]);
$customer = $db::table('customers')->where('phone', '9000000011')->value('customer_id');
if (! $customer || $db::table('carts')->exists()) {
    throw new RuntimeException('Seed customers first; refusing to overwrite cart data.');
}
$other = $db::table('customers')->insertGetId(['first_name' => 'Other Cart', 'last_name' => 'Customer', 'phone' => '9000000022', 'password' => Illuminate\Support\Facades\Hash::make('IsolatedCartBrowser123!'), 'status' => 1], 'customer_id');
foreach ([[$customer, 1, 2, 250], [$customer, 2, 1, 400], [$other, 2, 3, 400]] as [$owner, $product, $quantity, $price]) {
    $db::table('carts')->insert(['user_id' => $owner, 'product_id' => $product, 'quantity' => $quantity, 'price' => $price, 'created_at' => now(), 'updated_at' => now()]);
}
echo json_encode(['environment' => 'isolated-admin-cart-test', 'cart_rows' => 3, 'database' => 'isolated.sqlite']).PHP_EOL;
